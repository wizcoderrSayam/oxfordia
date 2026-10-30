<x-layouts.admin title="Employees">
    <x-page-header title="Employees" subtitle="Staff records that can be checked on the public employee verification page.">
        <a href="{{ route('employees.verify') }}" target="_blank" rel="noopener" class="btn-secondary"><x-icon name="external-link" /> Verification page</a>
        <a href="{{ route('admin.employees.create') }}" class="btn-primary"><x-icon name="user-plus" /> New employee</a>
    </x-page-header>

    <form method="GET" class="card mb-4 grid gap-3 p-4 sm:grid-cols-4" role="search">
        <div class="sm:col-span-3">
            <label for="q" class="sr-only">Search</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Search name, registration no. or position" class="form-input">
        </div>
        <div class="flex gap-2">
            <label for="status" class="sr-only">Status</label>
            <select id="status" name="status" class="form-input">
                <option value="">All statuses</option>
                @foreach (App\Enums\EmployeeStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary" aria-label="Apply filters"><x-icon name="search" /></button>
        </div>
    </form>

    <div class="card overflow-x-auto">
        @if ($employees->isEmpty())
            <x-empty-state icon="id-card" title="No employees found" message="Adjust the filters or register a new employee.">
                <a href="{{ route('admin.employees.create') }}" class="btn-primary">New employee</a>
            </x-empty-state>
        @else
            <table class="table-base">
                <thead><tr><th>Employee</th><th>Reg. no.</th><th>Position</th><th>Date of birth</th><th>Joined</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @foreach ($employees as $employee)
                        <tr>
                            <td>
                                <a href="{{ route('admin.employees.show', $employee) }}" class="flex items-center gap-3">
                                    @if ($employee->photo_url)
                                        <img src="{{ $employee->photo_url }}" alt="" class="size-9 rounded-full object-cover">
                                    @else
                                        <span class="flex size-9 items-center justify-center rounded-full bg-navy-50 text-xs font-bold text-navy-700">{{ $employee->initials }}</span>
                                    @endif
                                    <span class="font-semibold text-slate-900 hover:text-navy-700">{{ $employee->full_name }}</span>
                                </a>
                            </td>
                            <td class="font-mono text-xs">{{ $employee->registration_number }}</td>
                            <td>{{ $employee->position }}</td>
                            <td>{{ $employee->date_of_birth?->format('d M Y') }}</td>
                            <td>{{ $employee->joining_date->format('d M Y') }}</td>
                            <td><x-status-badge :status="$employee->status" /></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.employees.edit', $employee) }}" class="btn-secondary btn-sm"><x-icon name="pencil" /> Edit</a>
                                    <x-confirm :action="route('admin.employees.destroy', $employee)" method="DELETE" title="Delete employee?" :message="'“'.$employee->full_name.'” ('.$employee->registration_number.') will be permanently deleted and will no longer verify. This cannot be undone.'" confirm-label="Delete employee">
                                        <x-slot:trigger><x-icon name="trash-2" /> Delete</x-slot:trigger>
                                    </x-confirm>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="mt-4">{{ $employees->links() }}</div>
</x-layouts.admin>
