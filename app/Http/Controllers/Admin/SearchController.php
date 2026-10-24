<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Record lookup for the admin command palette.
 */
class SearchController extends Controller
{
    private const RESULTS_PER_GROUP = 5;

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim($validated['q'] ?? '');

        if (mb_strlen($term) < 2) {
            return response()->json(['results' => []]);
        }

        return response()->json([
            'results' => [
                ...$this->students($term),
                ...$this->courses($term),
                ...$this->enrollments($term),
                ...$this->certificates($term),
            ],
        ]);
    }

    /**
     * @return list<array{group: string, title: string, subtitle: string, url: string, icon: string}>
     */
    private function students(string $term): array
    {
        return Student::query()
            ->where(function (Builder $query) use ($term) {
                $query->where('full_name', 'like', "%{$term}%")
                    ->orWhere('registration_number', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            })
            ->orderBy('full_name')
            ->limit(self::RESULTS_PER_GROUP)
            ->get()
            ->map(fn (Student $student): array => [
                'group' => 'Students',
                'title' => $student->full_name,
                'subtitle' => $student->registration_number,
                'url' => route('admin.students.show', $student),
                'icon' => 'users',
            ])
            ->all();
    }

    /**
     * @return list<array{group: string, title: string, subtitle: string, url: string, icon: string}>
     */
    private function courses(string $term): array
    {
        return Course::query()
            ->where(fn (Builder $query) => $query->where('title', 'like', "%{$term}%")->orWhere('course_code', 'like', "%{$term}%"))
            ->orderBy('title')
            ->limit(self::RESULTS_PER_GROUP)
            ->get()
            ->map(fn (Course $course): array => [
                'group' => 'Courses',
                'title' => $course->title,
                'subtitle' => $course->course_code.' · '.$course->status->label(),
                'url' => route('admin.courses.show', $course),
                'icon' => 'book-open',
            ])
            ->all();
    }

    /**
     * @return list<array{group: string, title: string, subtitle: string, url: string, icon: string}>
     */
    private function enrollments(string $term): array
    {
        return Enrollment::query()
            ->with(['student', 'course'])
            ->where(function (Builder $query) use ($term) {
                $query->where('enrollment_number', 'like', "%{$term}%")
                    ->orWhereIn('student_id', Student::query()->select('id')
                        ->where('full_name', 'like', "%{$term}%")
                        ->orWhere('registration_number', 'like', "%{$term}%"));
            })
            ->latest()
            ->limit(self::RESULTS_PER_GROUP)
            ->get()
            ->map(fn (Enrollment $enrollment): array => [
                'group' => 'Enrollments',
                'title' => $enrollment->student->full_name.' — '.$enrollment->course->title,
                'subtitle' => $enrollment->enrollment_number.' · '.$enrollment->status->label(),
                'url' => route('admin.enrollments.show', $enrollment),
                'icon' => 'clipboard-list',
            ])
            ->all();
    }

    /**
     * @return list<array{group: string, title: string, subtitle: string, url: string, icon: string}>
     */
    private function certificates(string $term): array
    {
        return Certificate::query()
            ->where(function (Builder $query) use ($term) {
                $query->where('certificate_number', 'like', "%{$term}%")
                    ->orWhere('student_name_snapshot', 'like', "%{$term}%");
            })
            ->latest()
            ->limit(self::RESULTS_PER_GROUP)
            ->get()
            ->map(fn (Certificate $certificate): array => [
                'group' => 'Certificates',
                'title' => $certificate->certificate_number,
                'subtitle' => $certificate->student_name_snapshot.' · '.$certificate->status->label(),
                'url' => route('admin.certificates.show', $certificate),
                'icon' => 'award',
            ])
            ->all();
    }
}
