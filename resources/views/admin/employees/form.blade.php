<x-layouts.admin :title="$employee->exists ? 'Edit employee' : 'New employee'">
    <x-page-header :title="$employee->exists ? 'Edit '.$employee->full_name : 'New employee'" :subtitle="$employee->exists ? 'Registration no. '.$employee->registration_number : 'A registration number is generated automatically.'" :back="$employee->exists ? route('admin.employees.show', $employee) : route('admin.employees.index')" />

    <x-callout class="mb-6">
        All of these details, including the photo, are shown publicly on the <a href="{{ route('employees.verify') }}" target="_blank" rel="noopener" class="font-semibold underline">employee login page</a> to anyone who enters the registration number and date of birth.
    </x-callout>

    <form id="employee-form" method="POST" action="{{ $employee->exists ? route('admin.employees.update', $employee) : route('admin.employees.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @if ($employee->exists) @method('PUT') @endif

        <div class="card space-y-5 p-6 lg:col-span-2">
            <x-input name="full_name" label="Full name" :value="$employee->full_name" required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-input name="father_name" label="Father's name" :value="$employee->father_name" />
                <x-input name="mother_name" label="Mother's name" :value="$employee->mother_name" />
                <x-input name="date_of_birth" type="date" label="Date of birth" :value="$employee->date_of_birth?->toDateString()" required help="Needed with the registration number for the employee login." />
                <x-input name="position" label="Position" :value="$employee->position" required help="e.g. Senior Faculty, Counsellor." />
            </div>
            <x-textarea name="address" label="Address" :value="$employee->address" rows="3" />
            <x-textarea name="bio" label="Bio" :value="$employee->bio" rows="5" help="A short professional introduction." />
        </div>

        <div class="space-y-6">
            <div class="card space-y-5 p-6">
                <x-input name="joining_date" type="date" label="Date of joining" :value="$employee->joining_date?->toDateString()" required />
                <x-select name="status" label="Status" :options="collect(App\Enums\EmployeeStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$employee->status" required />
            </div>
            <div class="card space-y-4 p-6">
                @if ($employee->photo_url)
                    <img src="{{ $employee->photo_url }}" alt="" class="size-24 rounded-full object-cover">
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300"> Remove photo</label>
                @endif
                <x-input name="photo" type="file" label="Photo" accept="image/jpeg,image/png,image/webp" help="JPG, PNG or WebP, up to 1 MB." />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ $employee->exists ? route('admin.employees.show', $employee) : route('admin.employees.index') }}" class="btn-secondary">Cancel</a>
                @if ($employee->exists)
                    <x-confirm-submit form="employee-form" title="Save changes?" :message="'Update the record for '.$employee->full_name.' ('.$employee->registration_number.')? Changes appear on the employee login page straight away.'" confirm-label="Save changes">
                        <x-slot:trigger>Save changes</x-slot:trigger>
                    </x-confirm-submit>
                @else
                    <button type="submit" class="btn-primary" :disabled="submitting">Register employee</button>
                @endif
            </div>
        </div>
    </form>
</x-layouts.admin>
