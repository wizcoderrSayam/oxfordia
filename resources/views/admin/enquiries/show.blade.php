<x-layouts.admin :title="'Enquiry from '.$enquiry->name">
    <x-page-header :title="'Enquiry from '.$enquiry->name" :subtitle="'Received '.$enquiry->created_at->format('d M Y H:i')" :back="route('admin.enquiries.index')">
        <x-status-badge :status="$enquiry->status" />
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="card p-6 lg:col-span-2">
            <dl class="divide-y divide-slate-100">
                <x-dl-row label="Name">{{ $enquiry->name }}</x-dl-row>
                <x-dl-row label="Phone"><a href="tel:{{ $enquiry->phone }}" class="text-navy-700 hover:underline">{{ $enquiry->phone }}</a></x-dl-row>
                <x-dl-row label="Email">@if ($enquiry->email)<a href="mailto:{{ $enquiry->email }}" class="text-navy-700 hover:underline">{{ $enquiry->email }}</a>@endif</x-dl-row>
                <x-dl-row label="Course of interest">{{ $enquiry->course?->title }}</x-dl-row>
                <x-dl-row label="Message"><span class="whitespace-pre-line">{{ $enquiry->message }}</span></x-dl-row>
            </dl>
        </section>
        <aside class="space-y-6 lg:row-span-2">
            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Update status</h2>
                <form method="POST" action="{{ route('admin.enquiries.update', $enquiry) }}" class="mt-4 space-y-3">
                    @csrf @method('PATCH')
                    <x-select name="status" label="Status" :options="collect(App\Enums\EnquiryStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$enquiry->status" />
                    <button type="submit" class="btn-secondary w-full">Save status</button>
                </form>
            </section>
            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Student record</h2>
                @if ($enquiry->student)
                    <p class="mt-2 text-sm text-slate-600">Converted to <a href="{{ route('admin.students.show', $enquiry->student) }}" class="font-semibold text-navy-700 hover:underline">{{ $enquiry->student->full_name }} ({{ $enquiry->student->registration_number }})</a>.</p>
                @else
                    <p class="mt-2 text-sm text-slate-600">Create a student record pre-filled from this enquiry. Nothing is created until you save the form.</p>
                    <a href="{{ route('admin.students.create', ['enquiry' => $enquiry->id]) }}" class="btn-primary mt-4 w-full"><x-icon name="user-plus" /> Create student from enquiry</a>
                @endif
            </section>
        </aside>
        <div class="lg:col-span-2 lg:row-start-2">
            @if ($enquiry->email)
                <x-email-reply :action="route('admin.enquiries.reply', $enquiry)" :recipient="$enquiry->email" :subject="'Your admission enquiry'.($enquiry->course ? ' - '.$enquiry->course->title : '')" :replies="$replies" />
            @else
                <x-callout>This enquiry has no email address, so it cannot be answered by email. Call {{ $enquiry->phone }} instead.</x-callout>
            @endif
        </div>
    </div>
</x-layouts.admin>
