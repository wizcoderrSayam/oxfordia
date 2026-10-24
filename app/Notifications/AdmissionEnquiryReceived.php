<?php

namespace App\Notifications;

use App\Models\AdmissionEnquiry;
use App\Services\InstituteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Acknowledges an admission enquiry to the visitor who submitted it.
 *
 * Anyone can type any address into the public form, so nothing the visitor typed is echoed back.
 */
class AdmissionEnquiryReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AdmissionEnquiry $enquiry) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $enquiry = $this->enquiry->loadMissing('course');
        $settings = app(InstituteSettings::class);

        $message = (new MailMessage)
            ->subject("We received your enquiry - {$settings->get('name')}")
            ->greeting('Hello,')
            ->line('Thank you for your interest in '.$settings->get('name').'. Our admissions team has received your enquiry and will contact you shortly.');

        if ($enquiry->course) {
            $message->line("Course of interest: {$enquiry->course->title}");
        }

        return $message
            ->action('Browse Courses', route('courses.index'))
            ->line("If you need help sooner, call us on {$settings->get('phone')}.")
            ->replyTo($settings->get('email'), $settings->get('name'));
    }
}
