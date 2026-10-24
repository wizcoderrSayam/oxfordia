<x-layouts.admin :title="$course->exists ? 'Edit course' : 'New course'">
    <x-page-header :title="$course->exists ? 'Edit '.$course->title : 'New course'" :back="$course->exists ? route('admin.courses.show', $course) : route('admin.courses.index')" />

    <form method="POST" action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-3" x-data="{ submitting: false }" @submit="submitting = true">
        @csrf
        @if ($course->exists) @method('PUT') @endif

        <div class="card space-y-5 p-6 lg:col-span-2">
            <x-input name="title" label="Course title" :value="$course->title" required />
            <div class="grid gap-5 sm:grid-cols-2">
                <x-input name="course_code" label="Course code" :value="$course->course_code" required class="uppercase" help="Letters, numbers and hyphens, e.g. WEB-101." />
                <x-input name="slug" label="URL slug" :value="$course->slug" help="Leave blank to generate from the title." />
            </div>
            <x-select name="category_id" label="Category" :options="$categories" :value="$course->category_id" placeholder="Uncategorised" />
            <x-textarea name="short_description" label="Short description" :value="$course->short_description" rows="2" help="Shown on course cards (max 500 characters)." maxlength="500" />
            <x-textarea name="description" label="Full description" :value="$course->description" rows="10" />
        </div>

        <div class="space-y-6">
            <div class="card space-y-5 p-6">
                <x-select name="status" label="Status" :options="collect(App\Enums\CourseStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])" :value="$course->status" required />
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="is_featured" value="0">
                    <input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-navy-700" @checked(old('is_featured', $course->is_featured))> Feature on the home page
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <x-input name="duration_value" type="number" min="1" label="Duration" :value="$course->duration_value" />
                    <x-select name="duration_unit" label="Unit" :options="array_combine(App\Models\Course::DURATION_UNITS, array_map('ucfirst', App\Models\Course::DURATION_UNITS))" :value="$course->duration_unit" placeholder="—" />
                </div>
                <x-input name="fee" type="number" step="0.01" min="0" label="Fee (₹)" :value="$course->fee" help="Leave blank to show 'Apply for Admission' instead of online purchase." />
            </div>

            <div class="card space-y-5 p-6">
                <div>
                    @if ($course->image_path)
                        <img src="{{ $course->image_url }}" alt="" class="mb-3 aspect-[16/10] w-full rounded-lg object-cover">
                        <label class="mb-3 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300"> Remove current image</label>
                    @endif
                    <x-input name="image" type="file" label="Course image" accept="image/jpeg,image/png,image/webp" help="JPG, PNG or WebP, up to 2 MB, at least 400×250 px." />
                </div>
                <div>
                    @if ($course->syllabus_path)
                        <a href="{{ Storage::disk('public')->url($course->syllabus_path) }}" target="_blank" class="mb-2 inline-flex items-center gap-1 text-sm font-medium text-navy-700 hover:underline"><x-icon name="file-text" /> Current syllabus</a>
                        <label class="mb-3 flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="remove_syllabus" value="1" class="rounded border-slate-300"> Remove current syllabus</label>
                    @endif
                    <x-input name="syllabus" type="file" label="Syllabus (PDF)" accept="application/pdf" help="PDF up to 5 MB." />
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ $course->exists ? route('admin.courses.show', $course) : route('admin.courses.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary" :disabled="submitting">{{ $course->exists ? 'Save changes' : 'Create course' }}</button>
            </div>
        </div>
    </form>
</x-layouts.admin>
