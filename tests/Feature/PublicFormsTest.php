<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\User;
use App\Notifications\NewAdmissionEnquiry;
use App\Notifications\NewContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublicFormsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_admission_enquiry_is_stored_and_alerts_the_institute(): void
    {
        $course = Course::factory()->create();

        $this->post(route('admissions.store'), [
            'name' => 'Prospective Student',
            'phone' => '9876543210',
            'email' => 'prospect@example.com',
            'course_id' => $course->id,
            'message' => 'Evening batch?',
        ])->assertRedirect(route('admissions.create'))->assertSessionHas('success');

        $enquiry = AdmissionEnquiry::query()->firstOrFail();
        $this->assertSame(EnquiryStatus::New, $enquiry->status);
        $this->assertNull($enquiry->student_id);
        $this->assertDatabaseCount('students', 0);
        Notification::assertSentOnDemand(NewAdmissionEnquiry::class);
    }

    public function test_admission_enquiry_is_validated(): void
    {
        $draft = Course::factory()->draft()->create();

        $this->post(route('admissions.store'), ['name' => '', 'phone' => 'abc', 'email' => 'bad', 'course_id' => $draft->id])
            ->assertSessionHasErrors(['name', 'phone', 'email', 'course_id']);

        $this->assertDatabaseCount('admission_enquiries', 0);
    }

    public function test_contact_message_is_stored(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Visitor',
            'email' => 'visitor@example.com',
            'subject' => 'Question',
            'message' => 'Do you offer weekend classes?',
        ])->assertRedirect(route('contact.create'));

        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com', 'status' => 'new']);
        Notification::assertSentOnDemand(NewContactMessage::class);
    }

    public function test_contact_message_is_validated(): void
    {
        $this->post(route('contact.store'), ['name' => '', 'email' => 'nope', 'message' => 'short'])
            ->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_public_forms_are_rate_limited(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post(route('contact.store'), ['name' => 'V', 'email' => 'v@example.com', 'message' => 'A long enough message']);
        }

        $this->post(route('contact.store'), ['name' => 'V', 'email' => 'v@example.com', 'message' => 'A long enough message'])
            ->assertStatus(429);

        $this->assertSame(5, ContactMessage::query()->count());
    }

    public function test_staff_can_review_and_update_enquiries(): void
    {
        $enquiry = AdmissionEnquiry::query()->create(['name' => 'Lead', 'phone' => '9876543210']);
        $this->actingAs(User::factory()->staff()->create());

        $this->get(route('admin.enquiries.index'))->assertOk()->assertSee('Lead');
        $this->patch(route('admin.enquiries.update', $enquiry), ['status' => 'contacted'])->assertRedirect();

        $this->assertSame(EnquiryStatus::Contacted, $enquiry->fresh()->status);
    }
}
