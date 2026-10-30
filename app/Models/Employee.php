<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'full_name', 'father_name', 'mother_name', 'date_of_birth', 'address',
    'position', 'joining_date', 'status', 'bio',
])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => EmployeeStatus::class,
            'date_of_birth' => 'date',
            'joining_date' => 'date',
        ];
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : null);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function initials(): Attribute
    {
        return Attribute::get(function (): string {
            $parts = preg_split('/\s+/', trim($this->full_name)) ?: [];

            return strtoupper(mb_substr($parts[0] ?? '', 0, 1).mb_substr(count($parts) > 1 ? end($parts) : '', 0, 1));
        });
    }
}
