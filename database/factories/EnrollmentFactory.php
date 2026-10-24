<?php

namespace Database\Factories;

use App\Enums\EnrollmentStatus;
use App\Enums\ResultStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_number' => 'ENR-TEST-'.fake()->unique()->numerify('######'),
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'enrollment_date' => now()->subMonth()->toDateString(),
            'status' => EnrollmentStatus::Enrolled,
        ];
    }

    /**
     * A completed enrollment with a stored passing result.
     */
    public function passed(string $grade = 'A'): static
    {
        return $this->state(fn () => [
            'status' => EnrollmentStatus::Completed,
            'completion_date' => now()->toDateString(),
            'total_marks' => 160,
            'max_total_marks' => 200,
            'percentage' => 80,
            'result' => ResultStatus::Pass,
            'final_grade' => $grade,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => EnrollmentStatus::Completed,
            'completion_date' => now()->toDateString(),
            'total_marks' => 60,
            'max_total_marks' => 200,
            'percentage' => 30,
            'result' => ResultStatus::Fail,
            'final_grade' => 'Fail',
        ]);
    }

    public function withdrawn(): static
    {
        return $this->state(fn () => ['status' => EnrollmentStatus::Withdrawn]);
    }
}
