<x-layouts.admin :title="'Marks · '.$course->title">
    <x-page-header :title="'Marks: '.$course->title" subtitle="Enter marks per module. Totals, percentage and grade update as you type. Rows with a valid certificate are locked." :back="route('admin.courses.show', $course)" />

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
            $moduleData = $course->modules->map(fn ($m) => ['id' => $m->id, 'max' => $m->max_marks, 'pass' => $m->pass_marks])->values();
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
                                <th class="min-w-28 text-center">{{ $module->name }}<span class="block font-normal normal-case">/{{ $module->max_marks }} (pass {{ $module->pass_marks }})</span></th>
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
                                    $old = old("marks.{$enrollment->id}.{$module->id}");
                                    $value = $old ?? optional($existing->get($module->id))->marks;

                                    return [$module->id => $value === null ? '' : (string) (float) $value];
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
                                        <label for="m-{{ $enrollment->id }}-{{ $module->id }}" class="sr-only">{{ $enrollment->student->full_name }} – {{ $module->name }}</label>
                                        <input id="m-{{ $enrollment->id }}-{{ $module->id }}" type="number" step="0.01" min="0" max="{{ $module->max_marks }}" inputmode="decimal"
                                               @unless ($locked) name="marks[{{ $enrollment->id }}][{{ $module->id }}]" @endunless
                                               x-model="marks[{{ $module->id }}]"
                                               :class="invalid({ id: {{ $module->id }}, max: {{ $module->max_marks }} }) ? 'border-red-500 ring-2 ring-red-200' : ''"
                                               class="form-input mx-auto w-24 text-center" @disabled($locked)>
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
