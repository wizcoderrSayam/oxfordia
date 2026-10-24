<x-layouts.admin :title="$student->full_name">
    <x-page-header :title="$student->full_name" :subtitle="'Registration no. '.$student->registration_number" :back="route('admin.students.index')">
        <x-status-badge :status="$student->status" />
        <a href="{{ route('admin.enrollments.create', ['student' => $student->id]) }}" class="btn-secondary"><x-icon name="plus" /> Enroll in course</a>
        <a href="{{ route('admin.students.edit', $student) }}" class="btn-primary"><x-icon name="pencil" /> Edit</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4"><h2 class="text-base font-semibold text-slate-900">Enrollment history</h2></div>
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead><tr><th>Course</th><th>Enrollment</th><th>Date</th><th>Status</th><th>Result</th><th>Certificate</th></tr></thead>
                        <tbody>
                            @forelse ($student->enrollments as $enrollment)
                                <tr>
                                    <td><a href="{{ route('admin.courses.show', $enrollment->course) }}" class="font-medium text-slate-900 hover:text-navy-700">{{ $enrollment->course->title }}</a></td>
                                    <td><a href="{{ route('admin.enrollments.show', $enrollment) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $enrollment->enrollment_number }}</a></td>
                                    <td>{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                    <td><x-status-badge :status="$enrollment->status" /></td>
                                    <td>@if ($enrollment->result){{ $enrollment->final_grade }} <x-status-badge :status="$enrollment->result" />@else — @endif</td>
                                    <td>@if ($enrollment->validCertificate)<a href="{{ route('admin.certificates.show', $enrollment->validCertificate) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $enrollment->validCertificate->certificate_number }}</a>@else — @endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><x-empty-state icon="clipboard-list" title="Not enrolled in any course" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4"><h2 class="text-base font-semibold text-slate-900">Certificates</h2></div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($student->certificates as $certificate)
                        <li class="flex items-center justify-between gap-3 px-6 py-3">
                            <div>
                                <a href="{{ route('admin.certificates.show', $certificate) }}" class="font-mono text-sm font-semibold text-navy-700 hover:underline">{{ $certificate->certificate_number }}</a>
                                <p class="text-xs text-slate-500">{{ $certificate->course_title_snapshot }} · {{ $certificate->issue_date->format('d M Y') }}</p>
                            </div>
                            <x-status-badge :status="$certificate->status" />
                        </li>
                    @empty
                        <li><x-empty-state icon="award" title="No certificates issued" /></li>
                    @endforelse
                </ul>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-6">
                <div class="flex items-center gap-4">
                    @if ($student->photo_url)
                        <img src="{{ $student->photo_url }}" alt="" class="size-16 rounded-full object-cover">
                    @else
                        <span class="flex size-16 items-center justify-center rounded-full bg-navy-50 text-lg font-bold text-navy-700">{{ $student->initials }}</span>
                    @endif
                    <div>
                        <p class="text-base font-semibold text-slate-900">{{ $student->full_name }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $student->registration_number }}</p>
                    </div>
                </div>
                <dl class="mt-4 divide-y divide-slate-100">
                    <x-dl-row label="Email">{{ $student->email }}</x-dl-row>
                    <x-dl-row label="Phone">{{ $student->phone }}</x-dl-row>
                    <x-dl-row label="Date of birth">{{ $student->date_of_birth?->format('d M Y') }}</x-dl-row>
                    <x-dl-row label="Gender">{{ App\Models\Student::GENDERS[$student->gender] ?? '' }}</x-dl-row>
                    <x-dl-row label="Address"><span class="whitespace-pre-line">{{ $student->address }}</span></x-dl-row>
                    <x-dl-row label="Admission date">{{ $student->admission_date->format('d M Y') }}</x-dl-row>
                </dl>
            </section>

            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Portal access</h2>
                @if ($student->user)
                    <p class="mt-2 text-sm text-slate-600">Linked to login <strong>{{ $student->user->email }}</strong>{{ $student->user->hasVerifiedEmail() ? '' : ' (not yet activated)' }}.</p>
                @else
                    <p class="mt-2 text-sm text-slate-600">This student has no portal login yet.</p>
                @endif
                @if ($student->email && (! $student->user || ! $student->user->hasVerifiedEmail()))
                    <form method="POST" action="{{ route('admin.students.invite', $student) }}" class="mt-4">
                        @csrf
                        <button type="submit" class="btn-secondary w-full"><x-icon name="send" /> {{ $student->user ? 'Resend portal invite' : 'Send portal invite' }}</button>
                    </form>
                @elseif (! $student->email)
                    <p class="mt-2 text-xs text-slate-500">Add an email address to send a portal invite.</p>
                @endif
            </section>

            @if ($student->internal_notes)
                <section class="card border-amber-200 bg-amber-50/50 p-6">
                    <h2 class="flex items-center gap-2 text-base font-semibold text-slate-900"><x-icon name="lock" /> Internal notes</h2>
                    <p class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $student->internal_notes }}</p>
                </section>
            @endif

            <section class="card p-6">
                <h2 class="text-base font-semibold text-red-700">Danger zone</h2>
                @if ($student->enrollments->isNotEmpty() || $student->certificates->isNotEmpty())
                    <p class="mt-2 text-sm text-slate-600">Students with enrollments or certificates can't be deleted. Change the status to inactive instead.</p>
                @else
                    <x-confirm :action="route('admin.students.destroy', $student)" method="DELETE" title="Delete student?" message="This permanently deletes the student record." confirm-label="Delete student" trigger-class="btn-danger mt-3 w-full">
                        <x-slot:trigger><x-icon name="trash-2" /> Delete student</x-slot:trigger>
                    </x-confirm>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.admin>
