<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\InstituteSettings;
use App\Services\ResultService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GradingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    /**
     * @param  list<array{grade: string, min: int|float}>  $bands
     */
    private function saveBands(array $bands): TestResponse
    {
        return $this->actingAs($this->admin)->put(route('admin.grading.update'), ['grade_bands' => $bands]);
    }

    public function test_the_grading_page_lists_the_grade_bands(): void
    {
        $this->actingAs($this->admin)->get(route('admin.grading.edit'))
            ->assertOk()
            ->assertSee('Minimum %')
            ->assertSee('Percentage range')
            ->assertSee('\u0022grade\u0022:\u0022A+\u0022', false)
            ->assertSee('Fail');
    }

    public function test_grade_bands_are_saved_highest_first(): void
    {
        $this->saveBands([['grade' => 'B', 'min' => 80], ['grade' => 'A', 'min' => 90], ['grade' => 'C', 'min' => 70]])
            ->assertRedirect(route('admin.grading.edit'))
            ->assertSessionHas('success');

        $settings = app(InstituteSettings::class);
        $settings->flush();

        $this->assertSame(['A', 'B', 'C'], array_column($settings->gradeBands(), 'grade'));
    }

    public function test_saving_re_grades_existing_results_but_not_certified_ones(): void
    {
        Notification::fake();
        $course = Course::factory()->withModules(1, 100, 40)->create();
        $enrollment = Enrollment::factory()->create(['course_id' => $course->id]);
        $enrollment->marks()->create(['course_module_id' => $course->modules()->value('id'), 'marks' => 85]);
        app(ResultService::class)->recalculate($enrollment);
        $this->assertSame('A', $enrollment->fresh()->final_grade);

        $certified = Enrollment::factory()->passed('A')->create();
        app(CertificateService::class)->issue($certified, $this->admin);

        $this->saveBands([['grade' => 'Distinction', 'min' => 80], ['grade' => 'Pass', 'min' => 40]])
            ->assertSessionHas('success', 'Grades saved. 1 existing result re-graded.');

        $this->assertSame('Distinction', $enrollment->fresh()->final_grade);
        $this->assertSame('A', $certified->fresh()->final_grade);
    }

    public function test_grade_bands_are_validated(): void
    {
        $this->saveBands([['grade' => 'A', 'min' => 150]])->assertSessionHasErrors('grade_bands.0.min');
        $this->saveBands([['grade' => 'A', 'min' => 90], ['grade' => 'A', 'min' => 80]])->assertSessionHasErrors('grade_bands.1.grade');
        $this->saveBands([])->assertSessionHasErrors('grade_bands');
    }

    public function test_staff_cannot_change_grading(): void
    {
        $this->actingAs(User::factory()->staff()->create())
            ->get(route('admin.grading.edit'))
            ->assertForbidden();
    }
}
