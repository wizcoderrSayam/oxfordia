<x-layouts.admin :title="$student->exists ? 'Edit student' : 'New student'">
    <x-page-header :title="$student->exists ? 'Edit '.$student->full_name : 'New student'" :subtitle="$student->exists ? 'Registration no. '.$student->registration_number : 'A registration number is generated automatically.'" :back="$student->exists ? route('admin.students.show', $student) : route('admin.students.index')" />

    @if ($enquiry)
        <x-callout class="mb-6">
            Pre-filled from the admission enquiry by <strong>{{ $enquiry->name }}</strong>. Saving will link the enquiry to this student and mark it as converted.
        </x-callout>
    @endif

    <form method="POST" action="{{ $student->exists ? route('admin.students.update', $student) : route('admin.students.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @if ($student->exists) @method('PUT') @endif
        @if ($enquiry) <input type="hidden" name="enquiry_id" value="{{ $enquiry->id }}"> @endif

        <div class="card space-y-5 p-6 lg:col-span-2">
            <x-input name="full_name" label="Full name" :value="$student->full_name" required help="As it should appear on certificates." />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-input name="email" type="email" label="Email" :value="$student->email" help="Required for portal access." />
                <x-input name="phone" type="tel" label="Phone" :value="$student->phone" required />
                <x-input name="date_of_birth" type="date" label="Date of birth" :value="$student->date_of_birth?->toDateString()" />
                <x-select name="gender" label="Gender" :options="App\Models\Student::GENDERS" :value="$student->gender" placeholder="Not specified" />
            </div>
            <x-textarea name="address" label="Address" :value="$student->address" rows="3" />
            <x-textarea name="internal_notes" label="Internal notes" :value="$student->internal_notes" rows="3" help="Visible to staff only. Never shown to the student or the public." />
        </div>

        <div class="space-y-6">
            <div class="card space-y-5 p-6">
                <x-input name="admission_date" type="date" label="Admission date" :value="$student->admission_date?->toDateString()" required />
                <x-select name="status" label="Status" :options="collect(App\Enums\StudentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$student->status" required />
            </div>
            <div class="card space-y-4 p-6">
                @if ($student->photo_url)
                    <img src="{{ $student->photo_url }}" alt="" class="size-24 rounded-full object-cover">
                    <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_photo" value="1" class="rounded border-slate-300"> Remove photo</label>
                @endif
                <x-input name="photo" type="file" label="Profile photo" accept="image/jpeg,image/png,image/webp" help="JPG, PNG or WebP, up to 1 MB." />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ $student->exists ? route('admin.students.show', $student) : route('admin.students.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" :disabled="submitting">{{ $student->exists ? 'Save changes' : 'Register student' }}</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
