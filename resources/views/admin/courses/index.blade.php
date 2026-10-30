<x-layouts.admin title="Courses">
    <x-page-header title="Courses" subtitle="Manage the course catalogue, modules and publication status.">
        <a href="{{ route('admin.courses.create') }}" class="btn-primary"><x-icon name="plus" /> New course</a>
    </x-page-header>

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Search title or course code" class="form-input">
        </div>
        <label for="category" class="sr-only">Category</label>
        <select id="category" name="category" class="form-input">
            <option value="">All categories</option>
            @foreach ($categories as $id => $name)
                <option value="{{ $id }}" @selected((string) ($filters['category'] ?? '') === (string) $id)>{{ $name }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\CourseStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($courses->isEmpty())
            <x-empty-state icon="book-open" title="No courses found" message="Adjust the filters or create your first course.">
                <a href="{{ route('admin.courses.create') }}" class="btn-primary">New course</a>
            </x-empty-state>
        @else
            <table class="table-base">
                <thead><tr><th>Course</th><th>Category</th><th class="text-right">Fee</th><th class="text-right">Modules</th><th class="text-right">Enrollments</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($courses as $course)
                        <tr>
                            <td>
                                <a href="{{ route('admin.courses.show', $course) }}" class="font-semibold text-slate-900 hover:text-navy-700">{{ $course->title }}</a>
                                <p class="font-mono text-xs text-slate-500">{{ $course->course_code }}</p>
                            </td>
                            <td>{{ $course->category?->name ?? '—' }}</td>
                            <td class="text-right">{{ $course->fee !== null ? '₹'.number_format((float) $course->fee) : '—' }}</td>
                            <td class="text-right">{{ $course->modules_count }}</td>
                            <td class="text-right">{{ $course->enrollments_count }}</td>
                            <td><x-status-badge :status="$course->status" /></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.courses.edit', $course) }}" class="btn-secondary btn-sm"><x-icon name="pencil" /> Edit</a>
                                    @if ($course->orders_count === 0 && $course->certificates_count === 0)
                                        <x-confirm :action="route('admin.courses.destroy', $course)" method="DELETE" title="Delete course?" :message="'“'.$course->title.'” ('.$course->course_code.') will be permanently deleted, along with its modules'.($course->enrollments_count > 0 ? ', '.trans_choice('{1}its enrollment|[2,*]all :count of its enrollments', $course->enrollments_count).', marks and result documents' : '').'. This cannot be undone.'" confirm-label="Delete course">
                                            <x-slot:trigger><x-icon name="trash-2" /> Delete</x-slot:trigger>
                                        </x-confirm>
                                    @else
                                        <span class="inline-block" title="Courses with an issued certificate or a payment order can't be deleted. Archive them instead.">
                                            <button type="button" class="btn-danger btn-sm" disabled aria-disabled="true"><x-icon name="trash-2" /> Delete</button>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $courses->links() }}</div>
</x-layouts.admin>
