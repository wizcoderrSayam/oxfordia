<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Services\ResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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

        $this->student = Student::factory()->create(['full_name' => 'Asha Verma', 'registration_number' => 'OXF-2026-0042', 'date_of_birth' => '2004-05-17']);
        $this->course = Course::factory()->withModules(2, 100, 40)->create(['title' => 'Python Programming']);
        $this->enrollment = Enrollment::factory()->passed('A+')->create([
            'enrollment_number' => 'ENR-2026-00042',
            'student_id' => $this->student->id,
            'course_id' => $this->course->id,
        ]);
    }

    /**
     * @return array{registration_number: string, dob_day: int, dob_month: int, dob_year: int}
     */
    private function details(string $number = 'OXF-2026-0042', string $dateOfBirth = '2004-05-17'): array
    {
        [$year, $month, $day] = array_map('intval', explode('-', $dateOfBirth));

        return ['registration_number' => $number, 'dob_day' => $day, 'dob_month' => $month, 'dob_year' => $year];
    }

    public function test_the_lookup_form_is_shown(): void
    {
        $this->get(route('results.index'))
            ->assertOk()
            ->assertSee('Registration number')
            ->assertSee('Date of birth')
            ->assertSee('name="dob_day"', false)
            ->assertSee('name="dob_month"', false)
            ->assertSee('name="dob_year"', false);
    }

    public function test_matching_registration_number_and_date_of_birth_show_the_result_and_marksheet(): void
    {
        [$firstModule, $secondModule] = $this->course->modules()->orderBy('sort_order')->get();
        $this->enrollment->marks()->create(['course_module_id' => $firstModule->id, 'marks' => 87]);

        $this->post(route('results.lookup'), $this->details())
            ->assertOk()
            ->assertSee('Asha Verma')
            ->assertSee('OXF-2026-0042')
            ->assertDontSee('Enrollment No')
            ->assertSee('Python Programming')
            ->assertSee('Marksheet')
            ->assertSee('A+')
            ->assertSeeInOrder([$firstModule->name, '87', $secondModule->name, 'Awaiting'])
            ->assertDontSee('Check another result');
    }

    public function test_the_marksheet_shows_theory_and_practical_marks_with_totals(): void
    {
        $course = Course::factory()->withTheoryAndPracticalModules()->create();
        $module = $course->modules()->firstOrFail();
        $this->enrollment->update(['course_id' => $course->id]);
        $this->enrollment->marks()->create(['course_module_id' => $module->id, 'theory_marks' => 50, 'practical_marks' => 26]);
        app(ResultService::class)->recalculate($this->enrollment->fresh());

        $this->post(route('results.lookup'), $this->details())
            ->assertOk()
            ->assertSeeInOrder(['Max. Marks', 'Min. Marks', 'Marks Obt.', 'Total Marks'])
            ->assertSeeInOrder([$module->name, '70', '30', '28', '12', '50', '26', '76'])
            ->assertSeeInOrder(['Grand Total', '100', '40', '76'])
            ->assertSeeInOrder(['Result:', 'PASS', 'Percentage', '76%', 'Grade:']);
    }

    public function test_the_result_page_links_to_the_marksheet_download(): void
    {
        $this->post(route('results.lookup'), $this->details())
            ->assertOk()
            ->assertSee('Download result')
            ->assertSee('/results/marksheet/'.$this->enrollment->id, false)
            ->assertSee('signature=', false);
    }

    public function test_the_marksheet_downloads_as_a_pdf_with_a_valid_signed_link(): void
    {
        $this->get(route('results.marksheet', $this->enrollment))->assertForbidden();

        $this->get(URL::temporarySignedRoute('results.marksheet', now()->subMinute(), ['enrollment' => $this->enrollment]))
            ->assertForbidden();

        $this->get(URL::temporarySignedRoute('results.marksheet', now()->addMinutes(30), ['enrollment' => $this->enrollment]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertDownload('ENR-2026-00042-marksheet.pdf');
    }

    public function test_a_withdrawn_enrollment_marksheet_cannot_be_downloaded(): void
    {
        $this->enrollment->update(['status' => 'withdrawn']);

        $this->get(URL::temporarySignedRoute('results.marksheet', now()->addMinutes(30), ['enrollment' => $this->enrollment]))
            ->assertNotFound();
    }

    public function test_a_registration_number_shows_every_course_the_student_is_enrolled_in(): void
    {
        $otherCourse = Course::factory()->create(['title' => 'Graphic Design Professional']);
        Enrollment::factory()->create(['student_id' => $this->student->id, 'course_id' => $otherCourse->id]);

        $this->post(route('results.lookup'), $this->details())
            ->assertSee('Python Programming')
            ->assertSee('Graphic Design Professional');
    }

    public function test_a_roll_number_still_works_and_shows_only_that_course(): void
    {
        $otherCourse = Course::factory()->create(['title' => 'Graphic Design Professional']);
        Enrollment::factory()->create(['student_id' => $this->student->id, 'course_id' => $otherCourse->id]);

        $this->post(route('results.lookup'), $this->details('ENR-2026-00042'))
            ->assertSee('Python Programming')
            ->assertDontSee('Graphic Design Professional');
    }

    public function test_the_registration_number_is_matched_case_insensitively(): void
    {
        $this->post(route('results.lookup'), $this->details('  oxf-2026-0042 '))
            ->assertOk()
            ->assertSee('Asha Verma');
    }

    public function test_another_students_date_of_birth_does_not_unlock_a_registration_number(): void
    {
        Student::factory()->create(['date_of_birth' => '1999-01-01']);

        $this->post(route('results.lookup'), $this->details(dateOfBirth: '1999-01-01'))
            ->assertSee('No matching record found')
            ->assertDontSee('Asha Verma');
    }

    public function test_a_wrong_date_of_birth_reveals_nothing(): void
    {
        $this->post(route('results.lookup'), $this->details(dateOfBirth: '2004-05-18'))
            ->assertOk()
            ->assertSee('No matching record found')
            ->assertSee('18 May 2004')
            ->assertSee('Check another result')
            ->assertDontSee('Asha Verma');
    }

    public function test_an_impossible_date_is_rejected(): void
    {
        $this->from(route('results.index'))
            ->post(route('results.lookup'), $this->details(dateOfBirth: '2004-02-31'))
            ->assertRedirect(route('results.index'))
            ->assertSessionHasErrors('date_of_birth');
    }

    public function test_an_unknown_registration_number_gives_the_same_response(): void
    {
        $this->post(route('results.lookup'), $this->details('OXF-2026-9999'))
            ->assertOk()
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
            ->assertSessionHasErrors(['registration_number', 'dob_day', 'dob_month', 'dob_year']);
    }

    public function test_the_lookup_is_rate_limited(): void
    {
        foreach (range(1, 10) as $attempt) {
            $this->post(route('results.lookup'), $this->details('OXF-2026-9999'))->assertOk();
        }

        $this->post(route('results.lookup'), $this->details('OXF-2026-9999'))->assertTooManyRequests();
    }
}
