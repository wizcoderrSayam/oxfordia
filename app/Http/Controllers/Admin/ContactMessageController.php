<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\EmailReplyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(ContactMessageStatus::class)],
        ]);

        $messages = ContactMessage::query()
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")->orWhere('subject', 'like', "%{$search}%"));
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', compact('messages', 'filters'));
    }

    public function show(ContactMessage $message, EmailReplyService $replies): View
    {
        if ($message->status === ContactMessageStatus::New) {
            $message->status = ContactMessageStatus::Read;
            $message->save();
        }

        return view('admin.messages.show', [
            'message' => $message,
            'replies' => $replies->history($message),
        ]);
    }

    public function update(Request $request, ContactMessage $message): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(ContactMessageStatus::class)],
        ]);

        $message->status = ContactMessageStatus::from($validated['status']);
        $message->save();

        return back()->with('success', "Message marked as {$message->status->label()}.");
    }

    public function reply(Request $request, ContactMessage $message, EmailReplyService $replies): RedirectResponse
    {
        $validated = $request->validate(EmailReplyService::rules());

        try {
            $replies->send($message, $message->email, $message->name, $validated['reply_subject'], $validated['reply_body']);
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The reply could not be sent: '.$exception->getMessage());
        }

        return back()->with('success', "Reply sent to {$message->email}.");
    }
}
