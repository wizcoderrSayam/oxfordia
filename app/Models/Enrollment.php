<?php

namespace App\Models;

use App\Enums\CertificateStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\ResultStatus;
use Database\Factories\EnrollmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'student_id', 'course_id', 'enrollment_date', 'start_date', 'expected_completion_date',
    'completion_date', 'status', 'remarks',
])]
class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'result' => ResultStatus::class,
            'enrollment_date' => 'date',
            'start_date' => 'date',
            'expected_completion_date' => 'date',
            'completion_date' => 'date',
            'total_marks' => 'decimal:2',
            'percentage' => 'decimal:2',
            'max_total_marks' => 'integer',
        ];
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
     * @return HasMany<EnrollmentMark, $this>
     */
    public function marks(): HasMany
    {
        return $this->hasMany(EnrollmentMark::class);
    }

    /**
     * @return HasMany<Certificate, $this>
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * @return HasOne<Certificate, $this>
     */
    public function validCertificate(): HasOne
    {
        return $this->hasOne(Certificate::class)->where('status', CertificateStatus::Valid);
    }

    /**
     * Enrollments that block a new enrollment in the same course.
     *
     * @param  Builder<Enrollment>  $query
     */
    #[Scope]
    protected function blocking(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->whereIn('status', [EnrollmentStatus::Enrolled, EnrollmentStatus::InProgress])
                ->orWhere(function (Builder $query) {
                    $query->where('status', EnrollmentStatus::Completed)
                        ->where(fn (Builder $q) => $q->whereNull('result')->orWhere('result', ResultStatus::Pass));
                });
        });
    }

    /**
     * Completed and passed enrollments without a valid certificate.
     *
     * @param  Builder<Enrollment>  $query
     */
    #[Scope]
    protected function eligibleForCertificate(Builder $query): void
    {
        $query->where('status', EnrollmentStatus::Completed)
            ->where('result', ResultStatus::Pass)
            ->whereDoesntHave('validCertificate');
    }

    public function isPassed(): bool
    {
        return $this->result === ResultStatus::Pass;
    }

    public function isEligibleForCertificate(): bool
    {
        return $this->status === EnrollmentStatus::Completed
            && $this->isPassed()
            && ! $this->validCertificate()->exists();
    }
}
