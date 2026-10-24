<?php

namespace App\Notifications;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateIssued extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Certificate $certificate) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $certificate = $this->certificate;

        return (new MailMessage)
            ->subject("Your certificate for {$certificate->course_title_snapshot}")
            ->greeting("Congratulations {$certificate->student_name_snapshot}!")
            ->line("Your certificate for {$certificate->course_title_snapshot} has been issued.")
            ->line("Certificate number: {$certificate->certificate_number}")
            ->action('Verify Certificate', $certificate->verificationUrl())
            ->line('You can download it anytime from the student portal.');
    }
}
