<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['enrollment_id', 'course_module_id', 'theory_marks', 'practical_marks', 'marks'])]
class EnrollmentMark extends Model
{
    /**
     * Keep the stored total in sync with the theory and practical marks.
     * A mark saved with only a total is treated as theory-only.
     */
    protected static function booted(): void
    {
        static::saving(function (EnrollmentMark $mark): void {
            $mark->practical_marks ??= 0;
            $mark->theory_marks ??= (float) $mark->marks - (float) $mark->practical_marks;
            $mark->marks = round((float) $mark->theory_marks + (float) $mark->practical_marks, 2);
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
            'theory_marks' => 'decimal:2',
            'practical_marks' => 'decimal:2',
            'marks' => 'decimal:2',
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
     * @return BelongsTo<CourseModule, $this>
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    /**
     * Whether these marks reach the module's theory and practical pass marks.
     */
    public function isPassed(CourseModule $module): bool
    {
        return $module->isPassedBy((float) $this->theory_marks, (float) $this->practical_marks);
    }
}
