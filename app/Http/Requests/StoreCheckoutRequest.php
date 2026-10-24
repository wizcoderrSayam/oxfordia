<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isStudent();
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'date_of_birth' => ['nullable', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::in(array_keys(Student::GENDERS))],
            'address' => ['nullable', 'string', 'max:1000'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Please enter a valid phone number.',
            'terms.accepted' => 'Please confirm your details are correct.',
        ];
    }
}
