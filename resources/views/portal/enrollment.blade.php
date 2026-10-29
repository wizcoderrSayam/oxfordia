<x-layouts.portal title="Marksheet">
    <x-page-header :title="$enrollment->course->title" :subtitle="'Enrollment '.$enrollment->enrollment_number.' · Registration '.$student->registration_number" :back="route('portal.dashboard')">
        @if ($enrollment->validCertificate)
            <a href="{{ route('portal.certificates.download', $enrollment->validCertificate) }}" class="btn-primary"><x-icon name="download" /> Download certificate</a>
        @endif
    </x-page-header>

    <div class="grid gap-4 sm:grid-cols-4">
        <div class="card p-5"><p class="text-sm text-slate-500">Status</p><p class="mt-1"><x-status-badge :status="$enrollment->status" /></p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Total</p><p class="mt-1 font-display text-xl font-bold">{{ $enrollment->total_marks !== null ? rtrim(rtrim($enrollment->total_marks, '0'), '.').' / '.$enrollment->max_total_marks : '—' }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Percentage</p><p class="mt-1 font-display text-xl font-bold">{{ $enrollment->percentage !== null ? $enrollment->percentage.'%' : '—' }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Result</p><p class="mt-1 font-display text-xl font-bold">@if ($enrollment->result) {{ $enrollment->final_grade }} <x-status-badge :status="$enrollment->result" /> @else Pending @endif</p></div>
    </div>

    <div class="card mt-6 overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>Module</th><th class="text-right">Max</th><th class="text-right">Pass</th><th class="text-right">Marks</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($enrollment->course->modules as $module)
                    @php($mark = $marks->get($module->id))
                    <tr>
                        <td class="font-medium text-slate-900">{{ $module->name }}</td>
                        <td class="text-right">{{ $module->max_marks }}@if ($module->hasPractical())<span class="block text-xs text-slate-500">Th {{ $module->theory_max_marks }} + Pr {{ $module->practical_max_marks }}</span>@endif</td>
                        <td class="text-right">{{ $module->pass_marks }}@if ($module->hasPractical())<span class="block text-xs text-slate-500">Th {{ $module->theory_pass_marks }} + Pr {{ $module->practical_pass_marks }}</span>@endif</td>
                        <td class="text-right font-semibold">{{ $mark ? rtrim(rtrim($mark->marks, '0'), '.') : '—' }}@if ($mark && $module->hasPractical())<span class="block text-xs font-normal text-slate-500">Th {{ rtrim(rtrim($mark->theory_marks, '0'), '.') }} + Pr {{ rtrim(rtrim($mark->practical_marks, '0'), '.') }}</span>@endif</td>
                        <td>
                            @if (! $mark)
                                <span class="text-slate-400">Awaiting marks</span>
                            @elseif ($mark->isPassed($module))
                                <x-badge color="green">Passed</x-badge>
                            @else
                                <x-badge color="red">Not passed</x-badge>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="clipboard-list" title="No modules yet" message="Assessment modules for this course haven't been published." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.portal>
