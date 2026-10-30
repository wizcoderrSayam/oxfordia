<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_number' => 'EMP-TEST-'.fake()->unique()->numerify('######'),
            'full_name' => fake()->name(),
            'father_name' => fake()->name('male'),
            'mother_name' => fake()->name('female'),
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'address' => fake()->address(),
            'position' => fake()->randomElement(['Faculty', 'Senior Faculty', 'Counsellor', 'Office Assistant', 'Lab Instructor']),
            'joining_date' => now()->subYears(2)->toDateString(),
            'status' => EmployeeStatus::Active,
            'bio' => fake()->paragraph(),
        ];
    }

    public function relieved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => EmployeeStatus::Relieved]);
    }
}
