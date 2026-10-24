<?php

namespace App\Services;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * SMTP delivery settings saved from the admin panel and applied over config/mail.php.
 *
 * When no custom SMTP server is configured, the MAIL_* values from .env are used unchanged.
 */
class MailSettings
{
    /**
     * Supported connection security options and their labels.
     */
    public const ENCRYPTIONS = [
        'ssl' => 'SSL (usually port 465)',
        'tls' => 'TLS / STARTTLS (usually port 587)',
        'none' => 'None',
    ];

    public function __construct(private InstituteSettings $settings) {}

    public function usesCustomSmtp(): bool
    {
        return $this->settings->get('mail_mailer') === 'smtp' && filled($this->settings->get('mail_host'));
    }

    public function sendsAutoReplies(): bool
    {
        return (bool) $this->settings->get('mail_auto_replies');
    }

    /**
     * Whether an acknowledgement may be emailed to this visitor address.
     *
     * Public forms accept any address, so each one gets at most three acknowledgements a day.
     */
    public function mayAcknowledge(?string $email): bool
    {
        if (blank($email) || ! $this->sendsAutoReplies()) {
            return false;
        }

        return RateLimiter::attempt('auto-reply:'.sha1(Str::lower($email)), 3, fn (): bool => true, 86400);
    }

    /**
     * Whether a saved password can no longer be decrypted, usually after the APP_KEY changed.
     */
    public function passwordIsUnreadable(): bool
    {
        return filled($this->settings->get('mail_password')) && $this->password() === null;
    }

    /**
     * The decrypted SMTP password, or null when unset or unreadable after an APP_KEY change.
     */
    public function password(): ?string
    {
        $encrypted = $this->settings->get('mail_password');

        if (blank($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            return null;
        }
    }

    /**
     * Point the default mailer at the saved SMTP server. Call before the mailer is built.
     */
    public function apply(): void
    {
        if (! $this->usesCustomSmtp()) {
            return;
        }

        $encryption = $this->settings->get('mail_encryption');

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => array_merge(config('mail.mailers.smtp', []), [
                'transport' => 'smtp',
                'url' => null,
                'scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
                'host' => $this->settings->get('mail_host'),
                'port' => (int) ($this->settings->get('mail_port') ?: ($encryption === 'ssl' ? 465 : 587)),
                'username' => $this->settings->get('mail_username'),
                'password' => $this->password(),
                'timeout' => 15,
                'auto_tls' => $encryption !== 'none',
                'require_tls' => $encryption === 'tls',
            ]),
            'mail.from.address' => $this->settings->get('mail_from_address') ?: config('mail.from.address'),
            'mail.from.name' => $this->settings->get('mail_from_name') ?: $this->settings->get('name'),
        ]);
    }
}
