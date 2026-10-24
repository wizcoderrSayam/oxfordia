<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CourseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CourseRequest;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Services\ActivityLogger;
use App\Services\CourseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CourseController extends Controller
{
    public function __construct(private CourseService $courses) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer'],
            'status' => ['nullable', Rule::enum(CourseStatus::class)],
        ]);

        $courses = Course::query()
            ->with('category')
            ->withCount(['enrollments', 'modules'])
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('course_code', 'like', "%{$search}%"));
            })
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category_id', $category))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = CourseCategory::query()->orderBy('name')->pluck('name', 'id');

        return view('admin.courses.index', compact('courses', 'categories', 'filters'));
    }

    public function create(): View
    {
        return view('admin.courses.form', [
            'course' => new Course(['status' => CourseStatus::Draft]),
            'categories' => CourseCategory::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function store(CourseRequest $request): RedirectResponse
    {
        $course = $this->courses->create($request->validated(), $request->file('image'), $request->file('syllabus'));

        return redirect()->route('admin.courses.show', $course)->with('success', 'Course created. Add assessment modules below.');
    }

    public function show(Course $course): View
    {
        $course->load(['category', 'modules' => fn ($query) => $query->withCount('marks')]);

        $enrollments = $course->enrollments()
            ->with(['student', 'validCertificate'])
            ->latest('enrollment_date')
            ->paginate(15);

        return view('admin.courses.show', compact('course', 'enrollments'));
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.form', [
            'course' => $course,
            'categories' => CourseCategory::query()->orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(CourseRequest $request, Course $course): RedirectResponse
    {
        $this->courses->update($course, $request->validated(), $request->file('image'), $request->file('syllabus'));

        return redirect()->route('admin.courses.show', $course)->with('success', 'Course updated.');
    }

    public function updateStatus(Request $request, Course $course, ActivityLogger $logger): RedirectResponse
    {
        $status = CourseStatus::from($request->validate(['status' => ['required', Rule::enum(CourseStatus::class)]])['status']);

        $course->update(['status' => $status]);
        $logger->log('course.status_changed', $course, "Course {$course->course_code} set to {$status->label()}");

        return back()->with('success', "Course marked as {$status->label()}.");
    }

    public function destroy(Course $course): RedirectResponse
    {
        try {
            $this->courses->delete($course);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted.');
    }
}
