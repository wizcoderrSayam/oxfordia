<x-layouts.admin title="Enrollments">
    <x-page-header title="Enrollments" subtitle="Students enrolled in courses, their progress and results.">
        <a href="{{ route('admin.enrollments.create') }}" class="btn-primary"><x-icon name="plus" /> New enrollment</a>
    </x-page-header>

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-2">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Enrollment no., student name or reg. no." class="form-input">
        </div>
        <label for="course" class="sr-only">Course</label>
        <select id="course" name="course" class="form-input">
            <option value="">All courses</option>
            @foreach ($courses as $id => $title)
                <option value="{{ $id }}" @selected((string) ($filters['course'] ?? '') === (string) $id)>{{ $title }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\EnrollmentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($enrollments->isEmpty())
            <x-empty-state icon="clipboard-list" title="No enrollments found" message="Adjust the filters or enroll a student in a course.">
                <a href="{{ route('admin.enrollments.create') }}" class="btn-primary">New enrollment</a>
            </x-empty-state>
        @else
            <table class="table-base">
                <thead><tr><th>Enrollment</th><th>Student</th><th>Course</th><th>Enrolled</th><th>Status</th><th>Result</th></tr></thead>
                <tbody>
                    @foreach ($enrollments as $enrollment)
                        <tr>
                            <td><a href="{{ route('admin.enrollments.show', $enrollment) }}" class="font-mono text-xs font-semibold text-navy-700 hover:underline">{{ $enrollment->enrollment_number }}</a></td>
                            <td><a href="{{ route('admin.students.show', $enrollment->student) }}" class="font-medium text-slate-900 hover:text-navy-700">{{ $enrollment->student->full_name }}</a><p class="text-xs text-slate-500">{{ $enrollment->student->registration_number }}</p></td>
                            <td>{{ $enrollment->course->title }}</td>
                            <td>{{ $enrollment->enrollment_date->format('d M Y') }}</td>
                            <td><x-status-badge :status="$enrollment->status" /></td>
                            <td>
                                @if ($enrollment->result){{ $enrollment->final_grade }} <x-status-badge :status="$enrollment->result" />@else <span class="text-slate-400">Pending</span> @endif
                                @if ($enrollment->validCertificate)<x-icon name="award" class="ml-1 inline text-amber-500" />@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $enrollments->links() }}</div>
</x-layouts.admin>
