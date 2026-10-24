<?php

namespace Database\Factories;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->words(3, true));

        return [
            'category_id' => CourseCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'course_code' => Str::upper(fake()->unique()->bothify('???-###')),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'duration_value' => fake()->numberBetween(1, 12),
            'duration_unit' => 'months',
            'fee' => fake()->randomElement([4999, 9999, 14999]),
            'is_featured' => false,
            'status' => CourseStatus::Published,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => CourseStatus::Draft]);
    }

    public function archived(): static
    {
        return $this->state(fn () => ['status' => CourseStatus::Archived]);
    }

    public function enquiryOnly(): static
    {
        return $this->state(fn () => ['fee' => null]);
    }

    /**
     * Add assessment modules after creating the course.
     */
    public function withModules(int $count = 2, int $maxMarks = 100, int $passMarks = 40): static
    {
        return $this->afterCreating(function (Course $course) use ($count, $maxMarks, $passMarks) {
            foreach (range(1, $count) as $index) {
                $course->modules()->create([
                    'name' => "Module {$index}",
                    'max_marks' => $maxMarks,
                    'pass_marks' => $passMarks,
                    'sort_order' => $index,
                ]);
            }
        });
    }
}
