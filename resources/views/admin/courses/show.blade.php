<x-layouts.admin :title="$course->title">
    <x-page-header :title="$course->title" :subtitle="$course->course_code.' · '.($course->category?->name ?? 'Uncategorised')" :back="route('admin.courses.index')">
        <x-status-badge :status="$course->status" />
        <a href="{{ route('admin.courses.marks.edit', $course) }}" class="btn-secondary"><x-icon name="clipboard-list" /> Enter marks</a>
        <a href="{{ route('admin.courses.edit', $course) }}" class="btn-primary"><x-icon name="pencil" /> Edit</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Details</h2>
                <dl class="mt-2 divide-y divide-slate-100">
                    <x-dl-row label="Slug"><span class="font-mono text-xs">{{ $course->slug }}</span></x-dl-row>
                    <x-dl-row label="Duration">{{ $course->duration_label }}</x-dl-row>
                    <x-dl-row label="Fee">{{ $course->fee !== null ? '₹'.number_format((float) $course->fee, 2) : 'Not sold online (admission enquiry)' }}</x-dl-row>
                    <x-dl-row label="Featured">{{ $course->is_featured ? 'Yes' : 'No' }}</x-dl-row>
                    <x-dl-row label="Short description">{{ $course->short_description }}</x-dl-row>
                    <x-dl-row label="Syllabus">@if ($course->syllabus_path)<a href="{{ Storage::disk('public')->url($course->syllabus_path) }}" target="_blank" class="text-navy-700 hover:underline">Download PDF</a>@endif</x-dl-row>
                </dl>
            </section>

            <section id="modules" class="card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Assessment modules</h2>
                    <p class="text-sm text-slate-500">Students must reach the theory and practical pass marks in every module. Set practical max to 0 for a theory-only module. Max and pass marks lock once marks are recorded.</p>
                </div>
                @if ($errors->module->any())
                    <x-callout tone="danger" class="mx-6 mt-4">{{ $errors->module->first() }}</x-callout>
                @endif
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead><tr><th>#</th><th>Module</th><th class="text-right">Max <span class="font-normal normal-case">(Th + Pr)</span></th><th class="text-right">Pass <span class="font-normal normal-case">(Th + Pr)</span></th><th><span class="sr-only">Actions</span></th></tr></thead>
                        <tbody>
                            @forelse ($course->modules as $module)
                                @php($locked = $module->marks_count > 0)
                                <tr x-data="{ editing: false }">
                                    <td class="w-12 text-slate-400">{{ $loop->iteration }}</td>
                                    <td>
                                        <span x-show="!editing" class="font-medium text-slate-900">{{ $module->name }}</span>
                                        @if ($locked)<x-badge color="slate" class="ml-1" x-show="!editing"><x-icon name="lock" class="mr-1 size-3" /> Locked</x-badge>@endif
                                        <form x-show="editing" x-cloak method="POST" action="{{ route('admin.modules.update', $module) }}" class="flex flex-wrap items-end gap-2">
                                            @csrf @method('PUT')
                                            <input name="name" value="{{ $module->name }}" class="form-input w-48" aria-label="Module name" required>
                                            <input name="theory_max_marks" type="number" min="0" value="{{ $module->theory_max_marks }}" class="form-input w-20" aria-label="Theory max marks" title="Theory max" @disabled($locked) required>
                                            <input name="practical_max_marks" type="number" min="0" value="{{ $module->practical_max_marks }}" class="form-input w-20" aria-label="Practical max marks" title="Practical max" @disabled($locked) required>
                                            <input name="theory_pass_marks" type="number" min="0" value="{{ $module->theory_pass_marks }}" class="form-input w-20" aria-label="Theory pass marks" title="Theory pass" @disabled($locked) required>
                                            <input name="practical_pass_marks" type="number" min="0" value="{{ $module->practical_pass_marks }}" class="form-input w-20" aria-label="Practical pass marks" title="Practical pass" @disabled($locked) required>
                                            <input name="sort_order" type="number" min="0" value="{{ $module->sort_order }}" class="form-input w-20" aria-label="Order">
                                            <button type="submit" class="btn-primary btn-sm">Save</button>
                                            <button type="button" class="btn-secondary btn-sm" @click="editing = false">Cancel</button>
                                        </form>
                                    </td>
                                    <td class="text-right" x-show="!editing">{{ $module->max_marks }} <span class="block text-xs text-slate-500">{{ $module->theory_max_marks }} + {{ $module->practical_max_marks }}</span></td>
                                    <td class="text-right" x-show="!editing">{{ $module->pass_marks }} <span class="block text-xs text-slate-500">{{ $module->theory_pass_marks }} + {{ $module->practical_pass_marks }}</span></td>
                                    <td x-show="!editing">
                                        <div class="flex justify-end gap-2">
                                            <button type="button" class="btn-secondary btn-sm" @click="editing = true"><x-icon name="pencil" /></button>
                                            @unless ($locked)
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
                    <div><label for="module-theory-pass" class="form-label">Theory pass</label><input id="module-theory-pass" name="theory_pass_marks" type="number" min="0" value="28" class="form-input" required></div>
                    <div><label for="module-practical-pass" class="form-label">Practical pass</label><input id="module-practical-pass" name="practical_pass_marks" type="number" min="0" value="12" class="form-input" required></div>
                    <button type="submit" class="btn-primary col-span-2"><x-icon name="plus" /> Add</button>
                </form>
            </section>

            <section class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h2 class="text-base font-semibold text-slate-900">Enrolled students</h2>
                    <a href="{{ route('admin.enrollments.create', ['course' => $course->id]) }}" class="btn-secondary btn-sm"><x-icon name="plus" /> Enroll student</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="table-base">
                        <thead><tr><th>Student</th><th>Enrollment</th><th>Status</th><th>Grade</th><th>Certificate</th></tr></thead>
                        <tbody>
                            @forelse ($enrollments as $enrollment)
                                <tr>
                                    <td><a href="{{ route('admin.students.show', $enrollment->student) }}" class="font-medium text-slate-900 hover:text-navy-700">{{ $enrollment->student->full_name }}</a><p class="text-xs text-slate-500">{{ $enrollment->student->registration_number }}</p></td>
                                    <td><a href="{{ route('admin.enrollments.show', $enrollment) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $enrollment->enrollment_number }}</a></td>
                                    <td><x-status-badge :status="$enrollment->status" /></td>
                                    <td>{{ $enrollment->final_grade ?? '—' }}</td>
                                    <td>@if ($enrollment->validCertificate)<a href="{{ route('admin.certificates.show', $enrollment->validCertificate) }}" class="font-mono text-xs text-navy-700 hover:underline">{{ $enrollment->validCertificate->certificate_number }}</a>@else — @endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="5"><x-empty-state icon="users" title="No students enrolled yet" /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($enrollments->hasPages())<div class="border-t border-slate-100 px-6 py-3">{{ $enrollments->links() }}</div>@endif
            </section>
        </div>

        <aside class="space-y-6">
            <div class="card overflow-hidden"><img src="{{ $course->image_url }}" alt="" class="aspect-[16/10] w-full object-cover"></div>
            <div class="card space-y-3 p-6">
                <h2 class="text-base font-semibold text-slate-900">Publication</h2>
                @foreach (App\Enums\CourseStatus::cases() as $status)
                    @continue($status === $course->status)
                    <form method="POST" action="{{ route('admin.courses.status', $course) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $status->value }}">
                        <button type="submit" class="btn-secondary w-full">
                            <x-icon :name="match ($status) { App\Enums\CourseStatus::Published => 'globe', App\Enums\CourseStatus::Archived => 'archive', default => 'pencil' }" />
                            {{ match ($status) { App\Enums\CourseStatus::Published => 'Publish', App\Enums\CourseStatus::Archived => 'Archive', default => 'Move to draft' } }}
                        </button>
                    </form>
                @endforeach
            </div>
            <div class="card p-6">
                <h2 class="text-base font-semibold text-red-700">Danger zone</h2>
                @if ($course->hasHistory())
                    <p class="mt-2 text-sm text-slate-600">This course has enrollments, orders or certificates, so it can’t be deleted. Archive it to hide it from the catalogue.</p>
                @else
                    <p class="mt-2 mb-4 text-sm text-slate-600">Permanently delete this course and its modules.</p>
                    <x-confirm :action="route('admin.courses.destroy', $course)" method="DELETE" title="Delete course?" message="This permanently deletes the course, its modules and uploaded files." confirm-label="Delete course" trigger-class="btn-danger w-full">
                        <x-slot:trigger><x-icon name="trash-2" /> Delete course</x-slot:trigger>
                    </x-confirm>
                @endif
            </div>
        </aside>
    </div>
</x-layouts.admin>
