<x-layouts.admin title="Settings">
    <x-page-header title="Institute settings" subtitle="Branding, contact details and certificate configuration used across the website and certificates. Grades are set under Grading." />

    @php
        $images = [
            'logo' => ['label' => 'Logo', 'key' => 'logo_path'],
            'signature_1' => ['label' => 'Signatory 1 signature', 'key' => 'signatory_1_signature_path'],
            'signature_2' => ['label' => 'Signatory 2 signature', 'key' => 'signatory_2_signature_path'],
        ];
    @endphp

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf @method('PUT')

        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Institute</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-input name="name" label="Institute name" :value="$values['name']" required />
                <x-input name="tagline" label="Tagline" :value="$values['tagline']" />
                <div class="sm:col-span-2"><x-textarea name="address" label="Address" :value="$values['address']" rows="2" required /></div>
                <x-input name="phone" label="Phone (also used for WhatsApp)" :value="$values['phone']" required />
                <x-input name="phone_alt" label="Second phone (optional)" :value="$values['phone_alt']" />
                <x-input name="email" type="email" label="Email (receives enquiry alerts)" :value="$values['email']" required />
                <x-input name="website" type="url" label="Website URL" :value="$values['website']" />
                <x-input name="office_hours" label="Office hours" :value="$values['office_hours']" />
                <div class="sm:col-span-2"><x-input name="map_embed_url" type="url" label="Google Maps embed URL" :value="$values['map_embed_url']" help="Google Maps → Share → Embed a map → copy only the src URL." /></div>
            </div>
        </section>

        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Social links</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-input name="facebook_url" type="url" label="Facebook" :value="$values['facebook_url']" />
                <x-input name="instagram_url" type="url" label="Instagram" :value="$values['instagram_url']" />
                <x-input name="linkedin_url" type="url" label="LinkedIn" :value="$values['linkedin_url']" />
                <x-input name="youtube_url" type="url" label="YouTube" :value="$values['youtube_url']" />
            </div>
        </section>

        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Images</h2>
            <div class="mt-4 grid gap-6 sm:grid-cols-3">
                @foreach ($images as $input => $image)
                    <div>
                        @if ($values[$image['key']])
                            <img src="{{ Storage::disk('public')->url($values[$image['key']]) }}" alt="" class="mb-3 h-16 rounded border border-slate-200 bg-white object-contain p-1">
                            <label class="mb-3 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove[]" value="{{ $input }}" class="rounded border-slate-300"> Remove</label>
                        @endif
                        <x-input :name="$input" type="file" :label="$image['label']" accept="image/png,image/jpeg,image/webp" help="PNG with transparency recommended, up to 1 MB." />
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Certificate</h2>
            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                <x-input name="signatory_1_name" label="Signatory 1 name" :value="$values['signatory_1_name']" required />
                <x-input name="signatory_1_designation" label="Signatory 1 designation" :value="$values['signatory_1_designation']" required />
                <x-input name="signatory_2_name" label="Signatory 2 name" :value="$values['signatory_2_name']" required />
                <x-input name="signatory_2_designation" label="Signatory 2 designation" :value="$values['signatory_2_designation']" required />
                <x-input name="certificate_title" label="Certificate title" :value="$values['certificate_title']" required />
                <x-input name="certificate_footer" label="Footer text" :value="$values['certificate_footer']" />
                <div class="sm:col-span-2"><x-textarea name="accreditation_statement" label="Accreditation statement (optional)" :value="$values['accreditation_statement']" rows="2" help="Only fill this in if the institute's accreditation or recognition has been independently established. Leave blank otherwise." /></div>
            </div>
        </section>


        <div class="flex justify-end">
            <button type="submit" class="btn-primary" :disabled="submitting">Save settings</button>
        </div>
    </form>

    <section id="email" class="card mt-6 scroll-mt-6 p-6" x-data="{ mailer: @js(old('mail_mailer', $values['mail_mailer']) ?? '') }">
        <h2 class="text-base font-semibold text-slate-900">Email delivery</h2>
        <p class="mt-1 text-sm text-slate-500">Outgoing mail such as receipts, certificates, acknowledgements and replies. On Hostinger, create a mailbox in hPanel and use <code>smtp.hostinger.com</code> with SSL on port 465.</p>

        @if (app(App\Services\MailSettings::class)->passwordIsUnreadable())
            <x-callout tone="warning" class="mt-4">The saved SMTP password can no longer be read, probably because the APP_KEY changed. Enter the password again and save.</x-callout>
        @endif

        <form method="POST" action="{{ route('admin.settings.mail.update') }}" class="mt-4 space-y-5">
            @csrf @method('PUT')
            <div class="grid gap-5 sm:grid-cols-2">
                <x-select name="mail_mailer" label="Send using" :options="['' => 'Server default (.env)', 'smtp' => 'Custom SMTP server']" :value="$values['mail_mailer']" x-model="mailer" />
                <div></div>
                <template x-if="mailer === 'smtp'">
                    <div class="contents">
                        <x-input name="mail_host" label="SMTP host" :value="$values['mail_host']" placeholder="smtp.hostinger.com" required />
                        <x-input name="mail_port" type="number" label="Port" :value="$values['mail_port']" placeholder="465" min="1" max="65535" required />
                        <x-select name="mail_encryption" label="Security" :options="App\Services\MailSettings::ENCRYPTIONS" :value="$values['mail_encryption'] ?? 'ssl'" required />
                        <x-input name="mail_username" label="Username" :value="$values['mail_username']" autocomplete="off" help="Usually the full mailbox address." />
                        <div>
                            <x-input name="mail_password" type="password" label="Password" autocomplete="new-password" :help="filled($values['mail_password']) ? 'A password is saved. Leave blank to keep it.' : 'Stored encrypted.'" />
                            @if (filled($values['mail_password']))
                                <label class="mt-2 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="forget_mail_password" value="1" class="rounded border-slate-300"> Remove saved password</label>
                            @endif
                        </div>
                        <x-input name="mail_from_address" type="email" label="From address" :value="$values['mail_from_address']" required help="Must belong to the SMTP account, or mail may be rejected." />
                        <x-input name="mail_from_name" label="From name" :value="$values['mail_from_name']" :placeholder="$values['name']" />
                    </div>
                </template>
            </div>
            <label class="flex items-start gap-2 text-sm text-slate-700">
                <input type="hidden" name="mail_auto_replies" value="0">
                <input type="checkbox" name="mail_auto_replies" value="1" class="mt-0.5 rounded border-slate-300" @checked(old('mail_auto_replies', $values['mail_auto_replies']))>
                <span>Send automatic acknowledgements to people who submit the admission enquiry or contact form</span>
            </label>
            <div class="flex justify-end">
                <button type="submit" class="btn-primary">Save email settings</button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.settings.mail.test') }}" class="mt-6 flex flex-col gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1"><x-input name="test_recipient" type="email" label="Send a test email to" :value="auth()->user()->email" required help="Save your changes first. The test uses the saved settings." /></div>
            <button type="submit" class="btn-secondary sm:mb-6"><x-icon name="send" /> Send test email</button>
        </form>
    </section>
</x-layouts.admin>
