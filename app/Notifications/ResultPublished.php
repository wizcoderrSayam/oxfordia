<?php

namespace App\Notifications;

use App\Enums\ResultStatus;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Tells a student their course result is available or has changed.
 */
class ResultPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Enrollment $enrollment, public bool $updated = false) {}

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
            ->subject($this->updated ? "Your result for {$enrollment->course->title} has been updated" : "Your result for {$enrollment->course->title}")
            ->greeting("Hello {$enrollment->student->full_name},")
            ->line($this->updated
                ? "Your result for {$enrollment->course->title} has been updated."
                : "Your result for {$enrollment->course->title} is now available.")
            ->line('Result: '.$enrollment->result?->label())
            ->line("Grade: {$enrollment->final_grade}")
            ->line('Percentage: '.number_format((float) $enrollment->percentage, 2).'%');

        if ($enrollment->result === ResultStatus::Pass) {
            $message->line('Congratulations! Your certificate will be issued once the course is marked complete.');
        }

        if ($notifiable instanceof User) {
            $message->action('View Module Marks', route('portal.enrollments.show', $enrollment));
        }

        return $message;
    }
}
