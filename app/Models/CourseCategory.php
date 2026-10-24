<?php

namespace App\Models;

use Database\Factories\CourseCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description'])]
class CourseCategory extends Model
{
    /** @use HasFactory<CourseCategoryFactory> */
    use HasFactory;

    /**
     * @return HasMany<Course, $this>
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'category_id');
    }
}
