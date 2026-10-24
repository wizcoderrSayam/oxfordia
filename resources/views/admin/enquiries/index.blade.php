<x-layouts.admin title="Admission enquiries">
    <x-page-header title="Admission enquiries" subtitle="Enquiries submitted from the public admissions page." />

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-3">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Name, phone or email" class="form-input">
        </div>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\EnquiryStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($enquiries->isEmpty())
            <x-empty-state icon="inbox" title="No enquiries" message="New admission enquiries will appear here." />
        @else
            <table class="table-base">
                <thead><tr><th>Name</th><th>Contact</th><th>Course</th><th>Received</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($enquiries as $enquiry)
                        <tr @class(['font-semibold' => $enquiry->status === App\Enums\EnquiryStatus::New])>
                            <td><a href="{{ route('admin.enquiries.show', $enquiry) }}" class="text-slate-900 hover:text-navy-700">{{ $enquiry->name }}</a></td>
                            <td>{{ $enquiry->phone }}<p class="text-xs font-normal text-slate-500">{{ $enquiry->email }}</p></td>
                            <td>{{ $enquiry->course?->title ?? '—' }}</td>
                            <td>{{ $enquiry->created_at->format('d M Y') }}</td>
                            <td><x-status-badge :status="$enquiry->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $enquiries->links() }}</div>
</x-layouts.admin>
