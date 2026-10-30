<?php

namespace App\Services;

use App\Enums\EmployeeStatus;
use App\Models\Employee;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    public function __construct(
        private NumberSequenceService $numbers,
        private ActivityLogger $logger,
    ) {}

    /**
     * Register a new employee.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ?UploadedFile $photo = null): Employee
    {
        return DB::transaction(function () use ($attributes, $photo): Employee {
            $employee = new Employee(array_merge([
                'joining_date' => now()->toDateString(),
                'status' => EmployeeStatus::Active,
            ], $attributes));

            $employee->registration_number = $this->numbers->nextEmployeeNumber();

            if ($photo) {
                $employee->profile_photo_path = $photo->store('employees', 'public');
            }

            $employee->save();

            $this->logger->log('employee.created', $employee, "Registered {$employee->registration_number}");

            return $employee;
        });
    }

    /**
     * Update an employee, replacing or removing the profile photo when requested.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(Employee $employee, array $attributes, ?UploadedFile $photo = null, bool $removePhoto = false): Employee
    {
        $oldPhoto = $employee->profile_photo_path;

        if ($photo) {
            $employee->profile_photo_path = $photo->store('employees', 'public');
        } elseif ($removePhoto) {
            $employee->profile_photo_path = null;
        }

        $employee->fill($attributes)->save();

        if ($oldPhoto && $oldPhoto !== $employee->profile_photo_path) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $changed = $this->logger->changedFields($employee);

        if ($changed) {
            $this->logger->log('employee.updated', $employee, "Updated {$employee->registration_number}", ['fields' => $changed]);
        }

        return $employee;
    }

    /**
     * Delete an employee and their photo.
     */
    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            $this->logger->log('employee.deleted', null, "Deleted {$employee->registration_number}");
            $employee->delete();
        });

        if ($employee->profile_photo_path) {
            Storage::disk('public')->delete($employee->profile_photo_path);
        }
    }

    /**
     * Find an employee by the registration number printed on their ID card and their date of birth.
     */
    public function findForVerification(string $registrationNumber, CarbonInterface $dateOfBirth): ?Employee
    {
        return Employee::query()
            ->where('registration_number', strtoupper(trim($registrationNumber)))
            ->whereDate('date_of_birth', $dateOfBirth->toDateString())
            ->first();
    }
}
