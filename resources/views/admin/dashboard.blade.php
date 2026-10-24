<x-layouts.admin title="Dashboard">
    @php
        $firstName = Str::before(trim(auth()->user()->name), ' ') ?: auth()->user()->name;

        $waiting = array_values(array_filter([
            $stats['pending_enquiries'] ? $stats['pending_enquiries'].' new admission '.Str::plural('enquiry', $stats['pending_enquiries']) : null,
            $stats['unread_messages'] ? $stats['unread_messages'].' unread '.Str::plural('message', $stats['unread_messages']) : null,
            $stats['eligible_for_certificate'] ? $stats['eligible_for_certificate'].' '.Str::plural('certificate', $stats['eligible_for_certificate']).' ready to issue' : null,
            $stats['draft_courses'] ? $stats['draft_courses'].' draft '.Str::plural('course', $stats['draft_courses']) : null,
        ]));

        $summary = $waiting
            ? 'You have '.Arr::join($waiting, ', ', ' and ').'.'
            : 'Nothing is waiting for you. Everything is up to date.';

        $pending = array_values(array_filter([
            ['count' => $stats['pending_enquiries'], 'label' => 'Admission enquiries', 'hint' => 'New, awaiting a reply', 'icon' => 'inbox', 'href' => route('admin.enquiries.index', ['status' => 'new'])],
            ['count' => $stats['unread_messages'], 'label' => 'Contact messages', 'hint' => 'Not yet read', 'icon' => 'message-square', 'href' => route('admin.messages.index', ['status' => 'new'])],
            ['count' => $stats['eligible_for_certificate'], 'label' => 'Certificates to issue', 'hint' => 'Completed and passed', 'icon' => 'award', 'href' => route('admin.certificates.index').'#eligible'],
            ['count' => $stats['draft_courses'], 'label' => 'Draft courses', 'hint' => 'Not yet published', 'icon' => 'book-open', 'href' => route('admin.courses.index', ['status' => 'draft'])],
        ], fn (array $item): bool => $item['count'] > 0));

        $figures = [
            ['label' => 'Students', 'value' => $stats['students'], 'href' => route('admin.students.index'), 'change' => $stats['students_this_month'], 'note' => 'new this month'],
            ['label' => 'Active enrollments', 'value' => $stats['active_enrollments'], 'href' => route('admin.enrollments.index'), 'change' => $stats['enrollments_this_month'], 'note' => 'enrolled this month'],
            ['label' => 'Published courses', 'value' => $stats['published_courses'], 'href' => route('admin.courses.index', ['status' => 'published']), 'change' => null, 'note' => number_format($stats['courses']).' in the catalogue'],
            ['label' => 'Valid certificates', 'value' => $stats['certificates'], 'href' => route('admin.certificates.index'), 'change' => $stats['certificates_this_month'], 'note' => 'issued this month'],
        ];

        $trendTotal = array_sum(array_column($enrollmentTrend, 'count'));
        $trendPeak = max(1, ...array_column($enrollmentTrend, 'count'));
        $trendStep = collect([1, 2, 5, 10, 20, 25, 50, 100, 200, 250, 500, 1000])->first(fn (int $step): bool => $step * 2 >= $trendPeak) ?? (int) ceil($trendPeak / 2);
        $trendTop = $trendStep * 2;

        $activityIcons = [
            'certificate' => 'award', 'enrollment' => 'clipboard-list', 'marks' => 'pencil', 'student' => 'users',
            'course' => 'book-open', 'module' => 'layers', 'user' => 'user', 'settings' => 'settings', 'order' => 'shopping-cart',
        ];
    @endphp

    <x-page-header title="Dashboard" :subtitle="'Welcome back, '.$firstName.'. '.$summary">
        <span class="hidden items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 md:inline-flex">
            <x-icon name="calendar" class="size-4 text-slate-400" /> {{ now()->format('D, j M Y') }}
        </span>
        <a href="{{ route('admin.students.create') }}" class="btn-secondary"><x-icon name="user-plus" /> New student</a>
        <a href="{{ route('admin.enrollments.create') }}" class="btn-primary"><x-icon name="plus" /> New enrollment</a>
    </x-page-header>

    {{-- Key figures --}}
    <section aria-label="Key figures" class="grid grid-cols-2 gap-px overflow-hidden rounded-xl border border-slate-200 bg-slate-200 shadow-[0_1px_2px_rgb(15_23_42/0.04)] xl:grid-cols-4">
        @foreach ($figures as $figure)
            <a href="{{ $figure['href'] }}" class="group bg-white p-5 transition hover:bg-slate-50">
                <p class="flex items-center justify-between text-sm font-medium text-slate-500">
                    {{ $figure['label'] }}
                    <x-icon name="chevron-right" class="size-4 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500" />
                </p>
                <p class="mt-3 text-[1.75rem] leading-none font-semibold tracking-tight text-slate-900">{{ number_format($figure['value']) }}</p>
                <p class="mt-3 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                    @if ($figure['change'])
                        <span class="inline-flex items-center gap-0.5 rounded-md bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-700">
                            <x-icon name="trending-up" class="size-3.5" /> +{{ number_format($figure['change']) }}
                        </span>
                        {{ $figure['note'] }}
                    @elseif ($figure['change'] === 0)
                        None {{ $figure['note'] }}
                    @else
                        {{ $figure['note'] }}
                    @endif
                </p>
            </a>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">
        {{-- Enrollment trend --}}
        <section class="card p-5 xl:col-span-2" aria-labelledby="trend-title">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 id="trend-title" class="text-sm font-semibold text-slate-900">Enrollments</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Monthly, last {{ count($enrollmentTrend) }} months</p>
                </div>
                <div class="text-right">
                    <p class="text-2xl leading-none font-semibold tracking-tight text-slate-900">{{ number_format($trendTotal) }}</p>
                    <p class="mt-1 text-xs text-slate-500">in this period</p>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <div class="relative h-44 w-6 shrink-0 text-right text-[0.6875rem] text-slate-500 tabular-nums" aria-hidden="true">
                    @foreach ([0, $trendStep, $trendTop] as $tick)
                        <span class="absolute right-0 translate-y-1/2" style="bottom: {{ $tick / $trendTop * 100 }}%">{{ $tick }}</span>
                    @endforeach
                </div>
                <div class="relative min-w-0 flex-1">
                    <div class="relative h-44">
                        @foreach ([0, $trendStep, $trendTop] as $tick)
                            <div class="absolute inset-x-0 border-t border-[var(--chart-grid)]" style="bottom: {{ $tick / $trendTop * 100 }}%"></div>
                        @endforeach
                        <ol class="admin-chart relative flex h-full items-end" aria-label="Enrollments by month">
                            @foreach ($enrollmentTrend as $month)
                                @php($height = $month['count'] / $trendTop * 100)
                                <li class="admin-chart-col relative flex h-full flex-1 items-end justify-center" tabindex="0">
                                    <span class="sr-only">{{ $month['name'] }}: {{ number_format($month['count']) }} {{ Str::plural('enrollment', $month['count']) }}</span>
                                    @if ($loop->last && $month['count'] > 0)
                                        <span class="absolute left-1/2 -translate-x-1/2 text-xs font-semibold text-slate-700 tabular-nums" style="bottom: calc({{ $height }}% + 0.35rem)" aria-hidden="true">{{ $month['count'] }}</span>
                                    @endif
                                    <span class="admin-chart-bar block w-full max-w-6" style="height: {{ $height }}%"></span>
                                    <span class="admin-tooltip" style="bottom: calc({{ $height }}% + 0.6rem)" aria-hidden="true">
                                        <strong class="block text-sm font-semibold tabular-nums">{{ number_format($month['count']) }}</strong>
                                        <span class="block">{{ Str::plural('enrollment', $month['count']) }} · {{ $month['name'] }}</span>
                                    </span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                    <ol class="mt-2 flex" aria-hidden="true">
                        @foreach ($enrollmentTrend as $month)
                            <li @class(['flex-1 text-center text-[0.6875rem]', 'font-semibold text-slate-700' => $loop->last, 'text-slate-500' => ! $loop->last])>{{ $month['label'] }}</li>
                        @endforeach
                    </ol>
                    @if ($trendTotal === 0)
                        <p class="absolute inset-x-0 top-16 text-center text-sm text-slate-500">No enrollments in this period yet.</p>
                    @endif
                </div>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                <details class="group text-sm">
                    <summary class="inline-flex cursor-pointer list-none items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-800">
                        <x-icon name="chevron-right" class="size-3.5 transition group-open:rotate-90" /> Show as table
                    </summary>
                    <table class="table-base mt-3">
                        <caption class="sr-only">Enrollments by month</caption>
                        <thead><tr><th scope="col">Month</th><th scope="col" class="text-right">Enrollments</th></tr></thead>
                        <tbody>
                            @foreach ($enrollmentTrend as $month)
                                <tr><td>{{ $month['name'] }}</td><td class="text-right tabular-nums">{{ number_format($month['count']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </details>
                <a href="{{ route('admin.enrollments.index') }}" class="self-start text-xs font-medium text-navy-700 hover:underline">View enrollments</a>
            </div>
        </section>

        <div class="flex flex-col gap-6">
            {{-- Pending actions --}}
            <section class="card" aria-labelledby="pending-title">
                <div class="flex items-center justify-between px-5 py-4">
                    <h2 id="pending-title" class="text-sm font-semibold text-slate-900">Pending actions</h2>
                    @if ($pending)
                        <span class="admin-badge admin-badge--inline">{{ number_format(array_sum(array_column($pending, 'count'))) }}</span>
                    @endif
                </div>
                @if ($pending)
                    <ul class="divide-y divide-slate-100 border-t border-slate-100">
                        @foreach ($pending as $item)
                            <li>
                                <a href="{{ $item['href'] }}" class="group flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500"><x-icon :name="$item['icon']" class="size-4" /></span>
                                    <span class="min-w-0 flex-1">
                                        <span class="block text-sm font-medium text-slate-900">{{ $item['label'] }}</span>
                                        <span class="block text-xs text-slate-500">{{ $item['hint'] }}</span>
                                    </span>
                                    <span class="admin-badge admin-badge--inline">{{ number_format($item['count']) }}</span>
                                    <x-icon name="chevron-right" class="size-4 text-slate-300 group-hover:text-slate-500" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="flex items-center gap-3 border-t border-slate-100 px-5 py-4">
                        <span class="flex size-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700"><x-icon name="circle-check" class="size-4" /></span>
                        <div>
                            <p class="text-sm font-medium text-slate-900">All caught up</p>
                            <p class="text-xs text-slate-500">No enquiries, messages, certificates or drafts are waiting.</p>
                        </div>
                    </div>
                @endif
            </section>

            {{-- Recently issued certificates --}}
            <section class="card flex-1 overflow-hidden" aria-labelledby="certificates-title">
                <div class="flex items-center justify-between px-5 py-4">
                    <h2 id="certificates-title" class="text-sm font-semibold text-slate-900">Recent certificates</h2>
                    <a href="{{ route('admin.certificates.index') }}" class="text-xs font-medium text-navy-700 hover:underline">View all</a>
                </div>
                <ul class="divide-y divide-slate-100 border-t border-slate-100">
                    @forelse ($recentCertificates as $certificate)
                        <li>
                            <a href="{{ route('admin.certificates.show', $certificate) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50">
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-slate-900">{{ $certificate->student_name_snapshot }}</span>
                                    <span class="block font-mono text-xs text-slate-500">{{ $certificate->certificate_number }}</span>
                                </span>
                                <span class="flex shrink-0 flex-col items-end gap-1">
                                    <x-status-badge :status="$certificate->status" />
                                    <span class="text-xs text-slate-500">{{ $certificate->issue_date->format('d M Y') }}</span>
                                </span>
                            </a>
                        </li>
                    @empty
                        <li><x-empty-state icon="award" title="No certificates yet" /></li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- Recent enrollments --}}
        <section class="card overflow-hidden xl:col-span-2" aria-labelledby="enrollments-title">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 id="enrollments-title" class="text-sm font-semibold text-slate-900">Recent enrollments</h2>
                <a href="{{ route('admin.enrollments.index') }}" class="text-xs font-medium text-navy-700 hover:underline">View all</a>
            </div>
            @if ($recentEnrollments->isEmpty())
                <x-empty-state icon="clipboard-list" title="No enrollments yet" />
            @else
                <div class="overflow-x-auto border-t border-slate-100">
                    <table class="table-base">
                        <thead><tr><th scope="col">Student</th><th scope="col">Course</th><th scope="col">Enrollment no.</th><th scope="col">Date</th><th scope="col">Status</th></tr></thead>
                        <tbody>
                            @foreach ($recentEnrollments as $enrollment)
                                <tr>
                                    <td class="whitespace-nowrap"><a href="{{ route('admin.enrollments.show', $enrollment) }}" class="font-medium text-slate-900 hover:text-navy-700">{{ $enrollment->student->full_name }}</a></td>
                                    <td><span class="block max-w-56 truncate" title="{{ $enrollment->course->title }}">{{ $enrollment->course->title }}</span></td>
                                    <td class="font-mono text-xs">{{ $enrollment->enrollment_number }}</td>
                                    <td class="whitespace-nowrap text-slate-500">{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                                    <td><x-status-badge :status="$enrollment->status" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        {{-- New students --}}
        <section class="card overflow-hidden" aria-labelledby="students-title">
            <div class="flex items-center justify-between px-5 py-4">
                <h2 id="students-title" class="text-sm font-semibold text-slate-900">New students</h2>
                <a href="{{ route('admin.students.index') }}" class="text-xs font-medium text-navy-700 hover:underline">View all</a>
            </div>
            <ul class="divide-y divide-slate-100 border-t border-slate-100">
                @forelse ($recentStudents as $student)
                    <li>
                        <a href="{{ route('admin.students.show', $student) }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-slate-50">
                            <span class="flex size-8 items-center justify-center rounded-full bg-navy-50 text-xs font-semibold text-navy-700">{{ $student->initials }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-medium text-slate-900">{{ $student->full_name }}</span>
                                <span class="block font-mono text-xs text-slate-500">{{ $student->registration_number }}</span>
                            </span>
                            <span class="shrink-0 text-xs text-slate-500">{{ $student->created_at->diffForHumans(short: true) }}</span>
                        </a>
                    </li>
                @empty
                    <li><x-empty-state icon="users" title="No students yet" /></li>
                @endforelse
            </ul>
        </section>

        {{-- Latest activity (admins) --}}
        @if ($recentActivity->isNotEmpty())
            <section class="card overflow-hidden xl:col-span-3" aria-labelledby="activity-title">
                <div class="flex items-center justify-between px-5 py-4">
                    <h2 id="activity-title" class="text-sm font-semibold text-slate-900">Latest activity</h2>
                    <a href="{{ route('admin.activity.index') }}" class="text-xs font-medium text-navy-700 hover:underline">Open activity log</a>
                </div>
                <div class="overflow-x-auto border-t border-slate-100">
                    <table class="table-base">
                        <thead><tr><th scope="col">Event</th><th scope="col">By</th><th scope="col" class="text-right">When</th></tr></thead>
                        <tbody>
                            @foreach ($recentActivity as $log)
                                <tr>
                                    <td>
                                        <span class="flex items-center gap-3">
                                            <span @class([
                                                'flex size-7 shrink-0 items-center justify-center rounded-md',
                                                'bg-red-50 text-red-700' => $log->action === 'order.needs_refund',
                                                'bg-slate-100 text-slate-500' => $log->action !== 'order.needs_refund',
                                            ])><x-icon :name="$activityIcons[Str::before($log->action, '.')] ?? 'activity'" class="size-3.5" /></span>
                                            <span class="text-slate-800">{{ $log->description ?? Str::headline($log->action) }}</span>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-slate-500">{{ $log->user?->name ?? 'System' }}</td>
                                    <td class="text-right whitespace-nowrap text-slate-500"><time datetime="{{ $log->created_at?->toIso8601String() }}">{{ $log->created_at?->diffForHumans() }}</time></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif
    </div>
</x-layouts.admin>
