<?php

namespace App\Models;

use Database\Factories\CourseModuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'theory_max_marks', 'practical_max_marks', 'theory_pass_marks', 'practical_pass_marks',
    'max_marks', 'pass_marks', 'sort_order',
])]
class CourseModule extends Model
{
    /** @use HasFactory<CourseModuleFactory> */
    use HasFactory;

    /**
     * Keep the stored max and pass totals in sync with the theory and practical parts.
     * A module created with only max/pass marks is treated as theory-only.
     */
    protected static function booted(): void
    {
        static::saving(function (CourseModule $module): void {
            $module->practical_max_marks ??= 0;
            $module->practical_pass_marks ??= 0;
            $module->theory_max_marks ??= (int) $module->max_marks - $module->practical_max_marks;
            $module->theory_pass_marks ??= (int) $module->pass_marks - $module->practical_pass_marks;

            $module->max_marks = $module->theory_max_marks + $module->practical_max_marks;
            $module->pass_marks = $module->theory_pass_marks + $module->practical_pass_marks;
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
            'theory_max_marks' => 'integer',
            'practical_max_marks' => 'integer',
            'theory_pass_marks' => 'integer',
            'practical_pass_marks' => 'integer',
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

    /**
     * Whether the module has a practical part.
     */
    public function hasPractical(): bool
    {
        return $this->practical_max_marks > 0;
    }

    /**
     * A module is passed when both the theory and the practical pass marks are reached.
     */
    public function isPassedBy(float $theoryMarks, float $practicalMarks): bool
    {
        return $theoryMarks >= $this->theory_pass_marks && $practicalMarks >= $this->practical_pass_marks;
    }
}
