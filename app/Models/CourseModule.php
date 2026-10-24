<?php

namespace App\Models;

use Database\Factories\CourseModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'max_marks', 'pass_marks', 'sort_order'])]
class CourseModule extends Model
{
    /** @use HasFactory<CourseModuleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'max_marks' => 'integer',
            'pass_marks' => 'integer',
            'sort_order' => 'integer',
        ];
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
     * Max and pass marks are frozen once any student has marks recorded.
     */
    public function isLocked(): bool
    {
        return $this->marks()->exists();
    }
}
