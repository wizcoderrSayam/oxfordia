<?php

namespace App\Notifications;

use App\Services\InstituteSettings;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * A reply written by staff to an enquiry or contact message.
 *
 * Sent immediately rather than queued so delivery errors are shown to the sender.
 */
class InstituteReply extends Notification
{
    public function __construct(
        public string $recipientName,
        public string $replySubject,
        public string $body,
    ) {}

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

        $message = (new MailMessage)
            ->subject($this->replySubject)
            ->greeting("Hello {$this->recipientName},")
            ->replyTo($settings->get('email'), $settings->get('name'));

        foreach (preg_split('/\R{2,}/', trim($this->body)) ?: [] as $paragraph) {
            $message->line(trim($paragraph));
        }

        return $message;
    }
}
