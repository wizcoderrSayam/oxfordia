<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmployeeStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function __construct(private EmployeeService $employees) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(EmployeeStatus::class)],
        ]);

        $employees = Employee::query()
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.employees.index', compact('employees', 'filters'));
    }

    public function create(): View
    {
        $employee = new Employee([
            'joining_date' => now(),
            'status' => EmployeeStatus::Active,
        ]);

        return view('admin.employees.form', compact('employee'));
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $employee = $this->employees->create($request->employeeAttributes(), $request->file('photo'));

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', "Employee registered with number {$employee->registration_number}.");
    }

    public function show(Employee $employee): View
    {
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        return view('admin.employees.form', compact('employee'));
    }

    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->employees->update($employee, $request->employeeAttributes(), $request->file('photo'), $request->boolean('remove_photo'));

        return redirect()->route('admin.employees.show', $employee)->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->employees->delete($employee);

        return redirect()->route('admin.employees.index')->with('success', 'Employee deleted.');
    }
}
