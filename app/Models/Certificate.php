<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * Certificates are created only by CertificateService, so no attributes are mass assignable.
 */
class Certificate extends Model
{
    /**
     * Columns frozen at issuance; corrections require revoking and reissuing.
     */
    public const IMMUTABLE = [
        'certificate_number', 'verification_identifier', 'enrollment_id', 'student_id', 'course_id',
        'student_name_snapshot', 'course_title_snapshot', 'grade_snapshot', 'issue_date', 'issued_by',
    ];

    protected static function booted(): void
    {
        static::updating(function (Certificate $certificate): void {
            if ($certificate->isDirty(self::IMMUTABLE)) {
                throw new LogicException('Issued certificate details cannot be changed. Revoke and reissue instead.');
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CertificateStatus::class,
            'issue_date' => 'date',
            'revoked_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Enrollment, $this>
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return BelongsTo<Course, $this>
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * @return BelongsTo<Certificate, $this>
     */
    public function replaces(): BelongsTo
    {
        return $this->belongsTo(Certificate::class, 'replaces_certificate_id');
    }

    /**
     * @param  Builder<Certificate>  $query
     */
    #[Scope]
    protected function valid(Builder $query): void
    {
        $query->where('status', CertificateStatus::Valid);
    }

    public function isValid(): bool
    {
        return $this->status === CertificateStatus::Valid;
    }

    public function verificationUrl(): string
    {
        return route('certificates.verify.show', $this->verification_identifier);
    }
}
