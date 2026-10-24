<?php

namespace App\Notifications;

use App\Models\AdmissionEnquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAdmissionEnquiry extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject("New admission enquiry from {$enquiry->name}")
            ->line("Name: {$enquiry->name}")
            ->line("Phone: {$enquiry->phone}")
            ->line('Course: '.($enquiry->course?->title ?? 'Not specified'))
            ->action('Review Enquiry', route('admin.enquiries.show', $enquiry));
    }
}
