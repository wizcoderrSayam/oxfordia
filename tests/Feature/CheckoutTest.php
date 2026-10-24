<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Notifications\PurchaseReceipt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->buyer = User::factory()->create(['name' => 'Buyer Person']);
        $this->course = Course::factory()->create(['fee' => 4999]);
    }

    /**
     * @return array<string, mixed>
     */
    private function details(): array
    {
        return ['full_name' => 'Buyer Person', 'phone' => '9876543210', 'terms' => '1'];
    }

    private function startOrder(): Order
    {
        $this->actingAs($this->buyer)
            ->post(route('checkout.store', $this->course), $this->details())
            ->assertRedirectContains('/payments/dummy/');

        return Order::query()->latest('id')->firstOrFail();
    }

    private function completeUrl(Order $order): string
    {
        return URL::temporarySignedRoute('payments.dummy.complete', now()->addMinutes(30), ['order' => $order]);
    }

    public function test_guests_must_log_in_before_checkout(): void
    {
        $this->get(route('checkout.create', $this->course))->assertRedirect(route('login'));
    }

    public function test_unverified_students_must_verify_their_email_before_checkout(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('checkout.create', $this->course))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_successful_payment_creates_student_and_enrollment(): void
    {
        $order = $this->startOrder();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertEquals(4999, (float) $order->amount);

        $this->post($this->completeUrl($order), ['outcome' => 'success'])->assertRedirect(route('checkout.show', $order));

        $order->refresh();
        $student = $this->buyer->fresh()->student;

        $this->assertSame(OrderStatus::Paid, $order->status);
        $this->assertNotNull($student);
        $this->assertSame('9876543210', $student->phone);
        $this->assertStringStartsWith('OXF-', $student->registration_number);
        $this->assertSame(EnrollmentStatus::Enrolled, $order->enrollment->status);
        $this->assertSame($student->id, $order->enrollment->student_id);
        Notification::assertSentTo($this->buyer, PurchaseReceipt::class);

        $this->get(route('portal.dashboard'))->assertOk()->assertSee($this->course->title);
    }

    public function test_failed_payment_does_not_enroll(): void
    {
        $order = $this->startOrder();

        $this->post($this->completeUrl($order), ['outcome' => 'failure']);

        $this->assertSame(OrderStatus::Failed, $order->fresh()->status);
        $this->assertDatabaseCount('enrollments', 0);
    }

    public function test_replayed_callbacks_do_not_enroll_twice(): void
    {
        $order = $this->startOrder();
        $url = $this->completeUrl($order);

        $this->post($url, ['outcome' => 'success']);
        $this->post($url, ['outcome' => 'success']);
        $this->post($url, ['outcome' => 'failure']);

        $this->assertSame(1, Enrollment::query()->count());
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
    }

    public function test_unsigned_payment_callbacks_are_rejected(): void
    {
        $order = $this->startOrder();

        $this->post(route('payments.dummy.complete', $order), ['outcome' => 'success'])->assertForbidden();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_users_cannot_pay_or_view_other_users_orders(): void
    {
        $order = $this->startOrder();

        $this->actingAs(User::factory()->create())->post($this->completeUrl($order), ['outcome' => 'success'])->assertNotFound();
        $this->actingAs(User::factory()->create())->get(route('checkout.show', $order))->assertNotFound();
    }

    public function test_already_enrolled_students_cannot_buy_the_same_course(): void
    {
        $order = $this->startOrder();
        $this->post($this->completeUrl($order), ['outcome' => 'success']);

        $this->post(route('checkout.store', $this->course), $this->details())->assertSessionHas('error');

        $this->assertSame(1, Order::query()->count());
    }

    public function test_enquiry_only_and_unpublished_courses_cannot_be_bought(): void
    {
        $enquiryOnly = Course::factory()->enquiryOnly()->create();
        $draft = Course::factory()->draft()->create();

        $this->actingAs($this->buyer)->post(route('checkout.store', $enquiryOnly), $this->details())->assertSessionHas('error');
        $this->actingAs($this->buyer)->post(route('checkout.store', $draft), $this->details())->assertNotFound();

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_details_are_validated(): void
    {
        $this->actingAs($this->buyer)
            ->post(route('checkout.store', $this->course), ['full_name' => '', 'phone' => 'x'])
            ->assertSessionHasErrors(['full_name', 'phone', 'terms']);
    }

    public function test_staff_accounts_cannot_use_checkout(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->get(route('checkout.create', $this->course))
            ->assertRedirect(route('admin.dashboard'));
    }
}
