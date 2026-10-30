<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Models\AdmissionEnquiry;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Student;
use App\Models\User;
use App\Notifications\PortalInvitation;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->staff()->create());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Anjali Varma',
            'email' => 'anjali@example.com',
            'phone' => '+91 98765 43210',
            'admission_date' => now()->toDateString(),
            'status' => 'active',
        ], $overrides);
    }

    public function test_registering_students_generates_unique_sequential_registration_numbers(): void
    {
        $this->post(route('admin.students.store'), $this->payload())->assertRedirect();
        $this->post(route('admin.students.store'), $this->payload(['full_name' => 'Second Student', 'email' => null]))->assertRedirect();

        $numbers = Student::query()->orderBy('id')->pluck('registration_number')->all();
        $year = now()->year;

        $this->assertSame(["OXF-{$year}-0001", "OXF-{$year}-0002"], $numbers);
    }

    public function test_registration_number_cannot_be_set_through_the_form(): void
    {
        $this->post(route('admin.students.store'), $this->payload(['registration_number' => 'HACKED-1']));

        $this->assertDatabaseMissing('students', ['registration_number' => 'HACKED-1']);
    }

    public function test_email_and_phone_are_validated(): void
    {
        $this->post(route('admin.students.store'), $this->payload(['email' => 'not-an-email', 'phone' => 'abc']))
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_profile_photo_must_be_an_image(): void
    {
        $this->post(route('admin.students.store'), $this->payload([
            'photo' => UploadedFile::fake()->create('cv.pdf', 50, 'application/pdf'),
        ]))->assertSessionHasErrors('photo');

        $this->post(route('admin.students.store'), $this->payload([
            'photo' => UploadedFile::fake()->image('me.png', 300, 300),
        ]))->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists(Student::query()->firstOrFail()->profile_photo_path);
    }

    public function test_students_can_be_searched_and_filtered(): void
    {
        Student::factory()->create(['full_name' => 'Findable Person']);
        Student::factory()->create(['full_name' => 'Other Person', 'status' => 'graduated']);

        $this->get(route('admin.students.index', ['q' => 'Findable']))->assertSee('Findable Person')->assertDontSee('Other Person');
        $this->get(route('admin.students.index', ['status' => 'graduated']))->assertSee('Other Person')->assertDontSee('Findable Person');
    }

    public function test_student_profile_shows_enrollments(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->get(route('admin.students.show', $enrollment->student))
            ->assertOk()
            ->assertSee($enrollment->course->title)
            ->assertSee($enrollment->enrollment_number);
    }

    public function test_deleting_a_student_also_deletes_their_enrollments_marks_and_result_documents(): void
    {
        Storage::fake('local');
        $course = Course::factory()->withModules(1)->create();
        $enrollment = Enrollment::factory()->create(['course_id' => $course->id, 'result_document_path' => 'result-documents/sheet.pdf']);
        $mark = $enrollment->marks()->create(['course_module_id' => $course->modules()->value('id'), 'marks' => 70]);
        Storage::disk('local')->put('result-documents/sheet.pdf', 'pdf');

        $this->delete(route('admin.students.destroy', $enrollment->student))
            ->assertRedirect(route('admin.students.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($enrollment->student);
        $this->assertModelMissing($enrollment);
        $this->assertModelMissing($mark);
        Storage::disk('local')->assertMissing('result-documents/sheet.pdf');
    }

    public function test_a_student_with_an_issued_certificate_cannot_be_deleted(): void
    {
        Notification::fake();
        $enrollment = Enrollment::factory()->passed()->create();
        app(CertificateService::class)->issue($enrollment, User::factory()->admin()->create());

        $this->delete(route('admin.students.destroy', $enrollment->student))->assertSessionHas('error');

        $this->assertModelExists($enrollment->student);
        $this->assertModelExists($enrollment);
    }

    public function test_a_student_with_a_payment_order_cannot_be_deleted(): void
    {
        $enrollment = Enrollment::factory()->create();
        Order::query()->forceCreate([
            'order_number' => 'ORD-2026-00001',
            'user_id' => User::factory()->create()->id,
            'course_id' => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'amount' => 1000,
            'gateway' => 'dummy',
        ]);

        $this->delete(route('admin.students.destroy', $enrollment->student))->assertSessionHas('error');

        $this->assertModelExists($enrollment->student);
    }

    public function test_the_student_list_offers_delete_unless_the_student_has_a_certificate(): void
    {
        Notification::fake();
        $enrolled = Enrollment::factory()->create()->student;
        $certified = Enrollment::factory()->passed()->create();
        app(CertificateService::class)->issue($certified, User::factory()->admin()->create());

        $this->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee(route('admin.students.edit', $enrolled), false)
            ->assertSee(route('admin.students.edit', $certified->student), false)
            ->assertSee('action="'.route('admin.students.destroy', $enrolled).'"', false)
            ->assertSee('along with their enrollment, marks and result documents')
            ->assertDontSee('action="'.route('admin.students.destroy', $certified->student).'"', false)
            ->assertSee("Students with an issued certificate or a payment order can't be deleted", false);
    }

    public function test_the_student_list_shows_the_date_of_birth(): void
    {
        Student::factory()->create(['date_of_birth' => '2004-05-17']);
        Student::factory()->create(['date_of_birth' => null]);

        $this->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('Date of birth')
            ->assertSee('17 May 2004')
            ->assertSee('Not set');
    }

    public function test_saving_an_edited_student_asks_for_confirmation(): void
    {
        $student = Student::factory()->create();

        $this->get(route('admin.students.edit', $student))
            ->assertOk()
            ->assertSee('form="student-form"', false)
            ->assertSee('Save changes?');
    }

    public function test_enquiry_can_be_converted_into_a_student(): void
    {
        $enquiry = AdmissionEnquiry::query()->create(['name' => 'Enquirer', 'phone' => '9876543210']);

        $this->get(route('admin.students.create', ['enquiry' => $enquiry->id]))->assertOk()->assertSee('Enquirer');

        $this->post(route('admin.students.store'), $this->payload(['full_name' => 'Enquirer', 'enquiry_id' => $enquiry->id]))->assertRedirect();

        $enquiry->refresh();
        $this->assertSame(EnquiryStatus::Converted, $enquiry->status);
        $this->assertNotNull($enquiry->student_id);
    }

    public function test_portal_invite_creates_a_linked_student_login(): void
    {
        Notification::fake();
        $student = Student::factory()->create(['email' => 'invitee@example.com']);

        $this->post(route('admin.students.invite', $student))->assertSessionHas('success');

        $user = User::query()->where('email', 'invitee@example.com')->firstOrFail();
        $this->assertTrue($user->isStudent());
        $this->assertSame($user->id, $student->fresh()->user_id);
        Notification::assertSentTo($user, PortalInvitation::class);
    }

    public function test_portal_invite_never_links_a_staff_account(): void
    {
        Notification::fake();
        $staff = User::factory()->staff()->create();
        $student = Student::factory()->create(['email' => $staff->email]);

        $this->post(route('admin.students.invite', $student))->assertSessionHas('error');

        $this->assertNull($student->fresh()->user_id);
    }
}
