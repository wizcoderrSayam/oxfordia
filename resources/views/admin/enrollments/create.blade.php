<x-layouts.admin title="New enrollment">
    <x-page-header title="Enroll a student" subtitle="An enrollment number is generated automatically." :back="route('admin.enrollments.index')" />

    @if ($students->isEmpty() || $courses->isEmpty())
        <div class="card">
            <x-empty-state icon="info" title="Students and courses are required" message="Register at least one student and create at least one non-archived course first.">
                <div class="flex gap-2">
                    <a href="{{ route('admin.students.create') }}" class="btn-secondary">New student</a>
                    <a href="{{ route('admin.courses.create') }}" class="btn-primary">New course</a>
                </div>
            </x-empty-state>
        </div>
    @else
        <form method="POST" action="{{ route('admin.enrollments.store') }}" class="card max-w-2xl space-y-5 p-6" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            <x-select name="student_id" label="Student" :options="$students" :value="$selectedStudent" placeholder="Select a student" required />
            <x-select name="course_id" label="Course" :options="$courses" :value="$selectedCourse" placeholder="Select a course" required />
            <div class="grid gap-5 sm:grid-cols-3">
                <x-input name="enrollment_date" type="date" label="Enrollment date" :value="now()->toDateString()" required />
                <x-input name="start_date" type="date" label="Course start" />
                <x-input name="expected_completion_date" type="date" label="Expected completion" />
            </div>
            <x-textarea name="remarks" label="Internal remarks" rows="3" />
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.enrollments.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" :disabled="submitting">Create enrollment</button>
            </div>
        </form>
    @endif
</x-layouts.admin>
