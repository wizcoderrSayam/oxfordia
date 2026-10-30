{{-- Assessment modules (subjects) of a course: list, inline edit and add. Expects $course with modules loaded withCount('marks'). --}}
<section id="modules" class="card overflow-hidden">
    <div class="border-b border-slate-100 px-6 py-4">
        <h2 class="text-base font-semibold text-slate-900">Assessment modules</h2>
        <p class="text-sm text-slate-500">Students must reach the theory and practical min marks in every module. Set practical max to 0 for a theory-only module. Changing marks recalculates results, except for students who already have a certificate.</p>
    </div>
    @if ($errors->module->any())
        <x-callout tone="danger" class="mx-6 mt-4">{{ $errors->module->first() }}</x-callout>
    @endif
    <div class="overflow-x-auto">
        <table class="table-base">
            <thead><tr><th>#</th><th>Module</th><th class="text-right">Max <span class="font-normal normal-case">(Th + Pr)</span></th><th class="text-right">Min <span class="font-normal normal-case">(Th + Pr)</span></th><th><span class="sr-only">Actions</span></th></tr></thead>
            <tbody>
                @forelse ($course->modules as $module)
                    @php($hasMarks = $module->marks_count > 0)
                    <tr x-data="{ editing: false }">
                        <td class="w-12 text-slate-400">{{ $loop->iteration }}</td>
                        <td x-show="!editing">
                            <span class="font-medium text-slate-900">{{ $module->name }}</span>
                        </td>
                        <td colspan="4" x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('admin.modules.update', $module) }}" class="space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                @csrf @method('PUT')
                                <div class="grid gap-3 sm:grid-cols-[1fr_7rem]">
                                    <div><label for="module-{{ $module->id }}-name" class="form-label">Module name</label><input id="module-{{ $module->id }}-name" name="name" value="{{ $module->name }}" class="form-input" required></div>
                                    <div><label for="module-{{ $module->id }}-order" class="form-label">Position</label><input id="module-{{ $module->id }}-order" name="sort_order" type="number" min="0" value="{{ $module->sort_order }}" class="form-input"></div>
                                </div>
                                <div class="grid max-w-sm grid-cols-[6rem_1fr_1fr] items-center gap-x-3 gap-y-2">
                                    <span></span>
                                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Max marks</span>
                                    <span class="text-xs font-semibold tracking-wide text-slate-500 uppercase">Min marks</span>
                                    <span class="text-sm font-medium text-slate-700">Theory</span>
                                    <input name="theory_max_marks" type="number" min="0" value="{{ $module->theory_max_marks }}" class="form-input" aria-label="Theory max marks" required>
                                    <input name="theory_pass_marks" type="number" min="0" value="{{ $module->theory_pass_marks }}" class="form-input" aria-label="Theory min marks" required>
                                    <span class="text-sm font-medium text-slate-700">Practical</span>
                                    <input name="practical_max_marks" type="number" min="0" value="{{ $module->practical_max_marks }}" class="form-input" aria-label="Practical max marks" required>
                                    <input name="practical_pass_marks" type="number" min="0" value="{{ $module->practical_pass_marks }}" class="form-input" aria-label="Practical min marks" required>
                                </div>
                                <p class="text-xs text-slate-500">Set practical max and min to 0 for a theory-only module.</p>
                                <div class="flex gap-2">
                                    <button type="submit" class="btn-primary btn-sm">Save</button>
                                    <button type="button" class="btn-secondary btn-sm" @click="editing = false">Cancel</button>
                                </div>
                            </form>
                        </td>
                        <td class="text-right" x-show="!editing">{{ $module->max_marks }} <span class="block text-xs text-slate-500">{{ $module->theory_max_marks }} + {{ $module->practical_max_marks }}</span></td>
                        <td class="text-right" x-show="!editing">{{ $module->pass_marks }} <span class="block text-xs text-slate-500">{{ $module->theory_pass_marks }} + {{ $module->practical_pass_marks }}</span></td>
                        <td x-show="!editing">
                            <div class="flex justify-end gap-2">
                                <button type="button" class="btn-secondary btn-sm" @click="editing = true"><x-icon name="pencil" /></button>
                                @unless ($hasMarks)
                                    <x-confirm :action="route('admin.modules.destroy', $module)" method="DELETE" title="Delete module?" :message="'“'.$module->name.'” will be removed from this course.'" confirm-label="Delete">
                                        <x-slot:trigger><x-icon name="trash-2" /></x-slot:trigger>
                                    </x-confirm>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state icon="layers" title="No modules yet" message="Add at least one module so marks and results can be recorded." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <form method="POST" action="{{ route('admin.courses.modules.store', $course) }}" class="grid grid-cols-2 gap-3 border-t border-slate-100 bg-slate-50 px-6 py-4 sm:grid-cols-6 sm:items-end">
        @csrf
        <div class="col-span-2 sm:col-span-6"><label for="module-name" class="form-label">New module</label><input id="module-name" name="name" class="form-input" placeholder="e.g. HTML & CSS Fundamentals" required></div>
        <div><label for="module-theory-max" class="form-label">Theory max</label><input id="module-theory-max" name="theory_max_marks" type="number" min="0" value="70" class="form-input" required></div>
        <div><label for="module-practical-max" class="form-label">Practical max</label><input id="module-practical-max" name="practical_max_marks" type="number" min="0" value="30" class="form-input" required></div>
        <div><label for="module-theory-pass" class="form-label">Theory min</label><input id="module-theory-pass" name="theory_pass_marks" type="number" min="0" value="28" class="form-input" required></div>
        <div><label for="module-practical-pass" class="form-label">Practical min</label><input id="module-practical-pass" name="practical_pass_marks" type="number" min="0" value="12" class="form-input" required></div>
        <button type="submit" class="btn-primary col-span-2"><x-icon name="plus" /> Add</button>
    </form>
</section>
