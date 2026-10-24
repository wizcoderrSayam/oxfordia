<x-layouts.portal title="My courses">
    <div class="mb-8 flex flex-col gap-4 rounded-2xl bg-navy-900 p-6 text-white sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-navy-200">Welcome back,</p>
            <h1 class="font-display text-2xl font-bold">{{ $student->full_name ?? $user->name }}</h1>
            @if ($student)
                <p class="mt-1 text-sm text-navy-200">Registration no. <span class="font-mono font-semibold text-amber-300">{{ $student->registration_number }}</span></p>
            @endif
        </div>
        <a href="{{ route('courses.index') }}" class="btn-accent"><x-icon name="book-open" /> Browse courses</a>
    </div>

    @unless ($user->hasVerifiedEmail())
        <div class="mb-6 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
            <p class="flex items-center gap-2"><x-icon name="triangle-alert" /> Please verify your email address before buying a course.</p>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-secondary btn-sm">Resend link</button>
            </form>
        </div>
    @endunless

    <h2 class="mb-4 font-display text-lg font-bold text-slate-900">My enrollments</h2>
    @if ($enrollments->isEmpty())
        <div class="card">
            <x-empty-state icon="book-open" title="You haven't enrolled in any course yet" message="Browse our catalogue and buy a course to get started. Your results and certificates will appear here.">
                <a href="{{ route('courses.index') }}" class="btn-primary">Explore courses</a>
            </x-empty-state>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($enrollments as $enrollment)
                <div class="card flex flex-col p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold tracking-wide text-slate-400 uppercase">{{ $enrollment->course->course_code }} · {{ $enrollment->enrollment_number }}</p>
                            <h3 class="mt-1 font-display text-lg font-bold text-slate-900">{{ $enrollment->course->title }}</h3>
                        </div>
                        <x-status-badge :status="$enrollment->status" />
                    </div>
                    <dl class="mt-4 grid grid-cols-3 gap-3 text-sm">
                        <div><dt class="text-slate-500">Enrolled</dt><dd class="font-medium">{{ $enrollment->enrollment_date->format('d M Y') }}</dd></div>
                        <div><dt class="text-slate-500">Result</dt><dd class="font-medium">@if ($enrollment->result) <x-status-badge :status="$enrollment->result" /> @else Pending @endif</dd></div>
                        <div><dt class="text-slate-500">Grade</dt><dd class="font-medium">{{ $enrollment->final_grade ?? '—' }}</dd></div>
                    </dl>
                    <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                        <a href="{{ route('portal.enrollments.show', $enrollment) }}" class="btn-secondary btn-sm"><x-icon name="clipboard-list" /> View marksheet</a>
                        @if ($enrollment->validCertificate)
                            <a href="{{ route('portal.certificates.download', $enrollment->validCertificate) }}" class="btn-primary btn-sm"><x-icon name="download" /> Download certificate</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($orders->isNotEmpty())
        <h2 class="mt-10 mb-4 font-display text-lg font-bold text-slate-900">Recent orders</h2>
        <div class="card overflow-x-auto">
            <table class="table-base">
                <thead><tr><th>Order</th><th>Course</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td><a href="{{ route('checkout.show', $order) }}" class="font-mono font-medium text-navy-700 hover:underline">{{ $order->order_number }}</a></td>
                            <td>{{ $order->course->title }}</td>
                            <td>₹{{ number_format((float) $order->amount, 2) }}</td>
                            <td><x-status-badge :status="$order->status" /></td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.portal>
