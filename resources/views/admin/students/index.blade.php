<x-layouts.admin title="Students">
    <x-page-header title="Students" subtitle="Registered students, their status and enrollments.">
        <a href="{{ route('admin.students.create') }}" class="btn-primary"><x-icon name="user-plus" /> New student</a>
    </x-page-header>

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-3">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, registration no., email or phone" class="form-input">
        </div>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\StudentStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($students->isEmpty())
            <x-empty-state icon="users" title="No students found" message="Adjust the filters or register a new student.">
                <a href="{{ route('admin.students.create') }}" class="btn-primary">New student</a>
            </x-empty-state>
        @else
            <table class="table-base">
                <thead><tr><th>Student</th><th>Reg. no.</th><th>Phone</th><th>Admitted</th><th class="text-right">Enrollments</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>
                                <a href="{{ route('admin.students.show', $student) }}" class="flex items-center gap-3">
                                    @if ($student->photo_url)
                                        <img src="{{ $student->photo_url }}" alt="" class="size-9 rounded-full object-cover">
                                    @else
                                        <span class="flex size-9 items-center justify-center rounded-full bg-navy-50 text-xs font-bold text-navy-700">{{ $student->initials }}</span>
                                    @endif
                                    <span>
                                        <span class="block font-semibold text-slate-900 hover:text-navy-700">{{ $student->full_name }}</span>
                                        <span class="block text-xs text-slate-500">{{ $student->email ?? 'No email' }}</span>
                                    </span>
                                </a>
                            </td>
                            <td class="font-mono text-xs">{{ $student->registration_number }}</td>
                            <td>{{ $student->phone }}</td>
                            <td>{{ $student->admission_date->format('d M Y') }}</td>
                            <td class="text-right">{{ $student->enrollments_count }}</td>
                            <td><x-status-badge :status="$student->status" /></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
</x-layouts.admin>
