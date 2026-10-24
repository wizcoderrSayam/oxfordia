<x-layouts.admin :title="'Certificate '.$certificate->certificate_number">
    <x-page-header :title="'Certificate '.$certificate->certificate_number" :subtitle="$certificate->student_name_snapshot.' · '.$certificate->course_title_snapshot" :back="route('admin.certificates.index')">
        <x-status-badge :status="$certificate->status" />
        <a href="{{ route('admin.certificates.print', $certificate) }}" target="_blank" class="btn-secondary"><x-icon name="printer" /> Print</a>
        <a href="{{ route('admin.certificates.pdf', $certificate) }}" class="btn-primary"><x-icon name="download" /> Download PDF</a>
    </x-page-header>

    @include('certificates._styles')
    <div class="card mb-6 overflow-x-auto bg-slate-100 p-4">
        <div class="mx-auto w-fit shadow-lg" style="zoom: 0.75">
            @include('certificates._certificate')
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="card p-6">
            <h2 class="text-base font-semibold text-slate-900">Record</h2>
            <dl class="mt-2 divide-y divide-slate-100">
                <x-dl-row label="Student"><a href="{{ route('admin.students.show', $certificate->student) }}" class="text-navy-700 hover:underline">{{ $certificate->student->full_name }}</a> <span class="text-xs text-slate-500">({{ $certificate->student->registration_number }})</span></x-dl-row>
                <x-dl-row label="Enrollment"><a href="{{ route('admin.enrollments.show', $certificate->enrollment) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $certificate->enrollment->enrollment_number }}</a></x-dl-row>
                <x-dl-row label="Name on certificate">{{ $certificate->student_name_snapshot }}</x-dl-row>
                <x-dl-row label="Grade">{{ $certificate->grade_snapshot }}</x-dl-row>
                <x-dl-row label="Issue date">{{ $certificate->issue_date->format('d M Y') }}</x-dl-row>
                <x-dl-row label="Issued by">{{ $certificate->issuer?->name }} · {{ $certificate->created_at->format('d M Y H:i') }}</x-dl-row>
                <x-dl-row label="Verification link"><a href="{{ $verificationUrl }}" target="_blank" class="break-all text-navy-700 hover:underline">{{ $verificationUrl }}</a></x-dl-row>
                @if ($certificate->replaces)
                    <x-dl-row label="Replaces"><a href="{{ route('admin.certificates.show', $certificate->replaces) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $certificate->replaces->certificate_number }}</a></x-dl-row>
                @endif
                @if ($replacement)
                    <x-dl-row label="Replaced by"><a href="{{ route('admin.certificates.show', $replacement) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $replacement->certificate_number }}</a></x-dl-row>
                @endif
            </dl>
        </section>

        <section class="card p-6">
            @if ($certificate->isValid())
                <h2 class="text-base font-semibold text-slate-900">Revoke certificate</h2>
                <p class="mt-2 text-sm text-slate-600">Revoked certificates stay in the records and show as <strong>revoked</strong> on the public verification page. After revoking, you can issue a corrected replacement from the enrollment.</p>
                @can('manage-certificates')
                    <x-confirm :action="route('admin.certificates.revoke', $certificate)" title="Revoke this certificate?" message="Anyone verifying this certificate will see it as revoked. This cannot be undone." confirm-label="Revoke certificate" trigger-class="btn-danger mt-4">
                        <x-slot:trigger><x-icon name="shield-x" /> Revoke certificate</x-slot:trigger>
                        <x-textarea name="reason" label="Reason for revocation" rows="3" required minlength="5" />
                    </x-confirm>
                @else
                    <p class="mt-3 text-xs text-slate-500">Only administrators can revoke certificates.</p>
                @endcan
            @else
                <h2 class="text-base font-semibold text-red-700">Revoked</h2>
                <dl class="mt-2 divide-y divide-slate-100">
                    <x-dl-row label="Revoked on">{{ $certificate->revoked_at?->format('d M Y H:i') }}</x-dl-row>
                    <x-dl-row label="Revoked by">{{ $certificate->revoker?->name }}</x-dl-row>
                    <x-dl-row label="Reason"><span class="whitespace-pre-line">{{ $certificate->revocation_reason }}</span></x-dl-row>
                </dl>
                @if (! $replacement && $certificate->enrollment->isEligibleForCertificate())
                    <a href="{{ route('admin.enrollments.show', $certificate->enrollment) }}" class="btn-secondary mt-4"><x-icon name="award" /> Issue replacement</a>
                @endif
            @endif
        </section>
    </div>
</x-layouts.admin>
