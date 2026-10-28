<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultLookupTest extends TestCase
{
    use RefreshDatabase;

    private Student $student;

    private Course $course;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = Student::factory()->create(['full_name' => 'Asha Verma', 'date_of_birth' => '2004-05-17']);
        $this->course = Course::factory()->withModules(2, 100, 40)->create(['title' => 'Python Programming']);
        $this->enrollment = Enrollment::factory()->passed('A+')->create([
            'enrollment_number' => 'ENR-2026-00042',
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
        ]);
    }

    /**
     * @return array{roll_number: string, dob_day: int, dob_month: int, dob_year: int}
     */
    private function details(string $rollNumber = 'ENR-2026-00042', string $dateOfBirth = '2004-05-17'): array
    {
        [$year, $month, $day] = array_map('intval', explode('-', $dateOfBirth));

        return ['roll_number' => $rollNumber, 'dob_day' => $day, 'dob_month' => $month, 'dob_year' => $year];
    }

    public function test_the_lookup_form_is_shown(): void
    {
        $this->get(route('results.index'))
            ->assertOk()
            ->assertSee('Roll number')
            ->assertSee('Date of birth')
            ->assertSee('name="dob_day"', false)
            ->assertSee('name="dob_month"', false)
            ->assertSee('name="dob_year"', false);
    }

    public function test_matching_roll_number_and_date_of_birth_show_the_result(): void
    {
        $this->enrollment->marks()->create(['course_module_id' => $this->course->modules()->value('id'), 'marks' => 87]);

        $this->post(route('results.lookup'), $this->details())
            ->assertOk()
            ->assertSee('Asha Verma')
            ->assertSee('ENR-2026-00042')
            ->assertSee('Python Programming')
            ->assertSee('A+')
            ->assertDontSee('Module-wise marks');
    }

    public function test_only_the_course_for_that_roll_number_is_shown(): void
    {
        $otherCourse = Course::factory()->create(['title' => 'Graphic Design Professional']);
        Enrollment::factory()->create(['student_id' => $this->student->id, 'course_id' => $otherCourse->id]);

        $this->post(route('results.lookup'), $this->details())
            ->assertSee('Python Programming')
            ->assertDontSee('Graphic Design Professional');
    }

    public function test_the_roll_number_is_matched_case_insensitively(): void
    {
        $this->post(route('results.lookup'), $this->details('  enr-2026-00042 '))
            ->assertOk()
            ->assertSee('Asha Verma');
    }

    public function test_a_wrong_date_of_birth_reveals_nothing(): void
    {
        $this->post(route('results.lookup'), $this->details(dateOfBirth: '2004-05-18'))
            ->assertOk()
            ->assertSee('No matching record found')
            ->assertSee('18 May 2004')
            ->assertDontSee('Asha Verma');
    }

    public function test_an_impossible_date_is_rejected(): void
    {
        $this->from(route('results.index'))
            ->post(route('results.lookup'), $this->details(dateOfBirth: '2004-02-31'))
            ->assertRedirect(route('results.index'))
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_an_unknown_roll_number_gives_the_same_response(): void
    {
        $this->post(route('results.lookup'), $this->details('ENR-2026-99999'))
            ->assertOk()
            ->assertSee('No matching record found');
    }

    public function test_the_student_registration_number_is_not_a_roll_number(): void
    {
        $this->post(route('results.lookup'), $this->details($this->student->registration_number))
            ->assertSee('No matching record found');
    }

    public function test_a_student_without_a_date_of_birth_on_record_cannot_be_found(): void
    {
        $this->student->update(['date_of_birth' => null]);

        $this->post(route('results.lookup'), $this->details())
            ->assertSee('No matching record found');
    }

    public function test_a_withdrawn_enrollment_is_not_shown(): void
    {
        $this->enrollment->update(['status' => 'withdrawn']);

        $this->post(route('results.lookup'), $this->details())
            ->assertSee('No matching record found')
            ->assertDontSee('Python Programming');
    }

    public function test_both_fields_are_required(): void
    {
        $this->from(route('results.index'))
            ->post(route('results.lookup'), [])
            ->assertRedirect(route('results.index'))
            ->assertSessionHasErrors(['roll_number', 'dob_day', 'dob_month', 'dob_year']);
    }

    public function test_the_lookup_is_rate_limited(): void
    {
        foreach (range(1, 10) as $attempt) {
            $this->post(route('results.lookup'), $this->details('ENR-2026-99999'))->assertOk();
        }

        $this->post(route('results.lookup'), $this->details('ENR-2026-99999'))->assertTooManyRequests();
    }
}
