<?php

namespace App\Notifications;

use App\Services\InstituteSettings;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Invites a new account holder to choose a password via the password reset flow.
 *
 * Deliberately not queued: the payload contains a live password token.
 */
class PortalInvitation extends ResetPassword
{
    public function __construct(#[\SensitiveParameter] $token, public bool $backOffice = false)
    {
        parent::__construct($token);
    }

    /**
     * @param  string  $url
     */
    protected function buildMailMessage($url): MailMessage
    {
        $institute = app(InstituteSettings::class)->get('name');
        $expires = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject($this->backOffice ? "Your {$institute} staff account" : "Your {$institute} student portal access")
            ->greeting('Welcome!')
            ->line($this->backOffice
                ? "An account has been created for you on the {$institute} admin panel."
                : "A student portal account has been created for you at {$institute}. You can view your enrollments, results and certificates there.")
            ->action('Set Password', $url)
            ->line("This link expires in {$expires} minutes. You can request a new one from the \"Forgot password\" page.");
    }
}
