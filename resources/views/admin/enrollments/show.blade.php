<x-layouts.admin :title="'Enrollment '.$enrollment->enrollment_number">
    <x-page-header :title="$enrollment->student->full_name.' · '.$enrollment->course->title" :subtitle="'Enrollment '.$enrollment->enrollment_number" :back="route('admin.enrollments.index')">
        <x-status-badge :status="$enrollment->status" />
        <a href="{{ route('admin.courses.marks.edit', $enrollment->course) }}" class="btn-secondary"><x-icon name="clipboard-list" /> Enter marks</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Marksheet</h2>
                    <p class="text-sm text-slate-600">
                        @if ($enrollment->result)
                            {{ rtrim(rtrim($enrollment->total_marks, '0'), '.') }} / {{ $enrollment->max_total_marks }} · {{ $enrollment->percentage }}% · <strong>{{ $enrollment->final_grade }}</strong> <x-status-badge :status="$enrollment->result" />
                        @else
                            Result pending — marks incomplete
                        @endif
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead><tr><th>Module</th><th class="text-right">Max</th><th class="text-right">Pass</th><th class="text-right">Marks</th></tr></thead>
                        <tbody>
                            @forelse ($enrollment->course->modules as $module)
                                @php($mark = $marks->get($module->id))
                                <tr>
                                    <td class="font-medium text-slate-900">{{ $module->name }}</td>
                                    <td class="text-right">{{ $module->max_marks }}</td>
                                    <td class="text-right">{{ $module->pass_marks }}</td>
                                    <td class="text-right font-semibold {{ $mark && (float) $mark->marks < $module->pass_marks ? 'text-red-600' : '' }}">{{ $mark ? rtrim(rtrim($mark->marks, '0'), '.') : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><x-empty-state icon="layers" title="No modules defined for this course" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Progress & completion</h2>
                @if ($enrollment->validCertificate)
                    <p class="mt-2 flex items-center gap-2 text-sm text-slate-500"><x-icon name="lock" /> A valid certificate exists, so the status is locked. Revoke the certificate to make changes.</p>
                @endif
                <form method="POST" action="{{ route('admin.enrollments.update', $enrollment) }}" class="mt-4 grid gap-5 sm:grid-cols-2">
                    @csrf @method('PUT')
                    <x-select name="status" label="Status" :options="collect(App\Enums\EnrollmentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$enrollment->status" required />
                    <x-input name="enrollment_date" type="date" label="Enrollment date" :value="$enrollment->enrollment_date->toDateString()" required />
                    <x-input name="start_date" type="date" label="Course start" :value="$enrollment->start_date?->toDateString()" />
                    <x-input name="expected_completion_date" type="date" label="Expected completion" :value="$enrollment->expected_completion_date?->toDateString()" />
                    <x-input name="completion_date" type="date" label="Actual completion" :value="$enrollment->completion_date?->toDateString()" help="Set automatically when marked completed." />
                    <div class="sm:col-span-2"><x-textarea name="remarks" label="Internal remarks" :value="$enrollment->remarks" rows="3" /></div>
                    <div class="flex justify-end sm:col-span-2"><button type="submit" class="btn-primary">Save enrollment</button></div>
                </form>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Student</h2>
                <dl class="mt-2 divide-y divide-slate-100">
                    <x-dl-row label="Name"><a href="{{ route('admin.students.show', $enrollment->student) }}" class="text-navy-700 hover:underline">{{ $enrollment->student->full_name }}</a></x-dl-row>
                    <x-dl-row label="Reg. no."><span class="font-mono text-xs">{{ $enrollment->student->registration_number }}</span></x-dl-row>
                    <x-dl-row label="Course"><a href="{{ route('admin.courses.show', $enrollment->course) }}" class="text-navy-700 hover:underline">{{ $enrollment->course->title }}</a></x-dl-row>
                </dl>
            </section>

            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Certificate</h2>
                @if ($enrollment->validCertificate)
                    <p class="mt-2 text-sm text-slate-600">Issued on {{ $enrollment->validCertificate->issue_date->format('d M Y') }}.</p>
                    <a href="{{ route('admin.certificates.show', $enrollment->validCertificate) }}" class="btn-secondary mt-4 w-full"><x-icon name="award" /> {{ $enrollment->validCertificate->certificate_number }}</a>
                @elseif ($enrollment->status !== App\Enums\EnrollmentStatus::Completed)
                    <p class="mt-2 text-sm text-slate-600">Certificates can be issued once the enrollment is marked <strong>completed</strong> with a passing result.</p>
                @elseif (! $enrollment->isPassed())
                    <p class="mt-2 text-sm text-slate-600">This enrollment does not have a passing result, so a certificate cannot be issued.</p>
                @else
                    <p class="mt-2 text-sm text-emerald-700">Eligible for a certificate.</p>
                    @can('manage-certificates')
                        <x-confirm :action="route('admin.certificates.store', $enrollment)" title="Issue certificate?" :message="'A certificate will be issued to '.$enrollment->student->full_name.' for '.$enrollment->course->title.' with grade '.$enrollment->final_grade.'. Name, course and grade are frozen on the certificate.'" confirm-label="Issue certificate" variant="primary" trigger-class="btn-primary mt-4 w-full">
                            <x-slot:trigger><x-icon name="award" /> Issue certificate</x-slot:trigger>
                            <x-input name="issue_date" type="date" label="Issue date" :value="now()->toDateString()" />
                        </x-confirm>
                    @else
                        <p class="mt-2 text-xs text-slate-500">Only administrators can issue certificates.</p>
                    @endcan
                @endif

                @php($history = $enrollment->certificates->where('status', App\Enums\CertificateStatus::Revoked))
                @if ($history->isNotEmpty())
                    <h3 class="mt-6 text-xs font-semibold tracking-wide text-slate-500 uppercase">Revoked certificates</h3>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($history as $revoked)
                            <li><a href="{{ route('admin.certificates.show', $revoked) }}" class="font-mono text-xs text-slate-500 line-through hover:text-navy-700">{{ $revoked->certificate_number }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.admin>
