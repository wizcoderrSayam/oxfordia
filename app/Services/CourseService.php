<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseService
{
    public function __construct(private ActivityLogger $logger) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?UploadedFile $image = null, ?UploadedFile $syllabus = null): Course
    {
        $course = new Course($this->attributes($attributes));
        $course->slug = $this->uniqueSlug($attributes['slug'] ?? null ?: $attributes['title']);

        if ($image) {
            $course->image_path = $image->store('courses', 'public');
        }

        if ($syllabus) {
            $course->syllabus_path = $syllabus->store('syllabi', 'public');
        }

        $course->save();

        $this->logger->log('course.created', $course, "Created course {$course->course_code}");

        return $course;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Course $course, array $attributes, ?UploadedFile $image = null, ?UploadedFile $syllabus = null): Course
    {
        $oldImage = $course->image_path;
        $oldSyllabus = $course->syllabus_path;

        $course->fill($this->attributes($attributes));

        if (! empty($attributes['slug']) && $attributes['slug'] !== $course->getOriginal('slug')) {
            $course->slug = $this->uniqueSlug($attributes['slug'], $course->id);
        }

        if ($image) {
            $course->image_path = $image->store('courses', 'public');
        } elseif (! empty($attributes['remove_image'])) {
            $course->image_path = null;
        }

        if ($syllabus) {
            $course->syllabus_path = $syllabus->store('syllabi', 'public');
        } elseif (! empty($attributes['remove_syllabus'])) {
            $course->syllabus_path = null;
        }

        $course->save();

        foreach ([$oldImage => $course->image_path, $oldSyllabus => $course->syllabus_path] as $old => $current) {
            if ($old && $old !== $current) {
                Storage::disk('public')->delete($old);
            }
        }

        $changed = $this->logger->changedFields($course);

        if ($changed) {
            $this->logger->log('course.updated', $course, "Updated course {$course->course_code}", ['fields' => $changed]);
        }

        return $course;
    }

    /**
     * Hard delete a course that has no history.
     *
     * @throws ValidationException
     */
    public function delete(Course $course): void
    {
        if ($course->hasHistory()) {
            throw ValidationException::withMessages([
                'course' => 'This course has enrollments, orders or certificates and cannot be deleted. Archive it instead.',
            ]);
        }

        $files = array_filter([$course->image_path, $course->syllabus_path]);

        DB::transaction(function () use ($course) {
            $this->logger->log('course.deleted', null, "Deleted course {$course->course_code}");
            $course->delete();
        });

        Storage::disk('public')->delete($files);
    }

    public function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'course';
        $slug = $base;
        $suffix = 2;

        while (Course::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function attributes(array $attributes): array
    {
        return collect($attributes)
            ->only(['category_id', 'title', 'course_code', 'short_description', 'description', 'duration_value', 'duration_unit', 'fee', 'is_featured', 'status'])
            ->all();
    }
}
