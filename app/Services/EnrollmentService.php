<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EnrollmentService
{
    public function __construct(
        private NumberSequenceService $numbers,
        private ActivityLogger $logger,
    ) {}

    /**
     * Enroll a student in a course, refusing duplicates of active or passed enrollments.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    public function enroll(Student $student, Course $course, array $attributes = []): Enrollment
    {
        return DB::transaction(function () use ($student, $course, $attributes): Enrollment {
            Student::query()->whereKey($student->id)->lockForUpdate()->first();

            if ($this->hasBlockingEnrollment($student, $course)) {
                throw ValidationException::withMessages([
                    'course_id' => 'This student already has an active or passed enrollment in this course.',
                ]);
            }

            $enrollment = new Enrollment(array_merge([
                'enrollment_date' => now()->toDateString(),
                'status' => EnrollmentStatus::Enrolled,
            ], $attributes));

            $enrollment->student()->associate($student);
            $enrollment->course()->associate($course);
            $enrollment->enrollment_number = $this->numbers->nextEnrollmentNumber();
            $enrollment->save();

            $this->logger->log('enrollment.created', $enrollment, "Enrolled {$student->registration_number} in {$course->course_code}");

            return $enrollment;
        });
    }

    public function hasBlockingEnrollment(Student $student, Course $course): bool
    {
        return $student->enrollments()->where('course_id', $course->id)->blocking()->exists();
    }

    /**
     * Update an enrollment's progress details.
     *
     * @param  array<string, mixed>  $attributes
     *
     * @throws ValidationException
     */
    public function update(Enrollment $enrollment, array $attributes): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $attributes): Enrollment {
            // Same lock order as enroll(): student first, then the enrollment itself.
            Student::query()->whereKey($enrollment->student_id)->lockForUpdate()->first();
            /** @var Enrollment $locked */
            $locked = Enrollment::query()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();

            $newStatus = isset($attributes['status'])
                ? ($attributes['status'] instanceof EnrollmentStatus ? $attributes['status'] : EnrollmentStatus::from($attributes['status']))
                : $locked->status;

            if ($locked->validCertificate()->exists() && $newStatus !== EnrollmentStatus::Completed) {
                throw ValidationException::withMessages([
                    'status' => 'Revoke the valid certificate before changing the status of this enrollment.',
                ]);
            }

            $becomesActive = in_array($newStatus, [EnrollmentStatus::Enrolled, EnrollmentStatus::InProgress], true)
                && ! in_array($locked->status, [EnrollmentStatus::Enrolled, EnrollmentStatus::InProgress], true);

            $otherBlocking = Enrollment::query()
                ->where('student_id', $locked->student_id)
                ->where('course_id', $locked->course_id)
                ->whereKeyNot($locked->id)
                ->blocking()
                ->exists();

            if ($becomesActive && $otherBlocking) {
                throw ValidationException::withMessages([
                    'status' => 'This student already has another active or passed enrollment in this course.',
                ]);
            }

            if ($newStatus === EnrollmentStatus::Completed && empty($attributes['completion_date'])) {
                $attributes['completion_date'] = $locked->completion_date?->toDateString() ?? now()->toDateString();
            } elseif ($newStatus !== EnrollmentStatus::Completed) {
                $attributes['completion_date'] = null;
            }

            $locked->fill($attributes)->save();

            if ($locked->wasChanged('status')) {
                $this->logger->log('enrollment.status_changed', $locked, "Status changed to {$locked->status->label()}");
            }

            $enrollment->setRawAttributes($locked->getAttributes(), true);

            return $locked;
        });
    }
}
