<x-layouts.admin :title="'Marks · '.$course->title">
    <x-page-header :title="'Marks: '.$course->title" subtitle="Enter theory (Th) and practical (Pr) marks per module. Totals, percentage and grade update as you type. Rows with a valid certificate are locked." :back="route('admin.courses.show', $course)" />

    @if ($course->modules->isEmpty())
        <div class="card">
            <x-empty-state icon="layers" title="This course has no modules" message="Add assessment modules on the course page before entering marks.">
                <a href="{{ route('admin.courses.show', $course) }}#modules" class="btn-primary">Add modules</a>
            </x-empty-state>
        </div>
    @elseif ($enrollments->isEmpty())
        <div class="card">
            <x-empty-state icon="users" title="No active enrollments" message="Enroll students in this course to record their marks.">
                <a href="{{ route('admin.enrollments.create', ['course' => $course->id]) }}" class="btn-primary">Enroll a student</a>
            </x-empty-state>
        </div>
    @else
        @php
            $moduleData = $course->modules->map(fn ($m) => [
                'id' => $m->id,
                'theory' => ['max' => $m->theory_max_marks, 'pass' => $m->theory_pass_marks],
                'practical' => ['max' => $m->practical_max_marks, 'pass' => $m->practical_pass_marks],
            ])->values();
            $partsFor = fn ($module) => array_filter(['theory' => 'Th', 'practical' => 'Pr'], fn ($label, $part) => $module->{$part.'_max_marks'} > 0, ARRAY_FILTER_USE_BOTH);
        @endphp
        <form method="POST" action="{{ route('admin.courses.marks.update', $course) }}" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @method('PUT')
            <div class="card overflow-x-auto">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 bg-slate-50">Student</th>
                            @foreach ($course->modules as $module)
                                <th class="min-w-28 text-center">{{ $module->name }}
                                    @foreach ($partsFor($module) as $part => $label)
                                        <span class="block font-normal normal-case">{{ $label }} /{{ $module->{$part.'_max_marks'} }} (min {{ $module->{$part.'_pass_marks'} }})</span>
                                    @endforeach
                                </th>
                            @endforeach
                            <th class="text-right">Total</th>
                            <th class="text-right">%</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            @php
                                $existing = $enrollment->marks->keyBy('course_module_id');
                                $initial = $course->modules->mapWithKeys(function ($module) use ($enrollment, $existing) {
                                    $mark = $existing->get($module->id);
                                    $value = fn (string $part) => old("marks.{$enrollment->id}.{$module->id}.{$part}") ?? $mark?->{$part.'_marks'};

                                    return [$module->id => collect(['theory', 'practical'])->mapWithKeys(fn ($part) => [
                                        $part => $value($part) === null ? '' : (string) (float) $value($part),
                                    ])];
                                });
                                $locked = (bool) $enrollment->validCertificate;
                            @endphp
                            <tr x-data="marksRow(@js($moduleData), @js($initial), @js($bands))" @class(['bg-slate-50/60' => $locked])>
                                <td class="sticky left-0 z-10 bg-white">
                                    <span class="block font-medium text-slate-900">{{ $enrollment->student->full_name }}</span>
                                    <span class="block text-xs text-slate-500">{{ $enrollment->student->registration_number }} · <x-status-badge :status="$enrollment->status" /></span>
                                    @if ($locked)<span class="mt-1 inline-flex items-center gap-1 text-xs text-slate-500"><x-icon name="lock" class="size-3" /> Certificate issued</span>@endif
                                </td>
                                @foreach ($course->modules as $module)
                                    <td class="text-center">
                                        <div class="flex justify-center gap-1">
                                            @foreach ($partsFor($module) as $part => $label)
                                                <div>
                                                    <label for="m-{{ $enrollment->id }}-{{ $module->id }}-{{ $part }}" class="block text-xs text-slate-500">{{ $label }}<span class="sr-only"> marks for {{ $enrollment->student->full_name }} – {{ $module->name }}</span></label>
                                                    <input id="m-{{ $enrollment->id }}-{{ $module->id }}-{{ $part }}" type="number" step="0.01" min="0" max="{{ $module->{$part.'_max_marks'} }}" inputmode="decimal"
                                                           @unless ($locked) name="marks[{{ $enrollment->id }}][{{ $module->id }}][{{ $part }}]" @endunless
                                                           x-model="marks[{{ $module->id }}].{{ $part }}"
                                                           :class="invalid({{ $module->id }}, '{{ $part }}') ? 'border-red-500 ring-2 ring-red-200' : ''"
                                                           class="form-input w-20 text-center" @disabled($locked)>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error("marks.{$enrollment->id}.{$module->id}") <p class="form-error text-xs">{{ $message }}</p> @enderror
                                    </td>
                                @endforeach
                                <td class="text-right font-semibold" x-text="complete ? total.toFixed(2).replace(/\.00$/, '') + ' / ' + max : '—'"></td>
                                <td class="text-right" x-text="complete ? percentage.toFixed(2) + '%' : '—'"></td>
                                <td><span class="font-bold" :class="grade === 'Fail' ? 'text-red-600' : 'text-emerald-700'" x-text="grade"></span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500">Leave a cell blank if the assessment hasn't happened yet. Results become final once every module has marks.</p>
                <button type="submit" class="btn-primary" :disabled="submitting">
                    <span x-show="!submitting">Save marks</span><span x-show="submitting" x-cloak>Saving…</span>
                </button>
            </div>
        </form>
    @endif
</x-layouts.admin>
