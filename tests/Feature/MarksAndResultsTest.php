<?php

namespace Tests\Feature;

use App\Enums\ResultStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MarksAndResultsTest extends TestCase
{
    use RefreshDatabase;

    private Course $course;

    private Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->staff()->create());
        $this->course = Course::factory()->withModules(2, 100, 40)->create();
        $this->enrollment = Enrollment::factory()->create(['course_id' => $this->course->id]);
    }

    /**
     * @param  list<int|string|null>  $values
     */
    private function saveMarks(array $values): TestResponse
    {
        $moduleIds = $this->course->modules()->pluck('id')->all();

        return $this->put(route('admin.courses.marks.update', $this->course), [
            'marks' => [$this->enrollment->id => array_combine($moduleIds, $values)],
        ]);
    }

    public function test_marks_grid_renders(): void
    {
        $this->get(route('admin.courses.marks.edit', $this->course))
            ->assertOk()
            ->assertSee($this->enrollment->student->full_name);
    }

    public function test_complete_marks_produce_a_grade_from_the_bands(): void
    {
        $this->saveMarks([95, 85])->assertSessionHasNoErrors();

        $this->enrollment->refresh();
        $this->assertSame(ResultStatus::Pass, $this->enrollment->result);
        $this->assertSame('A+', $this->enrollment->final_grade);
        $this->assertEquals(90.0, (float) $this->enrollment->percentage);
    }

    public function test_failing_any_module_fails_the_course(): void
    {
        $this->saveMarks([100, 39]);

        $this->enrollment->refresh();
        $this->assertSame(ResultStatus::Fail, $this->enrollment->result);
        $this->assertSame('Fail', $this->enrollment->final_grade);
    }

    public function test_percentage_below_the_lowest_band_fails(): void
    {
        $this->saveMarks([45, 45]);

        $this->assertSame(ResultStatus::Fail, $this->enrollment->fresh()->result);
    }

    public function test_incomplete_marks_leave_the_result_pending(): void
    {
        $this->saveMarks([80, null]);

        $this->assertNull($this->enrollment->fresh()->result);
    }

    public function test_marks_outside_the_allowed_range_are_rejected(): void
    {
        $this->saveMarks([101, -1])->assertSessionHasErrors();

        $this->assertDatabaseCount('enrollment_marks', 0);
    }

    public function test_marks_are_locked_while_a_valid_certificate_exists(): void
    {
        Notification::fake();
        $this->saveMarks([95, 85]);
        $this->enrollment->update(['status' => 'completed', 'completion_date' => now()]);
        app(CertificateService::class)->issue($this->enrollment->fresh(), User::factory()->admin()->create());

        $this->saveMarks([10, 10]);

        $this->assertSame('A+', $this->enrollment->fresh()->final_grade);
    }

    public function test_module_marks_stay_editable_after_marks_are_entered(): void
    {
        $module = $this->course->modules()->first();
        $this->saveMarks([70, 70]);
        $marks = ['theory_max_marks' => 100, 'practical_max_marks' => 0, 'theory_pass_marks' => 75, 'practical_pass_marks' => 0];

        $this->put(route('admin.modules.update', $module), ['name' => 'Renamed'] + $marks)->assertSessionHasNoErrors();

        $module->refresh();
        $this->assertSame('Renamed', $module->name);
        $this->assertSame(75, $module->theory_pass_marks);
        $this->assertSame(ResultStatus::Fail, $this->enrollment->fresh()->result);
    }

    public function test_a_max_can_be_lowered_after_marks_are_entered(): void
    {
        $module = $this->course->modules()->first();
        $this->saveMarks([70, 70]);

        $this->put(route('admin.modules.update', $module), ['name' => 'Renamed', 'theory_max_marks' => 60, 'practical_max_marks' => 0, 'theory_pass_marks' => 10, 'practical_pass_marks' => 0])
            ->assertSessionHasNoErrors();

        $this->assertSame(60, $module->fresh()->max_marks);
    }

    public function test_a_module_with_marks_cannot_be_deleted(): void
    {
        $module = $this->course->modules()->first();
        $this->saveMarks([70, 70]);

        $this->delete(route('admin.modules.destroy', $module))->assertSessionHas('error');
        $this->assertModelExists($module);
    }

    public function test_a_module_can_be_added_with_theory_and_practical_marks(): void
    {
        $this->post(route('admin.courses.modules.store', $this->course), [
            'name' => 'Office Tools',
            'theory_max_marks' => 70,
            'practical_max_marks' => 30,
            'theory_pass_marks' => 28,
            'practical_pass_marks' => 12,
        ])->assertSessionHasNoErrors();

        $module = $this->course->modules()->where('name', 'Office Tools')->firstOrFail();
        $this->assertSame(100, $module->max_marks);
        $this->assertSame(40, $module->pass_marks);
    }

    public function test_the_course_edit_page_lists_and_manages_its_modules(): void
    {
        $module = $this->course->modules()->firstOrFail();

        $this->get(route('admin.courses.edit', $this->course))
            ->assertOk()
            ->assertSee('Assessment modules')
            ->assertSee($module->name)
            ->assertSee(route('admin.modules.update', $module), false)
            ->assertSee(route('admin.courses.modules.store', $this->course), false)
            ->assertSee(route('admin.courses.marks.edit', $this->course), false);
    }

    public function test_module_changes_return_to_the_page_they_were_made_from(): void
    {
        $module = $this->course->modules()->firstOrFail();
        $marks = ['theory_max_marks' => 70, 'practical_max_marks' => 30, 'theory_pass_marks' => 28, 'practical_pass_marks' => 12];

        $this->from(route('admin.courses.edit', $this->course))
            ->put(route('admin.modules.update', $module), ['name' => 'Renamed'] + $marks)
            ->assertRedirect(route('admin.courses.edit', $this->course).'#modules');

        $this->from(route('admin.courses.show', $this->course))
            ->put(route('admin.modules.update', $module), ['name' => 'Renamed again'] + $marks)
            ->assertRedirect(route('admin.courses.show', $this->course).'#modules');
    }

    public function test_a_pass_mark_cannot_exceed_its_max(): void
    {
        $this->post(route('admin.courses.modules.store', $this->course), [
            'name' => 'Office Tools',
            'theory_max_marks' => 70,
            'practical_max_marks' => 30,
            'theory_pass_marks' => 28,
            'practical_pass_marks' => 31,
        ])->assertSessionHasErrors('practical_pass_marks', errorBag: 'module');
    }

    public function test_theory_and_practical_marks_are_saved_and_totalled(): void
    {
        $course = Course::factory()->withTheoryAndPracticalModules()->create();
        $enrollment = Enrollment::factory()->create(['course_id' => $course->id]);
        $module = $course->modules()->firstOrFail();

        $this->put(route('admin.courses.marks.update', $course), [
            'marks' => [$enrollment->id => [$module->id => ['theory' => 50, 'practical' => 26]]],
        ])->assertSessionHasNoErrors();

        $mark = $enrollment->marks()->firstOrFail();
        $this->assertEquals(50, (float) $mark->theory_marks);
        $this->assertEquals(26, (float) $mark->practical_marks);
        $this->assertEquals(76, (float) $mark->marks);

        $enrollment->refresh();
        $this->assertSame(ResultStatus::Pass, $enrollment->result);
        $this->assertEquals(76.0, (float) $enrollment->percentage);
    }

    public function test_missing_the_practical_pass_mark_fails_the_module(): void
    {
        $course = Course::factory()->withTheoryAndPracticalModules()->create();
        $enrollment = Enrollment::factory()->create(['course_id' => $course->id]);
        $module = $course->modules()->firstOrFail();

        $this->put(route('admin.courses.marks.update', $course), [
            'marks' => [$enrollment->id => [$module->id => ['theory' => 70, 'practical' => 11]]],
        ]);

        $this->assertSame(ResultStatus::Fail, $enrollment->fresh()->result);
    }

    public function test_theory_and_practical_marks_must_both_be_entered_and_in_range(): void
    {
        $course = Course::factory()->withTheoryAndPracticalModules()->create();
        $enrollment = Enrollment::factory()->create(['course_id' => $course->id]);
        $module = $course->modules()->firstOrFail();
        $save = fn (array $parts) => $this->put(route('admin.courses.marks.update', $course), [
            'marks' => [$enrollment->id => [$module->id => $parts]],
        ]);

        $save(['theory' => 50, 'practical' => ''])->assertSessionHasErrors("marks.{$enrollment->id}.{$module->id}");
        $save(['theory' => 50, 'practical' => 31])->assertSessionHasErrors("marks.{$enrollment->id}.{$module->id}");

        $this->assertDatabaseCount('enrollment_marks', 0);
    }
}
