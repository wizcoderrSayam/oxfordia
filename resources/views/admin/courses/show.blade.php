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

            @include('admin.courses._modules')

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
                @if ($course->hasProtectedRecords())
                    <p class="mt-2 text-sm text-slate-600">This course has an issued certificate or a payment order, so it can’t be deleted. Archive it to hide it from the catalogue.</p>
                @else
                    <p class="mt-2 mb-4 text-sm text-slate-600">Permanently delete this course and its modules{{ $enrollments->total() > 0 ? ', along with its enrollments, marks and result documents' : '' }}.</p>
                    <x-confirm :action="route('admin.courses.destroy', $course)" method="DELETE" title="Delete course?" :message="'This permanently deletes the course, its modules and uploaded files'.($enrollments->total() > 0 ? ', along with its enrollments, marks and result documents' : '').'. This cannot be undone.'" confirm-label="Delete course" trigger-class="btn-danger w-full">
                        <x-slot:trigger><x-icon name="trash-2" /> Delete course</x-slot:trigger>
                    </x-confirm>
                @endif
            </div>
        </aside>
    </div>
</x-layouts.admin>
