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
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

        return $this->backToModules($course)->with('success', $message);
    }

    public function update(Request $request, CourseModule $module): RedirectResponse
    {
        $validated = $this->validateModule($request, $module);

        DB::transaction(function () use ($module, $validated) {
            $module->update($validated);

            if ($module->wasChanged(['theory_max_marks', 'practical_max_marks', 'theory_pass_marks', 'practical_pass_marks'])) {
                $this->results->recalculateCourse($module->course);
            }
        });

        return $this->backToModules($module->course_id)->with('success', 'Module updated.');
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

        return $this->backToModules($course)->with('success', 'Module deleted.');
    }

    /**
     * Return to the modules section of the page the change was made from: the course edit page or the course page.
     */
    private function backToModules(Course|int $course): RedirectResponse
    {
        $page = Str::before(url()->previous(), '#') === route('admin.courses.edit', $course) ? 'admin.courses.edit' : 'admin.courses.show';

        return redirect()->to(route($page, $course).'#modules');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateModule(Request $request, ?CourseModule $module = null): array
    {
        $validated = $request->validateWithBag('module', [
            'name' => ['required', 'string', 'max:255'],
            'theory_max_marks' => ['required', 'integer', 'min:0', 'max:1000'],
            'practical_max_marks' => ['required', 'integer', 'min:0', 'max:1000'],
            'theory_pass_marks' => ['required', 'integer', 'min:0', 'lte:theory_max_marks'],
            'practical_pass_marks' => ['required', 'integer', 'min:0', 'lte:practical_max_marks'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ], [
            'theory_pass_marks.lte' => 'Theory min marks cannot be more than theory max marks.',
            'practical_pass_marks.lte' => 'Practical min marks cannot be more than practical max marks.',
        ], [
            'theory_pass_marks' => 'theory min marks',
            'practical_pass_marks' => 'practical min marks',
        ]);

        if ((int) $validated['theory_max_marks'] + (int) $validated['practical_max_marks'] < 1) {
            throw ValidationException::withMessages(['theory_max_marks' => 'A module needs at least 1 theory or practical mark.'])->errorBag('module');
        }

        return array_filter($validated, fn ($value) => $value !== null);
    }
}
