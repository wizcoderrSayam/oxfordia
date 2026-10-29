<?php

namespace App\Http\Requests;

use App\Enums\StudentStatus;
use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccessBackOffice();
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::in(array_keys(Student::GENDERS))],
            'address' => ['nullable', 'string', 'max:1000'],
            'admission_date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::enum(StudentStatus::class)],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:max_width=4000,max_height=4000'],
            'remove_photo' => ['nullable', 'boolean'],
            'enquiry_id' => ['nullable', 'integer', Rule::exists('admission_enquiries', 'id')->whereNull('student_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
        ];
    }

    /**
     * Attributes that map directly onto the student model.
     *
     * @return array<string, mixed>
     */
    public function studentAttributes(): array
    {
        return $this->safe()->except(['photo', 'remove_photo', 'enquiry_id']);
    }
}
