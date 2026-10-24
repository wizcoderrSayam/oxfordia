<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Enums\OrderStatus;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Payments\PaymentGateway;
use App\Services\CertificateService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;
use LogicException;
use Tests\TestCase;

/**
 * Regression tests for issues raised in code review.
 */
class ReviewRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_portal_invite_refuses_to_link_an_unverified_account_with_the_same_email(): void
    {
        $squatter = User::factory()->unverified()->create(['email' => 'victim@example.com']);
        $student = Student::factory()->create(['email' => 'victim@example.com']);

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('admin.students.invite', $student))
            ->assertSessionHas('error');

        $this->assertNull($student->fresh()->user_id);
        $this->assertNull($squatter->fresh()->student);
    }

    public function test_portal_invite_links_a_verified_account_with_the_same_email(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $student = Student::factory()->create(['email' => 'owner@example.com']);

        $this->actingAs(User::factory()->staff()->create())
            ->post(route('admin.students.invite', $student))
            ->assertSessionHas('success');

        $this->assertSame($owner->id, $student->fresh()->user_id);
    }

    public function test_reactivating_a_withdrawn_enrollment_cannot_create_a_duplicate(): void
    {
        $active = Enrollment::factory()->create();
        $withdrawn = Enrollment::factory()->withdrawn()->create([
            'student_id' => $active->student_id,
            'course_id' => $active->course_id,
        ]);

        $this->actingAs(User::factory()->staff()->create())
            ->put(route('admin.enrollments.update', $withdrawn), [
                'status' => 'enrolled',
                'enrollment_date' => $withdrawn->enrollment_date->toDateString(),
            ])->assertSessionHasErrors('status');
    }

    public function test_payment_for_a_course_withdrawn_from_sale_is_recorded_for_refund(): void
    {
        $buyer = User::factory()->create();
        $course = Course::factory()->create(['fee' => 999]);
        $checkout = app(CheckoutService::class);
        $order = $checkout->start($buyer, $course, ['full_name' => 'Buyer', 'phone' => '9876543210']);

        $course->update(['status' => CourseStatus::Archived]);
        $checkout->markPaid($order, 'REF-1');

        $order->refresh();
        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertSame('REF-1', $order->gateway_reference);
        $this->assertNull($order->enrollment_id);
        $this->assertNotNull($order->failure_reason);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_starting_checkout_twice_reuses_the_pending_order(): void
    {
        $buyer = User::factory()->create();
        $course = Course::factory()->create(['fee' => 999]);
        $checkout = app(CheckoutService::class);

        $first = $checkout->start($buyer, $course, ['full_name' => 'Buyer', 'phone' => '9876543210']);
        $second = $checkout->start($buyer, $course, ['full_name' => 'Buyer', 'phone' => '9123456780']);

        $this->assertSame($first->id, $second->id);
        $this->assertSame('9123456780', $second->customer_details['phone']);
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_issued_certificate_snapshots_cannot_be_edited(): void
    {
        $certificate = app(CertificateService::class)->issue(
            Enrollment::factory()->passed()->create(),
            User::factory()->admin()->create(),
        );

        $this->expectException(LogicException::class);

        $certificate->forceFill(['student_name_snapshot' => 'Someone Else'])->save();
    }

    public function test_dummy_gateway_is_refused_in_production(): void
    {
        $this->app['env'] = 'production';

        try {
            $this->expectException(InvalidArgumentException::class);
            app(PaymentGateway::class);
        } finally {
            $this->app['env'] = 'testing';
        }
    }

    public function test_certificate_number_is_immutable_but_revocation_still_works(): void
    {
        $certificate = app(CertificateService::class)->issue(
            Enrollment::factory()->passed()->create(),
            $admin = User::factory()->admin()->create(),
        );

        app(CertificateService::class)->revoke($certificate, $admin, 'Issued in error');

        $this->assertFalse(Certificate::query()->firstOrFail()->isValid());
    }
}
