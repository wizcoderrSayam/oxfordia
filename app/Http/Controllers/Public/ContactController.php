<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Notifications\ContactMessageReceived;
use App\Notifications\NewContactMessage;
use App\Services\InstituteSettings;
use App\Services\MailSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ContactController extends Controller
{
    public function create(): View
    {
        return view('public.contact');
    }

    public function store(Request $request, InstituteSettings $settings, MailSettings $mail): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
        ]);

        $contactMessage = new ContactMessage($validated);
        $contactMessage->status = ContactMessageStatus::New;
        $contactMessage->save();

        Notification::route('mail', $settings->get('email'))->notify(new NewContactMessage($contactMessage));

        if ($mail->mayAcknowledge($contactMessage->email)) {
            Notification::route('mail', $contactMessage->email)->notify(new ContactMessageReceived($contactMessage));
        }

        return redirect()->route('contact.create')
            ->with('success', 'Thanks for reaching out. We will get back to you soon.');
    }
}
