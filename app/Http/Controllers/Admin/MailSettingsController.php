<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Notifications\TestEmail;
use App\Services\ActivityLogger;
use App\Services\InstituteSettings;
use App\Services\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class MailSettingsController extends Controller
{
    public function update(Request $request, InstituteSettings $settings, ActivityLogger $logger): RedirectResponse
    {
        $smtpRequired = Rule::requiredIf($request->input('mail_mailer') === 'smtp');

        $validated = $request->validate([
            'mail_mailer' => ['nullable', 'in:smtp'],
            'mail_host' => [$smtpRequired, 'nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9.\-]+$/'],
            'mail_port' => [$smtpRequired, 'nullable', 'integer', 'between:1,65535'],
            'mail_encryption' => [$smtpRequired, 'nullable', Rule::in(array_keys(MailSettings::ENCRYPTIONS))],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'forget_mail_password' => ['nullable', 'boolean'],
            'mail_from_address' => [$smtpRequired, 'nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:120', 'not_regex:/[\r\n]/'],
            'mail_auto_replies' => ['nullable', 'boolean'],
        ], [
            'mail_host.regex' => 'Enter only the server name, for example smtp.hostinger.com.',
        ]);

        $values = collect($validated)->except(['mail_password', 'forget_mail_password'])->all();
        $values['mail_mailer'] = $validated['mail_mailer'] ?? null;
        $values['mail_auto_replies'] = $request->boolean('mail_auto_replies') ? '1' : '0';

        if (filled($validated['mail_password'] ?? null)) {
            $values['mail_password'] = Crypt::encryptString($validated['mail_password']);
        } elseif ($request->boolean('forget_mail_password')) {
            $values['mail_password'] = null;
        }

        $settings->set($values);

        $logger->log('settings.mail_updated', null, 'Email delivery settings updated');

        return redirect()->to(route('admin.settings.edit').'#email')->with('success', 'Email settings saved. Send a test email to check them.');
    }

    public function test(Request $request, ActivityLogger $logger, MailSettings $mail): RedirectResponse
    {
        $validated = $request->validate([
            'test_recipient' => ['required', 'email', 'max:255'],
        ]);

        try {
            Notification::route('mail', $validated['test_recipient'])->notifyNow(new TestEmail);
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return redirect()->to(route('admin.settings.edit').'#email')
                ->withInput()
                ->with('error', 'The test email could not be sent: '.$exception->getMessage());
        }

        $logger->log('settings.mail_tested', null, 'Test email sent');

        return redirect()->to(route('admin.settings.edit').'#email')
            ->withInput()
            ->with('success', "Test email sent to {$validated['test_recipient']} using ".($mail->usesCustomSmtp() ? 'the saved SMTP server.' : 'the server default (.env) mailer.'));
    }
}
