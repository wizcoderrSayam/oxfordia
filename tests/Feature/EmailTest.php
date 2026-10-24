<?php

namespace Tests\Feature;

use App\Enums\EnquiryStatus;
use App\Models\ActivityLog;
use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use App\Notifications\AdmissionEnquiryReceived;
use App\Notifications\ContactMessageReceived;
use App\Notifications\EnrollmentConfirmed;
use App\Notifications\InstituteReply;
use App\Notifications\ResultPublished;
use App\Notifications\TestEmail;
use App\Services\InstituteSettings;
use App\Services\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /**
     * @return array<string, mixed>
     */
    private function smtpPayload(array $overrides = []): array
    {
        return array_merge([
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.hostinger.com',
            'mail_port' => 465,
            'mail_encryption' => 'ssl',
            'mail_username' => 'info@oxfordia.test',
            'mail_password' => 'secret-pass',
            'mail_from_address' => 'info@oxfordia.test',
            'mail_from_name' => 'Oxfordia Admissions',
            'mail_auto_replies' => '1',
        ], $overrides);
    }

    private function freshSettings(): InstituteSettings
    {
        $settings = app(InstituteSettings::class);
        $settings->flush();

        return $settings;
    }

    private function sentTo(string $email): \Closure
    {
        return fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === $email;
    }

    public function test_admin_can_save_smtp_settings_with_an_encrypted_password(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.mail.update'), $this->smtpPayload())
            ->assertRedirect(route('admin.settings.edit').'#email');

        $settings = $this->freshSettings();
        $this->assertSame('smtp.hostinger.com', $settings->get('mail_host'));
        $this->assertNotSame('secret-pass', $settings->get('mail_password'));
        $this->assertSame('secret-pass', Crypt::decryptString($settings->get('mail_password')));

        app(MailSettings::class)->apply();
        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('secret-pass', config('mail.mailers.smtp.password'));
        $this->assertSame('Oxfordia Admissions', config('mail.from.name'));
    }

    public function test_blank_password_keeps_the_saved_one_and_it_can_be_removed(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $this->put(route('admin.settings.mail.update'), $this->smtpPayload());

        $this->put(route('admin.settings.mail.update'), $this->smtpPayload(['mail_password' => '']));
        $this->freshSettings();
        $this->assertSame('secret-pass', app(MailSettings::class)->password());

        $this->put(route('admin.settings.mail.update'), $this->smtpPayload(['mail_password' => '', 'forget_mail_password' => '1']));
        $this->freshSettings();
        $this->assertNull(app(MailSettings::class)->password());
    }

    public function test_server_default_mailer_leaves_the_env_configuration_alone(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.mail.update'), ['mail_mailer' => '', 'mail_auto_replies' => '0'])
            ->assertSessionHasNoErrors();

        $this->freshSettings();
        app(MailSettings::class)->apply();
        $this->assertSame('array', config('mail.default'));
        $this->assertFalse(app(MailSettings::class)->sendsAutoReplies());
    }

    public function test_smtp_settings_are_validated(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.mail.update'), $this->smtpPayload([
            'mail_host' => 'smtp://bad host',
            'mail_port' => 70000,
            'mail_encryption' => 'starttls',
            'mail_from_address' => '',
        ]))->assertSessionHasErrors(['mail_host', 'mail_port', 'mail_encryption', 'mail_from_address']);
    }

    public function test_staff_cannot_change_mail_settings(): void
    {
        $this->actingAs(User::factory()->staff()->create());

        $this->put(route('admin.settings.mail.update'), $this->smtpPayload())->assertForbidden();
        $this->post(route('admin.settings.mail.test'), ['test_recipient' => 'a@example.com'])->assertForbidden();
    }

    public function test_admin_can_send_a_test_email(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Email delivery');

        $this->post(route('admin.settings.mail.test'), ['test_recipient' => 'check@example.com'])
            ->assertSessionHas('success');

        Notification::assertSentOnDemand(TestEmail::class, $this->sentTo('check@example.com'));
    }

    public function test_emails_use_the_institute_branding(): void
    {
        app(InstituteSettings::class)->set(['name' => 'Oxfordia Academy', 'phone' => '+91 91111 22222']);

        $html = (string) (new TestEmail)->toMail(new AnonymousNotifiable)->render();

        $this->assertStringContainsString('Oxfordia Academy', $html);
        $this->assertStringContainsString('+91 91111 22222', $html);
        $this->assertStringNotContainsString('Laravel', $html);
    }

    public function test_visitors_receive_acknowledgements(): void
    {
        $this->post(route('admissions.store'), ['name' => 'Asha', 'phone' => '9876543210', 'email' => 'asha@example.com']);
        $this->post(route('contact.store'), ['name' => 'Ravi', 'email' => 'ravi@example.com', 'message' => 'Do you have weekend batches?']);

        Notification::assertSentOnDemand(AdmissionEnquiryReceived::class, $this->sentTo('asha@example.com'));
        Notification::assertSentOnDemand(ContactMessageReceived::class, $this->sentTo('ravi@example.com'));
    }

    public function test_acknowledgements_are_skipped_when_disabled_or_without_an_email(): void
    {
        app(InstituteSettings::class)->set(['mail_auto_replies' => '0']);
        $this->post(route('contact.store'), ['name' => 'Ravi', 'email' => 'ravi@example.com', 'message' => 'Do you have weekend batches?']);

        app(InstituteSettings::class)->set(['mail_auto_replies' => '1']);
        $this->post(route('admissions.store'), ['name' => 'Asha', 'phone' => '9876543210']);

        Notification::assertSentOnDemandTimes(ContactMessageReceived::class, 0);
        Notification::assertSentOnDemandTimes(AdmissionEnquiryReceived::class, 0);
    }

    public function test_acknowledgements_are_capped_per_address_and_do_not_echo_visitor_text(): void
    {
        foreach (range(1, 4) as $attempt) {
            $this->post(route('contact.store'), [
                'name' => '[Claim your refund](https://evil.test)',
                'email' => 'victim@example.com',
                'subject' => 'Visit https://evil.test',
                'message' => 'Some message long enough.',
            ]);
        }

        Notification::assertSentOnDemandTimes(ContactMessageReceived::class, 3);
        Notification::assertSentOnDemand(ContactMessageReceived::class, function (ContactMessageReceived $notification, $channels, AnonymousNotifiable $notifiable) {
            $html = (string) $notification->toMail($notifiable)->render();

            return ! str_contains($html, 'evil.test');
        });
    }

    public function test_smtp_password_is_never_flashed_to_the_session(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.mail.update'), $this->smtpPayload(['mail_port' => 'not-a-port']))
            ->assertSessionHasErrors('mail_port')
            ->assertSessionHasInput('mail_host')
            ->assertSessionMissing('_old_input.mail_password');
    }

    public function test_settings_page_warns_when_the_saved_password_cannot_be_decrypted(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        app(InstituteSettings::class)->set(['mail_password' => 'encrypted-with-an-old-key']);

        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('can no longer be read');
    }

    public function test_admin_enrollment_emails_the_student(): void
    {
        $this->actingAs(User::factory()->staff()->create());
        $student = Student::factory()->create();

        $this->post(route('admin.enrollments.store'), [
            'student_id' => $student->id,
            'course_id' => Course::factory()->create()->id,
            'enrollment_date' => now()->toDateString(),
        ])->assertRedirect();

        Notification::assertSentOnDemand(EnrollmentConfirmed::class, $this->sentTo($student->email));
    }

    public function test_students_are_emailed_when_their_result_becomes_available_or_changes(): void
    {
        $this->actingAs(User::factory()->staff()->create());
        $course = Course::factory()->withModules(2, 100, 40)->create();
        $enrollment = Enrollment::factory()->create(['course_id' => $course->id]);
        $portalUser = User::factory()->create();
        $enrollment->student->user()->associate($portalUser)->save();
        $moduleIds = $course->modules()->pluck('id')->all();

        $save = fn (array $values) => $this->put(route('admin.courses.marks.update', $course), [
            'marks' => [$enrollment->id => array_combine($moduleIds, $values)],
        ])->assertSessionHasNoErrors();

        $save([80, null]);
        Notification::assertNothingSentTo($portalUser);

        $save([80, 90]);
        $save([80, 91]);
        Notification::assertSentToTimes($portalUser, ResultPublished::class, 1);

        $save([30, 91]);
        Notification::assertSentToTimes($portalUser, ResultPublished::class, 2);
        Notification::assertSentTo($portalUser, ResultPublished::class, fn (ResultPublished $notification) => $notification->updated);
    }

    public function test_staff_can_reply_to_an_enquiry_by_email(): void
    {
        $staff = User::factory()->staff()->create();
        $this->actingAs($staff);
        $enquiry = AdmissionEnquiry::query()->create(['name' => 'Asha', 'phone' => '9876543210', 'email' => 'asha@example.com']);

        $this->post(route('admin.enquiries.reply', $enquiry), [
            'reply_subject' => 'Your admission enquiry',
            'reply_body' => "The evening batch starts on Monday.\n\nPlease bring your ID.",
        ])->assertSessionHas('success');

        Notification::assertSentOnDemand(InstituteReply::class, fn (InstituteReply $notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['mail'] === 'asha@example.com'
            && $notification->replySubject === 'Your admission enquiry');
        $this->assertSame(EnquiryStatus::Contacted, $enquiry->refresh()->status);

        $log = ActivityLog::query()->where('action', 'email.replied')->sole();
        $this->assertSame($staff->id, $log->user_id);
        $this->assertStringNotContainsString('evening batch', $log->description.json_encode($log->properties));

        $this->get(route('admin.enquiries.show', $enquiry))->assertOk()->assertSee('Replies sent')->assertSee('Your admission enquiry');
    }

    public function test_enquiries_without_an_email_cannot_be_replied_to(): void
    {
        $this->actingAs(User::factory()->staff()->create());
        $enquiry = AdmissionEnquiry::query()->create(['name' => 'Asha', 'phone' => '9876543210']);

        $this->get(route('admin.enquiries.show', $enquiry))->assertOk()->assertSee('no email address');
        $this->post(route('admin.enquiries.reply', $enquiry), ['reply_subject' => 'Hi', 'reply_body' => 'Hello'])
            ->assertSessionHas('error');

        Notification::assertNothingSent();
    }

    public function test_staff_can_reply_to_a_contact_message(): void
    {
        $this->actingAs(User::factory()->staff()->create());
        $message = ContactMessage::query()->create(['name' => 'Ravi', 'email' => 'ravi@example.com', 'subject' => 'Fees', 'message' => 'Please share the fees.']);

        $this->post(route('admin.messages.reply', $message), ['reply_subject' => "Re: Fees\nBcc: x@evil.test", 'reply_body' => 'Hi'])
            ->assertSessionHasErrors('reply_subject');

        $this->post(route('admin.messages.reply', $message), ['reply_subject' => 'Re: Fees', 'reply_body' => 'The fee is listed on the course page.'])
            ->assertSessionHas('success');

        Notification::assertSentOnDemandTimes(InstituteReply::class, 1);
        $this->get(route('admin.messages.show', $message))->assertOk()->assertSee('Re: Fees');
    }
}
