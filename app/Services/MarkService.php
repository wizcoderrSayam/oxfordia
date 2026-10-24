<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Course;
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
     * @param  array<int|string, array<int|string, mixed>>  $marks  enrollment id => [module id => marks|null]
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
                } elseif ($value !== null && $value !== '' && (! is_numeric($value) || $value < 0 || $value > $module->max_marks)) {
                    $errors["marks.$enrollmentId.$moduleId"] = "{$enrollment->enrollment_number}: {$module->name} must be between 0 and {$module->max_marks}.";
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        /** @var list<array{enrollment: Enrollment, updated: bool}> $published */
        $published = [];

        $updated = DB::transaction(function () use ($marks, $enrollments, $modules, &$published): int {
            $updated = 0;

            // Lock the rows and re-check certificates inside the transaction so marks cannot change
            // underneath a certificate issued while this request was running.
            $locked = Enrollment::query()->whereIn('id', $enrollments->keys())->lockForUpdate()->pluck('id');
            $certified = Certificate::query()->whereIn('enrollment_id', $locked)->valid()->pluck('enrollment_id')->flip();

            foreach ($marks as $enrollmentId => $moduleMarks) {
                /** @var Enrollment $enrollment */
                $enrollment = $enrollments->get($enrollmentId);

                if ($certified->has($enrollment->id)) {
                    continue;
                }

                $changed = false;

                foreach ((array) $moduleMarks as $moduleId => $value) {
                    $existing = $enrollment->marks->firstWhere('course_module_id', (int) $moduleId);

                    if ($value === null || $value === '') {
                        if ($existing) {
                            $existing->delete();
                            $changed = true;
                        }

                        continue;
                    }

                    if ($existing && (float) $existing->marks === (float) $value) {
                        continue;
                    }

                    EnrollmentMark::query()->updateOrCreate(
                        ['enrollment_id' => $enrollment->id, 'course_module_id' => (int) $moduleId],
                        ['marks' => round((float) $value, 2)],
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
}
