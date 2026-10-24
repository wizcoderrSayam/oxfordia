<x-layouts.admin :title="'Message from '.$message->name">
    <x-page-header :title="$message->subject ?: 'Message from '.$message->name" :subtitle="'Received '.$message->created_at->format('d M Y H:i')" :back="route('admin.messages.index')">
        <x-status-badge :status="$message->status" />
        <a href="#reply" class="btn-primary"><x-icon name="mail" /> Reply by email</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-6 lg:col-span-2">
            <p class="text-sm whitespace-pre-line text-slate-800">{{ $message->message }}</p>
        </section>
        <aside class="space-y-6 lg:row-span-2">
            <section class="card p-6">
                <dl class="divide-y divide-slate-100">
                    <x-dl-row label="Name">{{ $message->name }}</x-dl-row>
                    <x-dl-row label="Email">{{ $message->email }}</x-dl-row>
                    <x-dl-row label="Phone">{{ $message->phone }}</x-dl-row>
                </dl>
                <form method="POST" action="{{ route('admin.messages.update', $message) }}" class="mt-4 space-y-3">
                    @csrf @method('PATCH')
                    <x-select name="status" label="Status" :options="collect(App\Enums\ContactMessageStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$message->status" />
                    <button type="submit" class="btn-secondary w-full">Save status</button>
                </form>
            </section>
        </aside>
        <div class="lg:col-span-2 lg:row-start-2">
            <x-email-reply :action="route('admin.messages.reply', $message)" :recipient="$message->email" :subject="'Re: '.($message->subject ?: 'Your message')" :replies="$replies" />
        </div>
    </div>
</x-layouts.admin>
