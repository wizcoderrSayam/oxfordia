<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ResultDocumentTest extends TestCase
{
    use RefreshDatabase;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $student = Student::factory()->create(['full_name' => 'Rachhpal Singh', 'registration_number' => 'OXF-2023-0018', 'father_name' => 'Gurmeet Singh', 'date_of_birth' => '2001-03-09']);
        $course = Course::factory()->create(['title' => 'ADCA', 'duration_value' => 2, 'duration_unit' => 'years']);
        $this->enrollment = Enrollment::factory()->passed('A')->create([
            'enrollment_number' => 'FGI/KH001/2023018',
            'student_id' => $student->id,
            'course_id' => $course->id,
            'start_date' => '2023-07-01',
            'expected_completion_date' => '2025-06-30',
            'completion_date' => null,
        ]);
    }

    private function upload(UploadedFile $file): TestResponse
    {
        return $this->actingAs(User::factory()->staff()->create())
            ->post(route('admin.enrollments.result-document.store', $this->enrollment), ['result_document' => $file]);
    }

    private function lookup(): TestResponse
    {
        return $this->post(route('results.lookup'), ['registration_number' => 'FGI/KH001/2023018', 'dob_day' => 9, 'dob_month' => 3, 'dob_year' => 2001]);
    }

    public function test_staff_can_upload_a_result_document(): void
    {
        $this->upload(UploadedFile::fake()->create('result.pdf', 300, 'application/pdf'))
            ->assertRedirect(route('admin.enrollments.show', $this->enrollment))
            ->assertSessionHasNoErrors();

        $this->enrollment->refresh();
        $this->assertSame('result.pdf', $this->enrollment->result_document_name);
        Storage::disk('local')->assertExists($this->enrollment->result_document_path);
    }

    public function test_only_images_and_pdfs_up_to_five_megabytes_are_accepted(): void
    {
        $this->upload(UploadedFile::fake()->create('notes.txt', 10, 'text/plain'))->assertSessionHasErrors('result_document');
        $this->upload(UploadedFile::fake()->create('result.pdf', 6000, 'application/pdf'))->assertSessionHasErrors('result_document');

        $this->assertNull($this->enrollment->fresh()->result_document_path);
    }

    public function test_replacing_a_document_deletes_the_old_file(): void
    {
        $this->upload(UploadedFile::fake()->image('first.jpg'));
        $oldPath = $this->enrollment->fresh()->result_document_path;

        $this->upload(UploadedFile::fake()->image('second.png'));

        Storage::disk('local')->assertMissing($oldPath);
        $this->assertSame('second.png', $this->enrollment->fresh()->result_document_name);
    }

    public function test_staff_can_remove_a_document(): void
    {
        $this->upload(UploadedFile::fake()->image('result.jpg'));
        $path = $this->enrollment->fresh()->result_document_path;

        $this->delete(route('admin.enrollments.result-document.destroy', $this->enrollment))
            ->assertRedirect(route('admin.enrollments.show', $this->enrollment));

        Storage::disk('local')->assertMissing($path);
        $this->assertNull($this->enrollment->fresh()->result_document_path);
    }

    public function test_students_cannot_upload_documents(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.enrollments.result-document.store', $this->enrollment), ['result_document' => UploadedFile::fake()->image('result.jpg')])
            ->assertForbidden();
    }

    public function test_the_result_page_shows_the_details_but_not_the_uploaded_document(): void
    {
        $this->upload(UploadedFile::fake()->image('result.jpg'));
        auth()->logout();

        $this->lookup()
            ->assertOk()
            ->assertSee('Statement of Result')
            ->assertSeeInOrder(['Registration No', 'OXF-2023-0018', 'Student Name', 'Rachhpal Singh', 'Father Name', 'Gurmeet Singh', 'Course', 'ADCA'])
            ->assertDontSee('01/07/2023 – 30/06/2025')
            ->assertDontSee('2 Years')
            ->assertDontSee('Official copy')
            ->assertDontSee('/results/document/'.$this->enrollment->id, false);
    }

    public function test_the_document_needs_a_valid_signed_link(): void
    {
        $this->upload(UploadedFile::fake()->image('result.jpg'));
        auth()->logout();

        $this->get(route('results.document', $this->enrollment))->assertForbidden();

        $this->get(URL::temporarySignedRoute('results.document', now()->addMinutes(30), ['enrollment' => $this->enrollment]))
            ->assertOk();

        $this->get(URL::temporarySignedRoute('results.document', now()->subMinute(), ['enrollment' => $this->enrollment]))
            ->assertForbidden();
    }
}
