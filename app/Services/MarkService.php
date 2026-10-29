<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\EnrollmentMark;
use App\Notifications\ResultPublished;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarkService
{
    public function __construct(
        private ResultService $results,
        private ActivityLogger $logger,
    ) {}

    /**
     * Enrollments shown in the marks grid for a course.
     *
     * @return Collection<int, Enrollment>
     */
    public function gridEnrollments(Course $course): Collection
    {
        return $course->enrollments()
            ->where('enrollments.status', '!=', EnrollmentStatus::Withdrawn)
            ->with(['student', 'marks', 'validCertificate'])
            ->join('students', 'students.id', '=', 'enrollments.student_id')
            ->orderBy('students.full_name')
            ->select('enrollments.*')
            ->get();
    }

    /**
     * Save marks for many enrollments of one course, then email students whose result became available or changed.
     *
     * @param  array<int|string, array<int|string, mixed>>  $marks  enrollment id => [module id => ['theory' => marks|null, 'practical' => marks|null]]; a plain value is theory marks
     * @return int Number of enrollments updated
     *
     * @throws ValidationException
     */
    public function saveForCourse(Course $course, array $marks): int
    {
        $modules = $course->modules()->get()->keyBy('id');
        $enrollments = $course->enrollments()
            ->whereIn('id', array_keys($marks))
            ->where('status', '!=', EnrollmentStatus::Withdrawn)
            ->with(['validCertificate', 'marks'])
            ->get()
            ->keyBy('id');

        $errors = [];
        /** @var array<int, array<int, array{theory: float, practical: float}|null>> $normalised */
        $normalised = [];

        foreach ($marks as $enrollmentId => $moduleMarks) {
            $enrollment = $enrollments->get($enrollmentId);

            if (! $enrollment) {
                $errors["marks.$enrollmentId"] = 'Unknown enrollment for this course.';

                continue;
            }

            foreach ((array) $moduleMarks as $moduleId => $value) {
                $module = $modules->get($moduleId);

                if (! $module) {
                    $errors["marks.$enrollmentId.$moduleId"] = 'Unknown module.';

                    continue;
                }

                $parsed = $this->parseCell($module, $value);

                if (is_string($parsed)) {
                    $errors["marks.$enrollmentId.$moduleId"] = "{$enrollment->enrollment_number}: {$module->name} {$parsed}";
                } else {
                    $normalised[(int) $enrollmentId][(int) $moduleId] = $parsed;
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        /** @var list<array{enrollment: Enrollment, updated: bool}> $published */
        $published = [];

        $updated = DB::transaction(function () use ($normalised, $enrollments, $modules, &$published): int {
            $updated = 0;

            // Lock the rows and re-check certificates inside the transaction so marks cannot change
            // underneath a certificate issued while this request was running.
            $locked = Enrollment::query()->whereIn('id', $enrollments->keys())->lockForUpdate()->pluck('id');
            $certified = Certificate::query()->whereIn('enrollment_id', $locked)->valid()->pluck('enrollment_id')->flip();

            foreach ($normalised as $enrollmentId => $moduleMarks) {
                /** @var Enrollment $enrollment */
                $enrollment = $enrollments->get($enrollmentId);

                if ($certified->has($enrollment->id)) {
                    continue;
                }

                $changed = false;

                foreach ($moduleMarks as $moduleId => $parts) {
                    $existing = $enrollment->marks->firstWhere('course_module_id', $moduleId);

                    if ($parts === null) {
                        if ($existing) {
                            $existing->delete();
                            $changed = true;
                        }

                        continue;
                    }

                    if ($existing
                        && (float) $existing->theory_marks === $parts['theory']
                        && (float) $existing->practical_marks === $parts['practical']) {
                        continue;
                    }

                    EnrollmentMark::query()->updateOrCreate(
                        ['enrollment_id' => $enrollment->id, 'course_module_id' => $moduleId],
                        ['theory_marks' => $parts['theory'], 'practical_marks' => $parts['practical']],
                    );
                    $changed = true;
                }

                if ($changed) {
                    $hadResult = $enrollment->result !== null;
                    $this->results->recalculate($enrollment, $modules);

                    if ($enrollment->result !== null && $enrollment->wasChanged(['result', 'final_grade'])) {
                        $published[] = ['enrollment' => $enrollment, 'updated' => $hadResult];
                    }

                    $this->logger->log('marks.updated', $enrollment, "Marks updated for {$enrollment->enrollment_number}");
                    $updated++;
                }
            }

            return $updated;
        });

        (new Collection(array_column($published, 'enrollment')))->load(['student.user', 'course']);

        foreach ($published as ['enrollment' => $enrollment, 'updated' => $updated]) {
            $enrollment->student->notifyByMail(new ResultPublished($enrollment, $updated));
        }

        return $updated;
    }

    /**
     * Parse one submitted cell into theory and practical marks.
     *
     * A plain value is theory marks. A part whose max is 0 counts as 0 when left blank.
     *
     * @return array{theory: float, practical: float}|string|null The marks, an error message, or null when the cell is empty.
     */
    private function parseCell(CourseModule $module, mixed $value): array|string|null
    {
        $submitted = is_array($value)
            ? ['theory' => $value['theory'] ?? null, 'practical' => $value['practical'] ?? null]
            : ['theory' => $value, 'practical' => null];
        $maxima = ['theory' => $module->theory_max_marks, 'practical' => $module->practical_max_marks];

        $isBlank = fn (mixed $part): bool => $part === null || $part === '';
        $required = array_filter($maxima, fn (int $max): bool => $max > 0);
        $blankRequired = array_filter(array_intersect_key($submitted, $required), $isBlank);

        if (count($blankRequired) === count($required)) {
            return null;
        }

        if ($blankRequired) {
            return 'needs both theory and practical marks.';
        }

        $parts = [];

        foreach ($maxima as $part => $max) {
            $raw = $submitted[$part];

            if ($isBlank($raw)) {
                $parts[$part] = 0.0;

                continue;
            }

            if (! is_numeric($raw) || $raw < 0 || $raw > $max) {
                return "{$part} must be between 0 and {$max}.";
            }

            $parts[$part] = round((float) $raw, 2);
        }

        return $parts;
    }
}
