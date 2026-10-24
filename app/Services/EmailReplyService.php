<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Notifications\InstituteReply;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Sends staff replies to enquiries and contact messages, keeping a record in the activity log.
 */
class EmailReplyService
{
    public const LOG_ACTION = 'email.replied';

    public function __construct(private ActivityLogger $logger) {}

    /**
     * Send the reply immediately. Only the subject is logged, never the body.
     *
     * @throws TransportExceptionInterface
     */
    public function send(Model $record, string $email, string $name, string $subject, string $body): void
    {
        Notification::route('mail', $email)->notifyNow(new InstituteReply($name, $subject, $body));

        $this->logger->log(self::LOG_ACTION, $record, "Email reply sent: {$subject}");
    }

    /**
     * Replies already sent for a record, newest first.
     *
     * @return Collection<int, ActivityLog>
     */
    public function history(Model $record): Collection
    {
        return ActivityLog::query()
            ->with('user')
            ->where('action', self::LOG_ACTION)
            ->where('subject_type', $record->getMorphClass())
            ->where('subject_id', $record->getKey())
            ->latest('id')
            ->get();
    }

    /**
     * Validation rules for the reply form.
     *
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        return [
            'reply_subject' => ['required', 'string', 'max:200', 'not_regex:/[\r\n]/'],
            'reply_body' => ['required', 'string', 'max:5000'],
        ];
    }
}
