<x-layouts.admin title="Contact messages">
    <x-page-header title="Contact messages" subtitle="Messages sent from the public contact page." />

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-3">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email or subject" class="form-input">
        </div>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\ContactMessageStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($messages->isEmpty())
            <x-empty-state icon="message-square" title="No messages" message="Messages from the contact form will appear here." />
        @else
            <table class="table-base">
                <thead><tr><th>From</th><th>Subject</th><th>Received</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($messages as $message)
                        <tr @class(['font-semibold' => $message->status === App\Enums\ContactMessageStatus::New])>
                            <td><a href="{{ route('admin.messages.show', $message) }}" class="text-slate-900 hover:text-navy-700">{{ $message->name }}</a><p class="text-xs font-normal text-slate-500">{{ $message->email }}</p></td>
                            <td>{{ Str::limit($message->subject ?: $message->message, 60) }}</td>
                            <td>{{ $message->created_at->format('d M Y H:i') }}</td>
                            <td><x-status-badge :status="$message->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $messages->links() }}</div>
</x-layouts.admin>
