<x-layouts.admin title="Certificates">
    <x-page-header title="Certificates" subtitle="Issue certificates for completed, passed enrollments and manage issued certificates." />

    <section id="eligible" class="card mb-8 overflow-hidden">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900">Ready for a certificate</h2>
                <p class="text-sm text-slate-500">Completed enrollments with a passing result and no valid certificate.</p>
            </div>
            <x-badge color="amber">{{ $eligible->total() }} eligible</x-badge>
        </div>
        @if ($eligible->isEmpty())
            <x-empty-state icon="circle-check" title="Nothing waiting" message="Mark enrollments as completed after entering passing marks to see them here." />
        @else
            <div class="overflow-x-auto">
                <table class="table-base">
                    <thead><tr><th>Student</th><th>Course</th><th>Completed</th><th>Grade</th><th><span class="sr-only">Actions</span></th></tr></thead>
                    <tbody>
                        @foreach ($eligible as $enrollment)
                            <tr>
                                <td><span class="font-medium text-slate-900">{{ $enrollment->student->full_name }}</span><p class="text-xs text-slate-500">{{ $enrollment->enrollment_number }}</p></td>
                                <td>{{ $enrollment->course->title }}</td>
                                <td>{{ $enrollment->completion_date?->format('d M Y') ?? '—' }}</td>
                                <td class="font-semibold">{{ $enrollment->final_grade }}</td>
                                <td class="text-right">
                                    @can('manage-certificates')
                                        <x-confirm :action="route('admin.certificates.store', $enrollment)" title="Issue certificate?" :message="'Issue a certificate to '.$enrollment->student->full_name.' for '.$enrollment->course->title.' (grade '.$enrollment->final_grade.').'" confirm-label="Issue certificate" variant="primary" trigger-class="btn-primary btn-sm">
                                            <x-slot:trigger><x-icon name="award" /> Issue</x-slot:trigger>
                                            <x-input name="issue_date" type="date" label="Issue date" :value="now()->toDateString()" :id="'issue-date-'.$enrollment->id" />
                                        </x-confirm>
                                    @else
                                        <a href="{{ route('admin.enrollments.show', $enrollment) }}" class="btn-secondary btn-sm">View</a>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($eligible->hasPages())<div class="border-t border-slate-100 px-6 py-3">{{ $eligible->links() }}</div>@endif
        @endif
    </section>

    <h2 class="mb-3 text-lg font-semibold text-slate-900">Issued certificates</h2>
    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-3">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Certificate number, student or course" class="form-input">
        </div>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\CertificateStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($certificates->isEmpty())
            <x-empty-state icon="award" title="No certificates found" />
        @else
            <table class="table-base">
                <thead><tr><th>Certificate no.</th><th>Student</th><th>Course</th><th>Issued</th><th>Issued by</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($certificates as $certificate)
                        <tr>
                            <td><a href="{{ route('admin.certificates.show', $certificate) }}" class="font-mono text-xs font-semibold text-navy-700 hover:underline">{{ $certificate->certificate_number }}</a></td>
                            <td>{{ $certificate->student_name_snapshot }}</td>
                            <td>{{ $certificate->course_title_snapshot }}</td>
                            <td>{{ $certificate->issue_date->format('d M Y') }}</td>
                            <td>{{ $certificate->issuer?->name }}</td>
                            <td><x-status-badge :status="$certificate->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $certificates->links() }}</div>
</x-layouts.admin>
