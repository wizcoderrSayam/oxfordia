<?php

namespace App\Services;

use App\Enums\CertificateStatus;
use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\User;
use App\Notifications\CertificateIssued;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CertificateService
{
    public function __construct(
        private NumberSequenceService $numbers,
        private ActivityLogger $logger,
    ) {}

    /**
     * Issue a certificate for a completed and passed enrollment.
     *
     * @throws ValidationException
     */
    public function issue(Enrollment $enrollment, User $issuer, ?string $issueDate = null): Certificate
    {
        $certificate = DB::transaction(function () use ($enrollment, $issuer, $issueDate): Certificate {
            /** @var Enrollment $locked */
            $locked = Enrollment::query()->whereKey($enrollment->id)->lockForUpdate()->firstOrFail();
            $locked->load(['student', 'course']);

            if ($locked->status !== EnrollmentStatus::Completed) {
                throw ValidationException::withMessages(['enrollment' => 'Only completed enrollments can receive a certificate.']);
            }

            if (! $locked->isPassed()) {
                throw ValidationException::withMessages(['enrollment' => 'This enrollment does not have a passing result.']);
            }

            if ($locked->validCertificate()->exists()) {
                throw ValidationException::withMessages(['enrollment' => 'A valid certificate already exists for this enrollment.']);
            }

            $replaces = $locked->certificates()
                ->where('status', CertificateStatus::Revoked)
                ->latest('id')
                ->first();

            $certificate = new Certificate;
            $certificate->forceFill([
                'certificate_number' => $this->numbers->nextCertificateNumber(),
                'verification_identifier' => $this->newIdentifier(),
                'enrollment_id' => $locked->id,
                'student_id' => $locked->student_id,
                'course_id' => $locked->course_id,
                'replaces_certificate_id' => $replaces?->id,
                'student_name_snapshot' => $locked->student->full_name,
                'course_title_snapshot' => $locked->course->title,
                'grade_snapshot' => $locked->final_grade,
                'issue_date' => $issueDate ?? now()->toDateString(),
                'issued_by' => $issuer->id,
                'status' => CertificateStatus::Valid,
            ])->save();

            $this->logger->log('certificate.issued', $certificate, "Issued {$certificate->certificate_number}", array_filter([
                'enrollment' => $locked->enrollment_number,
                'replaces' => $replaces?->certificate_number,
            ]));

            return $certificate;
        });

        $this->notifyStudent($certificate);

        return $certificate;
    }

    /**
     * Revoke a valid certificate. Revoked certificates stay verifiable as revoked.
     *
     * @throws ValidationException
     */
    public function revoke(Certificate $certificate, User $revoker, string $reason): Certificate
    {
        return DB::transaction(function () use ($certificate, $revoker, $reason): Certificate {
            /** @var Certificate $locked */
            $locked = Certificate::query()->whereKey($certificate->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isValid()) {
                throw ValidationException::withMessages(['certificate' => 'This certificate has already been revoked.']);
            }

            $locked->forceFill([
                'status' => CertificateStatus::Revoked,
                'revoked_at' => now(),
                'revoked_by' => $revoker->id,
                'revocation_reason' => $reason,
            ])->save();

            $this->logger->log('certificate.revoked', $locked, "Revoked {$locked->certificate_number}", ['reason' => $reason]);

            return $locked;
        });
    }

    /**
     * Find a certificate by its public identifier or human readable number.
     */
    public function findForVerification(string $value): ?Certificate
    {
        $value = trim($value);

        if ($value === '' || strlen($value) > 64) {
            return null;
        }

        return Certificate::query()
            ->where('verification_identifier', $value)
            ->orWhere('certificate_number', strtoupper($value))
            ->first();
    }

    private function newIdentifier(): string
    {
        do {
            $identifier = Str::random(32);
        } while (Certificate::query()->where('verification_identifier', $identifier)->exists());

        return $identifier;
    }

    private function notifyStudent(Certificate $certificate): void
    {
        $certificate->student->notifyByMail(new CertificateIssued($certificate));
    }
}
