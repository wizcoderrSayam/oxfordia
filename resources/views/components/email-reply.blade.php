{{--
    Reply-by-email form with the list of replies already sent for the record.
--}}
@props(['action', 'recipient', 'subject', 'replies'])
<section id="reply" class="card scroll-mt-6 p-6">
    <h2 class="text-base font-semibold text-slate-900">Reply by email</h2>
    <p class="mt-1 text-sm text-slate-500">Sent to {{ $recipient }} from the institute address. Their reply comes back to the institute mailbox.</p>

    <form method="POST" action="{{ $action }}" class="mt-4 space-y-4" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        <x-input name="reply_subject" label="Subject" :value="$subject" maxlength="200" required />
        <x-textarea name="reply_body" label="Message" rows="7" maxlength="5000" required help="Leave a blank line between paragraphs. The greeting and institute signature are added automatically." />
        <div class="flex justify-end">
            <button type="submit" class="btn-primary" :disabled="submitting"><x-icon name="send" /> Send reply</button>
        </div>
    </form>

    @if ($replies->isNotEmpty())
        <div class="mt-6 border-t border-slate-100 pt-4">
            <h3 class="text-sm font-semibold text-slate-700">Replies sent</h3>
            <ul class="mt-2 divide-y divide-slate-100 text-sm">
                @foreach ($replies as $reply)
                    <li class="flex flex-wrap justify-between gap-2 py-2">
                        <span class="text-slate-800">{{ Str::after($reply->description, 'Email reply sent: ') }}</span>
                        <span class="text-slate-500">{{ $reply->user?->name ?? 'System' }} · {{ $reply->created_at->format('d M Y H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</section>
