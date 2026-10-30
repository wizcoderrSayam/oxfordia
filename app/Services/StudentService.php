<?php

namespace App\Services;

use App\Enums\EnquiryStatus;
use App\Enums\Role;
use App\Enums\StudentStatus;
use App\Models\AdmissionEnquiry;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PortalInvitation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentService
{
    public function __construct(
        private NumberSequenceService $numbers,
        private ActivityLogger $logger,
    ) {}

    /**
     * Register a new student.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?UploadedFile $photo = null, ?AdmissionEnquiry $enquiry = null): Student
    {
        return DB::transaction(function () use ($attributes, $photo, $enquiry): Student {
            $student = new Student(array_merge([
                'admission_date' => now()->toDateString(),
                'status' => StudentStatus::Active,
            ], $attributes));

            $student->registration_number = $this->numbers->nextStudentNumber();

            if ($photo) {
                $student->profile_photo_path = $photo->store('students', 'public');
            }

            $student->save();

            if ($enquiry) {
                $enquiry->student()->associate($student);
                $enquiry->status = EnquiryStatus::Converted;
                $enquiry->save();
            }

            $this->logger->log('student.created', $student, "Registered {$student->registration_number}");

            return $student;
        });
    }

    /**
     * Find or create the student record for a registered student user.
     *
     * @param  array<string, mixed>  $details
     */
    public function forUser(User $user, array $details = []): Student
    {
        $existing = $user->student()->first();

        if ($existing) {
            return $existing;
        }

        $student = $this->create(array_merge([
            'full_name' => $user->name,
            'email' => $user->email,
        ], $details));

        $student->user()->associate($user);
        $student->save();

        return $student;
    }

    /**
     * Update a student, replacing or removing the profile photo when requested.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Student $student, array $attributes, ?UploadedFile $photo = null, bool $removePhoto = false): Student
    {
        $oldPhoto = $student->profile_photo_path;

        if ($photo) {
            $student->profile_photo_path = $photo->store('students', 'public');
        } elseif ($removePhoto) {
            $student->profile_photo_path = null;
        }

        $student->fill($attributes)->save();

        if ($oldPhoto && $oldPhoto !== $student->profile_photo_path) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $changed = $this->logger->changedFields($student);

        if ($changed) {
            $this->logger->log('student.updated', $student, "Updated {$student->registration_number}", ['fields' => $changed]);
        }

        return $student;
    }

    /**
     * Delete a student together with their enrollments, marks and result documents.
     *
     * @throws ValidationException
     */
    public function delete(Student $student): void
    {
        if ($student->hasProtectedRecords()) {
            throw ValidationException::withMessages([
                'student' => 'This student has an issued certificate or a payment order. Mark the student inactive instead.',
            ]);
        }

        $resultDocuments = $student->enrollments()->whereNotNull('result_document_path')->pluck('result_document_path')->all();

        DB::transaction(function () use ($student) {
            $enrollmentNumbers = $student->enrollments()->pluck('enrollment_number')->all();

            $this->logger->log('student.deleted', null, "Deleted {$student->registration_number}", array_filter(['enrollments' => $enrollmentNumbers]));

            // Marks are removed by the enrollment_marks cascade.
            $student->enrollments()->delete();
            $student->delete();
        });

        Storage::disk('local')->delete($resultDocuments);

        if ($student->profile_photo_path) {
            Storage::disk('public')->delete($student->profile_photo_path);
        }
    }

    /**
     * Give a staff-created student portal access and email a set-password link.
     *
     * @throws ValidationException
     */
    public function invite(Student $student): User
    {
        if (! $student->email) {
            throw ValidationException::withMessages(['email' => 'Add an email address to this student before sending a portal invite.']);
        }

        $user = DB::transaction(function () use ($student): User {
            $user = $student->user ?? User::query()->where('email', $student->email)->lockForUpdate()->first();

            // An existing self-registered account is only linked once its owner has proven the email
            // address; otherwise anyone could pre-register a student's email and receive their records.
            if ($user && ! $student->user && ! $user->hasVerifiedEmail()) {
                throw ValidationException::withMessages(['email' => 'An unverified account already uses this email. Ask the student to verify their email address first, then send the invite again.']);
            }

            if ($user && ! $user->isStudent()) {
                throw ValidationException::withMessages(['email' => 'This email belongs to a staff account and cannot be used for the student portal.']);
            }

            if ($user && $user->student()->whereKeyNot($student->id)->exists()) {
                throw ValidationException::withMessages(['email' => 'This email is already linked to another student record.']);
            }

            if (! $user) {
                $user = new User([
                    'name' => $student->full_name,
                    'email' => $student->email,
                    'password' => Str::password(32),
                ]);
                $user->role = Role::Student;
                $user->save();
            }

            $student->user()->associate($user);
            $student->save();

            $this->logger->log('student.portal_invited', $student, "Portal invite sent to {$student->registration_number}");

            return $user;
        });

        // Sent immediately so the password token is never written to the queue table.
        $user->notifyNow(new PortalInvitation(Password::broker()->createToken($user)));

        return $user;
    }
}
