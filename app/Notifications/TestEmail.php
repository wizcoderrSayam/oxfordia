<?php

namespace App\Notifications;

use App\Services\InstituteSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirms the mail settings work. Sent immediately so delivery errors reach the admin.
 */
class TestEmail extends Notification
{
    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $institute = app(InstituteSettings::class)->get('name');

        return (new MailMessage)
            ->subject("Test email from {$institute}")
            ->line('This is a test email sent from the admin settings page.')
            ->line('If you are reading this, outgoing email is working.');
    }
}
