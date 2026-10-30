<x-layouts.admin :title="$employee->full_name">
    <x-page-header :title="$employee->full_name" :subtitle="'Registration no. '.$employee->registration_number" :back="route('admin.employees.index')">
        <x-status-badge :status="$employee->status" />
        <a href="{{ route('employees.verify', ['registration_number' => $employee->registration_number]) }}" target="_blank" rel="noopener" class="btn-secondary"><x-icon name="shield-check" /> Open verification page</a>
        <a href="{{ route('admin.employees.edit', $employee) }}" class="btn-primary"><x-icon name="pencil" /> Edit</a>
    </x-page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <section class="card p-6">
                <div class="flex items-center gap-4">
                    @if ($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" alt="" class="size-16 rounded-full object-cover">
                    @else
                        <span class="flex size-16 items-center justify-center rounded-full bg-navy-50 text-lg font-bold text-navy-700">{{ $employee->initials }}</span>
                    @endif
                    <div>
                        <p class="text-base font-semibold text-slate-900">{{ $employee->full_name }}</p>
                        <p class="text-sm text-slate-600">{{ $employee->position }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $employee->registration_number }}</p>
                    </div>
                </div>
                <dl class="mt-4 divide-y divide-slate-100">
                    <x-dl-row label="Father's name">{{ $employee->father_name }}</x-dl-row>
                    <x-dl-row label="Mother's name">{{ $employee->mother_name }}</x-dl-row>
                    <x-dl-row label="Date of birth">{{ $employee->date_of_birth?->format('d M Y') }}</x-dl-row>
                    <x-dl-row label="Address"><span class="whitespace-pre-line">{{ $employee->address }}</span></x-dl-row>
                    <x-dl-row label="Date of joining">{{ $employee->joining_date->format('d M Y') }}</x-dl-row>
                    <x-dl-row label="Status"><x-status-badge :status="$employee->status" /></x-dl-row>
                </dl>
            </section>

            <section class="card p-6">
                <h2 class="text-base font-semibold text-slate-900">Bio</h2>
                <p class="mt-2 text-sm whitespace-pre-line text-slate-700">{{ $employee->bio ?: 'No bio added.' }}</p>
            </section>
        </div>

        <aside class="space-y-6">
            <section class="card p-6">
                <h2 class="text-base font-semibold text-red-700">Danger zone</h2>
                <p class="mt-2 text-sm text-slate-600">To stop this person verifying as a current employee, change the status to relieved instead of deleting the record.</p>
                <x-confirm :action="route('admin.employees.destroy', $employee)" method="DELETE" title="Delete employee?" message="This permanently deletes the employee record. The registration number will no longer verify." confirm-label="Delete employee" trigger-class="btn-danger mt-3 w-full">
                    <x-slot:trigger><x-icon name="trash-2" /> Delete employee</x-slot:trigger>
                </x-confirm>
            </section>
        </aside>
    </div>
</x-layouts.admin>
