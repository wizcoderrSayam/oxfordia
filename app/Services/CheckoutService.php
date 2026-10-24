<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Notifications\PurchaseReceipt;
use App\Payments\PaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        private PaymentGateway $gateway,
        private NumberSequenceService $numbers,
        private StudentService $students,
        private EnrollmentService $enrollments,
        private ActivityLogger $logger,
    ) {}

    /**
     * Ensure the user may buy the course.
     *
     * @throws ValidationException
     */
    public function assertCanPurchase(User $user, Course $course): void
    {
        if (! $course->isPurchasable()) {
            throw ValidationException::withMessages(['course' => 'This course is not available for online purchase.']);
        }

        $student = $user->student()->first();

        if ($student && $this->enrollments->hasBlockingEnrollment($student, $course)) {
            throw ValidationException::withMessages(['course' => 'You are already enrolled in this course.']);
        }
    }

    /**
     * Create a pending order and return it.
     *
     * @param  array<string, mixed>  $customerDetails
     *
     * @throws ValidationException
     */
    public function start(User $user, Course $course, array $customerDetails): Order
    {
        $this->assertCanPurchase($user, $course);

        // Reuse an open order for the same course so a buyer cannot accumulate parallel payments.
        $order = $user->orders()
            ->where('course_id', $course->id)
            ->where('status', OrderStatus::Pending)
            ->latest('id')
            ->first() ?? new Order;

        if (! $order->exists) {
            $order->forceFill([
                'order_number' => $this->numbers->nextOrderNumber(),
                'user_id' => $user->id,
                'course_id' => $course->id,
                'currency' => 'INR',
                'status' => OrderStatus::Pending,
            ]);
        }

        $order->forceFill([
            'amount' => $course->fee,
            'gateway' => $this->gateway->name(),
            'customer_details' => $customerDetails,
        ])->save();

        return $order;
    }

    public function paymentUrl(Order $order): string
    {
        return $this->gateway->checkoutUrl($order);
    }

    /**
     * Record a successful payment. Safe to call more than once for the same order.
     */
    public function markPaid(Order $order, string $gatewayReference): Order
    {
        $paidOrder = DB::transaction(function () use ($order, $gatewayReference): ?Order {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! $locked->isPending()) {
                return null;
            }

            // Serialise every payment of this buyer so student creation and the duplicate check cannot race.
            User::query()->whereKey($locked->user_id)->lockForUpdate()->first();

            $locked->load(['user', 'course']);
            $details = $locked->customer_details ?? [];

            $student = $this->students->forUser($locked->user, array_filter([
                'full_name' => $details['full_name'] ?? null,
                'phone' => $details['phone'] ?? null,
                'date_of_birth' => $details['date_of_birth'] ?? null,
                'gender' => $details['gender'] ?? null,
                'address' => $details['address'] ?? null,
            ]));

            $locked->forceFill([
                'gateway_reference' => $gatewayReference,
                'paid_at' => now(),
            ]);

            $problem = match (true) {
                ! $locked->course->isPublished() => 'Paid after the course was withdrawn from sale. Refund or enroll manually.',
                $this->enrollments->hasBlockingEnrollment($student, $locked->course) => 'Paid while an active enrollment already existed. Refund or adjust manually.',
                default => null,
            };

            $enrollment = null;

            if ($problem === null) {
                try {
                    $enrollment = $this->enrollments->enroll($student, $locked->course);
                } catch (ValidationException) {
                    // A staff member enrolled the student concurrently; the money must still be recorded.
                    $problem = 'Paid while an active enrollment already existed. Refund or adjust manually.';
                }
            }

            if ($enrollment === null) {
                // The gateway has taken the money, so the payment is always recorded and flagged for refund.
                $locked->forceFill([
                    'status' => OrderStatus::Paid,
                    'failure_reason' => $problem,
                ])->save();

                $this->logger->log('order.needs_refund', $locked, "Order {$locked->order_number} paid but not enrolled", ['reason' => $problem]);

                return null;
            }

            $locked->forceFill([
                'status' => OrderStatus::Paid,
                'enrollment_id' => $enrollment->id,
            ])->save();

            $this->logger->log('order.paid', $locked, "Order {$locked->order_number} paid");

            return $locked;
        });

        if ($paidOrder) {
            $paidOrder->user->notify(new PurchaseReceipt($paidOrder));

            return $paidOrder;
        }

        return $order->refresh();
    }

    /**
     * Record a failed payment attempt.
     */
    public function markFailed(Order $order, string $reason): Order
    {
        return DB::transaction(function () use ($order, $reason): Order {
            /** @var Order $locked */
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->isPending()) {
                $locked->forceFill([
                    'status' => OrderStatus::Failed,
                    'failure_reason' => $reason,
                ])->save();
            }

            return $locked;
        });
    }
}
