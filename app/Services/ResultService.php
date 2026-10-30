<?php

namespace App\Services;

use App\Enums\ResultStatus;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use Illuminate\Support\Collection;

/**
 * Computes totals, percentage, pass/fail and grade from module marks.
 */
class ResultService
{
    public function __construct(private InstituteSettings $settings) {}

    /**
     * Recalculate and persist the result columns of an enrollment.
     */
    /**
     * @param  Collection<int, CourseModule>|null  $modules  Pre-loaded course modules, to avoid re-querying per enrollment.
     */
    public function recalculate(Enrollment $enrollment, ?Collection $modules = null): Enrollment
    {
        $modules ??= $enrollment->course->modules()->get();
        $marks = $enrollment->marks()->get()->keyBy('course_module_id');

        $result = $this->compute($modules, $marks->map(fn ($mark) => [
            'theory' => (float) $mark->theory_marks,
            'practical' => (float) $mark->practical_marks,
        ])->all());

        $enrollment->forceFill([
            'total_marks' => $result['total'],
            'max_total_marks' => $result['max_total'],
            'percentage' => $result['percentage'],
            'result' => $result['result'],
            'final_grade' => $result['grade'],
        ])->save();

        return $enrollment;
    }

    /**
     * Recalculate every enrollment of a course that is not frozen by a valid certificate.
     */
    public function recalculateCourse(Course $course): void
    {
        $modules = $course->modules()->get();

        $course->enrollments()
            ->whereDoesntHave('validCertificate')
            ->each(fn (Enrollment $enrollment) => $this->recalculate($enrollment, $modules));
    }

    /**
     * Re-grade every final result that is not frozen by a valid certificate, after the grade bands change.
     *
     * @return int The number of results re-graded.
     */
    public function regradeAll(): int
    {
        $count = 0;

        Enrollment::query()
            ->whereNotNull('percentage')
            ->whereDoesntHave('validCertificate')
            ->with('course.modules')
            ->each(function (Enrollment $enrollment) use (&$count) {
                $this->recalculate($enrollment, $enrollment->course->modules);
                $count++;
            });

        return $count;
    }

    /**
     * Compute a result. It is only final when every module has marks.
     * A module is passed only when both its theory and practical pass marks are reached.
     *
     * @param  Collection<int, CourseModule>  $modules
     * @param  array<int, array{theory: float, practical: float}>  $marksByModule
     * @return array{total: float|null, max_total: int|null, percentage: float|null, result: ResultStatus|null, grade: string|null, complete: bool}
     */
    public function compute(Collection $modules, array $marksByModule): array
    {
        $empty = ['total' => null, 'max_total' => null, 'percentage' => null, 'result' => null, 'grade' => null, 'complete' => false];

        if ($modules->isEmpty()) {
            return $empty;
        }

        $complete = $modules->every(fn (CourseModule $module) => array_key_exists($module->id, $marksByModule));

        if (! $complete) {
            return $empty;
        }

        $total = round($modules->sum(fn (CourseModule $module) => $marksByModule[$module->id]['theory'] + $marksByModule[$module->id]['practical']), 2);
        $maxTotal = (int) $modules->sum('max_marks');
        $percentage = $maxTotal > 0 ? round($total / $maxTotal * 100, 2) : 0.0;

        $passedEveryModule = $modules->every(fn (CourseModule $module) => $module->isPassedBy(
            $marksByModule[$module->id]['theory'],
            $marksByModule[$module->id]['practical'],
        ));
        $grade = $passedEveryModule ? $this->gradeFor($percentage) : null;
        $passed = $passedEveryModule && $grade !== null;

        return [
            'total' => $total,
            'max_total' => $maxTotal,
            'percentage' => $percentage,
            'result' => $passed ? ResultStatus::Pass : ResultStatus::Fail,
            'grade' => $passed ? $grade : 'Fail',
            'complete' => true,
        ];
    }

    /**
     * The grade for a percentage, or null when it falls below every band.
     */
    public function gradeFor(float $percentage): ?string
    {
        foreach ($this->settings->gradeBands() as $band) {
            if ($percentage >= $band['min']) {
                return $band['grade'];
            }
        }

        return null;
    }
}
