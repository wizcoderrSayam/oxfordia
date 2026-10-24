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

    public function test_module_marks_lock_once_marks_exist(): void
    {
        $module = $this->course->modules()->first();
        $this->saveMarks([70, 70]);

        $this->put(route('admin.modules.update', $module), ['name' => 'Renamed', 'max_marks' => 50, 'pass_marks' => 10])
            ->assertSessionHasErrors(['max_marks', 'pass_marks'], errorBag: 'module');

        $this->put(route('admin.modules.update', $module), ['name' => 'Renamed'])->assertSessionHasNoErrors();
        $this->assertSame('Renamed', $module->fresh()->name);
        $this->assertSame(100, $module->fresh()->max_marks);

        $this->delete(route('admin.modules.destroy', $module))->assertSessionHas('error');
        $this->assertModelExists($module);
    }
}
