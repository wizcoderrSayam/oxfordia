<?php

namespace App\Http\Requests;

use App\Enums\CourseStatus;
use App\Models\Course;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccessBackOffice();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'course_code' => Str::upper(trim((string) $this->input('course_code'))),
            'slug' => $this->filled('slug') ? Str::slug((string) $this->input('slug')) : null,
            'is_featured' => $this->boolean('is_featured'),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Course|null $course */
        $course = $this->route('course');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('courses', 'slug')->ignore($course)],
            'course_code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9\-]+$/', Rule::unique('courses', 'course_code')->ignore($course)],
            'category_id' => ['nullable', 'integer', Rule::exists('course_categories', 'id')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:20000'],
            'duration_value' => ['nullable', 'integer', 'min:1', 'max:1000', 'required_with:duration_unit'],
            'duration_unit' => ['nullable', Rule::in(Course::DURATION_UNITS), 'required_with:duration_value'],
            'fee' => ['nullable', 'numeric', 'min:0', 'max:9999999.99'],
            'status' => ['required', Rule::enum(CourseStatus::class)],
            'is_featured' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=400,min_height=250,max_width=6000,max_height=6000'],
            'remove_image' => ['nullable', 'boolean'],
            'syllabus' => ['nullable', 'file', 'mimes:pdf', 'max:5120'],
            'remove_syllabus' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'course_code.regex' => 'The course code may only contain letters, numbers and hyphens.',
            'image.dimensions' => 'The image must be at least 400×250 pixels.',
        ];
    }
}
