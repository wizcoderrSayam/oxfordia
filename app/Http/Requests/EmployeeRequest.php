<?php

namespace App\Http\Requests;

use App\Enums\EmployeeStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canAccessBackOffice();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'address' => ['nullable', 'string', 'max:1000'],
            'position' => ['required', 'string', 'max:255'],
            'joining_date' => ['required', 'date', 'before_or_equal:today', 'after:1900-01-01'],
            'status' => ['required', Rule::enum(EmployeeStatus::class)],
            'bio' => ['nullable', 'string', 'max:5000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024', 'dimensions:max_width=4000,max_height=4000'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Attributes that map directly onto the employee model.
     *
     * @return array<string, mixed>
     */
    public function employeeAttributes(): array
    {
        return $this->safe()->except(['photo', 'remove_photo']);
    }
}
