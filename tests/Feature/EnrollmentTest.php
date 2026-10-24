<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->staff()->create());
    }

    private function enroll(Student $student, Course $course): TestResponse
    {
        return $this->post(route('admin.enrollments.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_date' => now()->toDateString(),
        ]);
    }

    public function test_student_can_be_enrolled_in_a_course(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $this->enroll($student, $course)->assertRedirect();

        $enrollment = Enrollment::query()->firstOrFail();
        $this->assertSame(EnrollmentStatus::Enrolled, $enrollment->status);
        $this->assertSame('ENR-'.now()->year.'-00001', $enrollment->enrollment_number);
    }

    public function test_duplicate_active_enrollment_is_prevented(): void
    {
        $student = Student::factory()->create();
        $course = Course::factory()->create();

        $this->enroll($student, $course);
        $this->enroll($student, $course)->assertSessionHasErrors('course_id');

        $this->assertSame(1, Enrollment::query()->count());
    }

    public function test_passed_enrollment_blocks_re_enrollment(): void
    {
        $enrollment = Enrollment::factory()->passed()->create();

        $this->enroll($enrollment->student, $enrollment->course)->assertSessionHasErrors('course_id');
    }

    public function test_re_enrollment_is_allowed_after_withdrawal_or_failure(): void
    {
        $withdrawn = Enrollment::factory()->withdrawn()->create();
        $failed = Enrollment::factory()->failed()->create();

        $this->enroll($withdrawn->student, $withdrawn->course)->assertSessionHasNoErrors();
        $this->enroll($failed->student, $failed->course)->assertSessionHasNoErrors();

        $this->assertSame(4, Enrollment::query()->count());
    }

    public function test_archived_courses_cannot_receive_new_enrollments(): void
    {
        $this->enroll(Student::factory()->create(), Course::factory()->archived()->create())
            ->assertSessionHasErrors('course_id');
    }

    public function test_marking_an_enrollment_completed_records_the_completion_date(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->put(route('admin.enrollments.update', $enrollment), [
            'status' => 'completed',
            'enrollment_date' => $enrollment->enrollment_date->toDateString(),
        ])->assertRedirect();

        $enrollment->refresh();
        $this->assertSame(EnrollmentStatus::Completed, $enrollment->status);
        $this->assertTrue($enrollment->completion_date->isToday());
    }

    public function test_enrolled_students_are_listed_on_the_admin_course_page(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->get(route('admin.courses.show', $enrollment->course))
            ->assertOk()
            ->assertSee($enrollment->student->full_name);
    }
}
