<?php

namespace Database\Factories;

use App\Enums\StudentStatus;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => 'OXF-TEST-'.fake()->unique()->numerify('######'),
            'full_name' => fake()->name(),
            'father_name' => fake()->name('male'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('98########'),
            'date_of_birth' => fake()->dateTimeBetween('-35 years', '-17 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
            'admission_date' => now()->subMonths(2)->toDateString(),
            'status' => StudentStatus::Active,
            'internal_notes' => null,
        ];
    }
}
