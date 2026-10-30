<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StudentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentRequest;
use App\Models\AdmissionEnquiry;
use App\Models\Student;
use App\Services\StudentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StudentController extends Controller
{
    public function __construct(private StudentService $students) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(StudentStatus::class)],
        ]);

        $students = Student::query()
            ->withCount(['enrollments', 'certificates', 'orders'])
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'filters'));
    }

    public function create(Request $request): View
    {
        $enquiry = $request->filled('enquiry')
            ? AdmissionEnquiry::query()->whereNull('student_id')->find($request->integer('enquiry'))
            : null;

        $student = new Student([
            'full_name' => $enquiry?->name,
            'email' => $enquiry?->email,
            'phone' => $enquiry?->phone,
            'admission_date' => now(),
            'status' => StudentStatus::Active,
        ]);

        return view('admin.students.form', compact('student', 'enquiry'));
    }

    public function store(StudentRequest $request): RedirectResponse
    {
        $enquiry = $request->filled('enquiry_id') ? AdmissionEnquiry::query()->find($request->integer('enquiry_id')) : null;

        $student = $this->students->create($request->studentAttributes(), $request->file('photo'), $enquiry);

        return redirect()->route('admin.students.show', $student)
            ->with('success', "Student registered with number {$student->registration_number}.");
    }

    public function show(Student $student): View
    {
        $student->load([
            'user',
            'enrollments' => fn ($query) => $query->with(['course', 'validCertificate'])->latest('enrollment_date'),
            'certificates' => fn ($query) => $query->latest('issue_date'),
        ])->loadCount('orders');

        return view('admin.students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', ['student' => $student, 'enquiry' => null]);
    }

    public function update(StudentRequest $request, Student $student): RedirectResponse
    {
        $this->students->update($student, $request->studentAttributes(), $request->file('photo'), $request->boolean('remove_photo'));

        return redirect()->route('admin.students.show', $student)->with('success', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        try {
            $this->students->delete($student);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('admin.students.index')->with('success', 'Student deleted.');
    }

    public function invite(Student $student): RedirectResponse
    {
        try {
            $this->students->invite($student);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return back()->with('success', "Portal invitation sent to {$student->email}.");
    }
}
