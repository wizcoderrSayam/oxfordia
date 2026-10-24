<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseModule;
use App\Services\ActivityLogger;
use App\Services\ResultService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseModuleController extends Controller
{
    public function __construct(
        private ActivityLogger $logger,
        private ResultService $results,
    ) {}

    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $this->validateModule($request);

        DB::transaction(function () use ($course, $validated) {
            $module = $course->modules()->create($validated + [
                'sort_order' => $validated['sort_order'] ?? (int) $course->modules()->max('sort_order') + 1,
            ]);

            $this->results->recalculateCourse($course);
            $this->logger->log('module.created', $module, "Added module {$module->name} to {$course->course_code}");
        });

        $message = $course->enrollments()->whereNotNull('result')->exists()
            ? 'Module added. Results for students without marks in this module are now pending until those marks are entered.'
            : 'Module added.';

        return redirect()->to(route('admin.courses.show', $course).'#modules')->with('success', $message);
    }

    public function update(Request $request, CourseModule $module): RedirectResponse
    {
        $validated = $this->validateModule($request, $module);

        DB::transaction(function () use ($module, $validated) {
            $module->update($validated);

            if ($module->wasChanged(['max_marks', 'pass_marks'])) {
                $this->results->recalculateCourse($module->course);
            }
        });

        return redirect()->to(route('admin.courses.show', $module->course_id).'#modules')->with('success', 'Module updated.');
    }

    public function destroy(CourseModule $module): RedirectResponse
    {
        if ($module->isLocked()) {
            return back()->with('error', 'Marks have been recorded for this module, so it cannot be deleted.');
        }

        $course = $module->course;

        DB::transaction(function () use ($module, $course) {
            $this->logger->log('module.deleted', $course, "Deleted module {$module->name}");
            $module->delete();
            $this->results->recalculateCourse($course);
        });

        return redirect()->to(route('admin.courses.show', $course).'#modules')->with('success', 'Module deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateModule(Request $request, ?CourseModule $module = null): array
    {
        $locked = $module?->isLocked() ?? false;

        $validated = $request->validateWithBag('module', [
            'name' => ['required', 'string', 'max:255'],
            'max_marks' => $locked ? ['prohibited'] : ['required', 'integer', 'min:1', 'max:1000'],
            'pass_marks' => $locked ? ['prohibited'] : ['required', 'integer', 'min:0', 'lte:max_marks'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ], [
            'max_marks.prohibited' => 'Max marks are locked because marks have been recorded.',
            'pass_marks.prohibited' => 'Pass marks are locked because marks have been recorded.',
        ]);

        return array_filter($validated, fn ($value) => $value !== null);
    }
}
