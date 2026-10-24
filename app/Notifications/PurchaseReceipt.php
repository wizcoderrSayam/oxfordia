<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseReceipt extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order->loadMissing(['course', 'enrollment.student']);

        return (new MailMessage)
            ->subject("Payment received: {$order->order_number}")
            ->greeting("Hello {$order->enrollment?->student->full_name},")
            ->line("Thank you for enrolling in {$order->course->title}.")
            ->line("Order number: {$order->order_number}")
            ->line('Amount paid: ₹'.number_format((float) $order->amount, 2))
            ->line("Registration number: {$order->enrollment?->student->registration_number}")
            ->line("Enrollment number: {$order->enrollment?->enrollment_number}")
            ->action('Go to Student Portal', route('portal.dashboard'));
    }
}
