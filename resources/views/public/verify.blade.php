<x-layouts.public title="Verify Certificate" description="Verify the authenticity of a certificate issued by the institute.">
    <section class="bg-navy-950 py-16">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
            <span class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-amber-500 text-navy-950"><x-icon name="shield-check" class="size-7" /></span>
            <h1 class="mt-6 font-display text-4xl font-extrabold text-white">Certificate verification</h1>
            <p class="mt-3 text-navy-100">Enter the certificate number printed on the certificate, or scan its QR code.</p>
            <form method="GET" action="{{ route('certificates.verify') }}" class="mx-auto mt-8 flex max-w-xl flex-col gap-3 rounded-2xl bg-white p-3 shadow-lg sm:flex-row">
                <label for="number" class="sr-only">Certificate number</label>
                <input id="number" name="number" type="text" value="{{ $number }}" required maxlength="64" placeholder="e.g. OXF-CERT-2026-000001" class="form-input flex-1 border-0 uppercase shadow-none placeholder:normal-case focus:ring-0" autocomplete="off">
                <button type="submit" class="btn-primary"><x-icon name="search" /> Verify</button>
            </form>
            @error('number') <p class="mt-2 text-sm text-red-300">{{ $message }}</p> @enderror
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
        @if (! $searched)
            <div class="card p-8 text-center text-sm text-slate-600">
                Certificates issued by {{ $institute->get('name') }} carry a unique certificate number and a QR code. Verification checks our records directly — a certificate is genuine only if it is shown here as verified.
            </div>
        @elseif (! $certificate)
            <div class="card border-red-200 p-8 text-center" role="alert">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-red-100 text-red-600"><x-icon name="shield-x" class="size-7" /></span>
                <h2 class="mt-4 font-display text-2xl font-bold text-red-700">Certificate Invalid or Revoked</h2>
                <p class="mt-2 text-sm text-slate-600">No certificate matching <span class="font-mono font-semibold">{{ Str::limit($number, 64) }}</span> was found in our records. Please check the number and try again.</p>
            </div>
        @elseif (! $certificate->isValid())
            <div class="card border-red-200 p-8" role="alert">
                <div class="text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-red-100 text-red-600"><x-icon name="shield-x" class="size-7" /></span>
                    <h2 class="mt-4 font-display text-2xl font-bold text-red-700">Certificate Invalid or Revoked</h2>
                    <p class="mt-2 text-sm text-slate-600">This certificate was issued by {{ $institute->get('name') }} but has been <strong>revoked</strong> and is no longer valid.</p>
                </div>
                <dl class="mt-6 divide-y divide-slate-100 border-t border-slate-100">
                    <x-dl-row label="Certificate number"><span class="font-mono">{{ $certificate->certificate_number }}</span></x-dl-row>
                    <x-dl-row label="Status"><x-status-badge :status="$certificate->status" /></x-dl-row>
                    <x-dl-row label="Revoked on">{{ $certificate->revoked_at?->format('d M Y') }}</x-dl-row>
                </dl>
            </div>
        @else
            <div class="card overflow-hidden border-emerald-200">
                <div class="bg-emerald-50 p-8 text-center">
                    <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-emerald-600 text-white"><x-icon name="badge-check" class="size-7" /></span>
                    <h2 class="mt-4 font-display text-2xl font-bold text-emerald-800">Certificate Verified</h2>
                    <p class="mt-2 text-sm text-emerald-900">This is a genuine certificate issued by {{ $institute->get('name') }}.</p>
                </div>
                <dl class="divide-y divide-slate-100 px-8 py-4">
                    <x-dl-row label="Student name"><span class="font-semibold">{{ $certificate->student_name_snapshot }}</span></x-dl-row>
                    <x-dl-row label="Course">{{ $certificate->course_title_snapshot }}</x-dl-row>
                    @if ($certificate->grade_snapshot)
                        <x-dl-row label="Grade">{{ $certificate->grade_snapshot }}</x-dl-row>
                    @endif
                    <x-dl-row label="Issue date">{{ $certificate->issue_date->format('d M Y') }}</x-dl-row>
                    <x-dl-row label="Certificate number"><span class="font-mono">{{ $certificate->certificate_number }}</span></x-dl-row>
                    <x-dl-row label="Status"><x-status-badge :status="$certificate->status" /></x-dl-row>
                </dl>
            </div>
        @endif
    </section>
</x-layouts.public>
