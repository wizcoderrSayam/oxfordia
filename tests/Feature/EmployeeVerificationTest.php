<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{registration_number: string, dob_day: int, dob_month: int, dob_year: int}
     */
    private function details(string $registrationNumber = 'EMP-2026-0007', string $dateOfBirth = '1990-03-14'): array
    {
        [$year, $month, $day] = array_map('intval', explode('-', $dateOfBirth));

        return ['registration_number' => $registrationNumber, 'dob_day' => $day, 'dob_month' => $month, 'dob_year' => $year];
    }

    public function test_the_verification_form_is_shown(): void
    {
        $this->get(route('employees.verify'))
            ->assertOk()
            ->assertSee('Registration number')
            ->assertSee('name="registration_number"', false)
            ->assertSee('name="dob_day"', false)
            ->assertSee('name="dob_month"', false)
            ->assertSee('name="dob_year"', false)
            ->assertDontSee('Login failed');
    }

    public function test_the_header_links_to_employee_verification(): void
    {
        foreach ([route('home'), route('about')] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('href="'.route('employees.verify').'" class="btn-oxf btn-navy', false)
                ->assertSee('Employee Login')
                ->assertDontSee('Verify an employee')
                ->assertDontSee('data-verify-menu', false);
        }
    }

    public function test_the_old_address_redirects_to_the_employee_login(): void
    {
        $this->get('/verify-employee')->assertRedirect('/employee-login');
    }

    public function test_the_form_can_be_prefilled_with_a_registration_number(): void
    {
        $this->get(route('employees.verify', ['registration_number' => 'emp-2026-0007']))
            ->assertOk()
            ->assertSee('value="EMP-2026-0007"', false);
    }

    public function test_matching_registration_number_and_date_of_birth_show_the_employee_details(): void
    {
        Employee::factory()->create([
            'registration_number' => 'EMP-2026-0007',
            'full_name' => 'Ravi Shankar',
            'father_name' => 'Mohan Shankar',
            'mother_name' => 'Sita Shankar',
            'date_of_birth' => '1990-03-14',
            'address' => '12 MG Road, Lucknow',
            'joining_date' => '2021-07-01',
            'position' => 'Senior Faculty',
            'bio' => 'Teaches programming fundamentals.',
        ]);

        $this->post(route('employees.verify.lookup'), $this->details())
            ->assertOk()
            ->assertSee('Active employee')
            ->assertSee('Ravi Shankar')
            ->assertSee('Mohan Shankar')
            ->assertSee('Sita Shankar')
            ->assertSee('14 Mar 1990')
            ->assertSee('12 MG Road, Lucknow')
            ->assertSee('01 Jul 2021')
            ->assertSee('Senior Faculty')
            ->assertSee('Active')
            ->assertSee('Teaches programming fundamentals.');
    }

    public function test_the_lookup_ignores_case_and_surrounding_spaces(): void
    {
        Employee::factory()->create(['registration_number' => 'EMP-2026-0007', 'full_name' => 'Ravi Shankar', 'date_of_birth' => '1990-03-14']);

        $this->post(route('employees.verify.lookup'), $this->details('  emp-2026-0007 '))
            ->assertOk()
            ->assertSee('Ravi Shankar');
    }

    public function test_a_wrong_date_of_birth_gives_the_same_response_as_an_unknown_number(): void
    {
        Employee::factory()->create(['registration_number' => 'EMP-2026-0007', 'full_name' => 'Ravi Shankar', 'date_of_birth' => '1990-03-14']);

        $wrongDateOfBirth = $this->post(route('employees.verify.lookup'), $this->details('EMP-2026-0007', '1990-03-15'));
        $unknownNumber = $this->post(route('employees.verify.lookup'), $this->details('EMP-2026-9999', '1990-03-15'));

        foreach ([$wrongDateOfBirth, $unknownNumber] as $response) {
            $response->assertNotFound()
                ->assertSee('Login failed')
                ->assertSee('15 March 1990')
                ->assertDontSee('Ravi Shankar');
        }
    }

    public function test_an_impossible_date_of_birth_is_rejected(): void
    {
        $this->post(route('employees.verify.lookup'), $this->details('EMP-2026-0007', '1990-02-31'))
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_a_relieved_employee_is_flagged_as_no_longer_employed(): void
    {
        Employee::factory()->relieved()->create(['registration_number' => 'EMP-2026-0008', 'date_of_birth' => '1990-03-14']);

        $this->post(route('employees.verify.lookup'), $this->details('EMP-2026-0008'))
            ->assertOk()
            ->assertSee('No longer employed')
            ->assertSee('is no longer an employee', false)
            ->assertDontSee('Active employee');
    }

    public function test_lookups_are_rate_limited(): void
    {
        foreach (range(1, 10) as $attempt) {
            $this->post(route('employees.verify.lookup'), $this->details("EMP-2026-{$attempt}"));
        }

        $this->post(route('employees.verify.lookup'), $this->details())->assertTooManyRequests();
    }
}
