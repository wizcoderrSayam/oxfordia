<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use App\Services\InstituteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Acknowledges a contact form message to the visitor who sent it.
 *
 * Anyone can type any address into the public form, so nothing the visitor typed is echoed back.
 */
class ContactMessageReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactMessage $contactMessage) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $settings = app(InstituteSettings::class);

        return (new MailMessage)
            ->subject("Thanks for contacting {$settings->get('name')}")
            ->greeting('Hello,')
            ->line('Thank you for your message. We have received it and will get back to you soon.')
            ->line("Office hours: {$settings->get('office_hours')}")
            ->replyTo($settings->get('email'), $settings->get('name'));
    }
}
