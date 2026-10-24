<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Notifications\EnrollmentConfirmed;
use App\Services\EnrollmentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EnrollmentController extends Controller
{
    public function __construct(private EnrollmentService $enrollments) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'course' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(EnrollmentStatus::class)],
        ]);

        $enrollments = Enrollment::query()
            ->with(['student', 'course', 'validCertificate'])
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('enrollment_number', 'like', "%{$search}%")
                        ->orWhereHas('student', fn ($q) => $q->where('full_name', 'like', "%{$search}%")->orWhere('registration_number', 'like', "%{$search}%"));
                });
            })
            ->when($filters['course'] ?? null, fn ($query, $course) => $query->where('course_id', $course))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $courses = Course::query()->orderBy('title')->pluck('title', 'id');

        return view('admin.enrollments.index', compact('enrollments', 'courses', 'filters'));
    }

    public function create(Request $request): View
    {
        return view('admin.enrollments.create', [
            'students' => Student::query()->orderBy('full_name')->get(['id', 'full_name', 'registration_number'])
                ->mapWithKeys(fn (Student $student) => [$student->id => "{$student->full_name} ({$student->registration_number})"]),
            'courses' => Course::query()->where('status', '!=', CourseStatus::Archived)->orderBy('title')->get(['id', 'title', 'course_code'])
                ->mapWithKeys(fn (Course $course) => [$course->id => "{$course->title} ({$course->course_code})"]),
            'selectedStudent' => $request->integer('student') ?: null,
            'selectedCourse' => $request->integer('course') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', Rule::exists('students', 'id')],
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')->whereNot('status', CourseStatus::Archived->value)],
            'enrollment_date' => ['required', 'date'],
            'start_date' => ['nullable', 'date'],
            'expected_completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $enrollment = $this->enrollments->enroll(
            Student::query()->findOrFail($validated['student_id']),
            Course::query()->findOrFail($validated['course_id']),
            collect($validated)->except(['student_id', 'course_id'])->all(),
        );

        $enrollment->student->notifyByMail(new EnrollmentConfirmed($enrollment));

        return redirect()->route('admin.enrollments.show', $enrollment)
            ->with('success', "Enrollment {$enrollment->enrollment_number} created.");
    }

    public function show(Enrollment $enrollment): View
    {
        $enrollment->load(['student', 'course.modules', 'marks', 'certificates.issuer', 'validCertificate']);
        $marks = $enrollment->marks->keyBy('course_module_id');

        return view('admin.enrollments.show', compact('enrollment', 'marks'));
    }

    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(EnrollmentStatus::class)],
            'enrollment_date' => ['required', 'date'],
            'start_date' => ['nullable', 'date'],
            'expected_completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'completion_date' => ['nullable', 'date', 'after_or_equal:enrollment_date', 'before_or_equal:today'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->enrollments->update($enrollment, $validated);

        return redirect()->route('admin.enrollments.show', $enrollment)->with('success', 'Enrollment updated.');
    }
}
