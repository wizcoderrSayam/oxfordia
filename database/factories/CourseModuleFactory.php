<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseModule>
 */
class CourseModuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => fake()->words(3, true),
            'max_marks' => 100,
            'pass_marks' => 40,
            'sort_order' => 1,
        ];
    }
}
