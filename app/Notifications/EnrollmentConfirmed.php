<?php

namespace App\Notifications;

use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a student they were enrolled in a course by the institute.
 */
class EnrollmentConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Enrollment $enrollment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $enrollment = $this->enrollment->loadMissing(['course', 'student']);

        $message = (new MailMessage)
            ->subject("You are enrolled in {$enrollment->course->title}")
            ->greeting("Hello {$enrollment->student->full_name},")
            ->line("You have been enrolled in {$enrollment->course->title}.")
            ->line("Enrollment number: {$enrollment->enrollment_number}")
            ->line("Registration number: {$enrollment->student->registration_number}");

        if ($enrollment->start_date) {
            $message->line('Start date: '.$enrollment->start_date->format('d M Y'));
        }

        if ($notifiable instanceof User) {
            $message->action('View in Student Portal', route('portal.enrollments.show', $enrollment));
        }

        return $message;
    }
}
