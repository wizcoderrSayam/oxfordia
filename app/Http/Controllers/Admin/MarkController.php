<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\InstituteSettings;
use App\Services\MarkService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    public function __construct(private MarkService $marks) {}

    public function edit(Course $course, InstituteSettings $settings): View
    {
        $course->load('modules');
        $enrollments = $this->marks->gridEnrollments($course);

        return view('admin.marks.edit', [
            'course' => $course,
            'enrollments' => $enrollments,
            'bands' => $settings->gradeBands(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $request->validate(['marks' => ['nullable', 'array']]);

        $updated = $this->marks->saveForCourse($course, $request->input('marks', []));

        return redirect()->route('admin.courses.marks.edit', $course)
            ->with('success', $updated === 0 ? 'No changes to save.' : "Marks saved for {$updated} ".str('enrollment')->plural($updated).'.');
    }
}
