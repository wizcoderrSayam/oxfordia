<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\AdmissionEnquiry;
use App\Models\ContactMessage;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDeskTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_finds_matching_students_courses_and_enrollments(): void
    {
        $student = Student::factory()->create(['full_name' => 'Harpreet Kaur']);
        $course = Course::factory()->create(['title' => 'Harmony in Accounting', 'course_code' => 'HAR-101']);
        $enrollment = Enrollment::factory()->for($student)->for($course)->create();
        Student::factory()->create(['full_name' => 'Gurpreet Singh']);

        $response = $this->actingAs(User::factory()->admin()->create())
            ->getJson(route('admin.search', ['q' => 'Har']))
            ->assertOk();

        $results = collect($response->json('results'));

        $this->assertSame(['Students', 'Courses', 'Enrollments'], $results->pluck('group')->unique()->values()->all());
        $this->assertContains(route('admin.students.show', $student), $results->pluck('url'));
        $this->assertContains(route('admin.courses.show', $course), $results->pluck('url'));
        $this->assertContains(route('admin.enrollments.show', $enrollment), $results->pluck('url'));
        $this->assertNotContains('Gurpreet Singh', $results->pluck('title'));
    }

    public function test_search_ignores_queries_shorter_than_two_characters(): void
    {
        Student::factory()->create(['full_name' => 'Aarav Sharma']);

        $this->actingAs(User::factory()->staff()->create())
            ->getJson(route('admin.search', ['q' => 'A']))
            ->assertOk()
            ->assertExactJson(['results' => []]);
    }

    public function test_search_is_limited_to_back_office_users(): void
    {
        $this->getJson(route('admin.search', ['q' => 'Aarav']))->assertUnauthorized();

        $this->actingAs(User::factory()->create())
            ->getJson(route('admin.search', ['q' => 'Aarav']))
            ->assertForbidden();
    }

    public function test_dashboard_summarises_waiting_work_and_badges_the_sidebar(): void
    {
        AdmissionEnquiry::query()->create(['name' => 'Simran Kaur', 'phone' => '9800000011', 'message' => 'Batch timings?']);
        ContactMessage::query()->create(['name' => 'Rahul Nair', 'email' => 'rahul@example.com', 'subject' => 'Fees', 'message' => 'Please share the fees.']);
        Enrollment::factory()->passed()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('You have 1 new admission enquiry, 1 unread message and 1 certificate ready to issue.')
            ->assertSee('aria-label="Admission enquiries, 1 waiting"', false)
            ->assertSee('aria-label="Contact messages, 1 waiting"', false)
            ->assertSee('aria-label="Certificates, 1 waiting"', false);
    }

    public function test_dashboard_says_when_nothing_is_waiting(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Nothing is waiting for you.')
            ->assertSee('All caught up')
            ->assertDontSee(' waiting"', false);
    }

    public function test_dashboard_counts_enrollments_per_month_including_empty_months(): void
    {
        Enrollment::factory()->count(2)->create(['enrollment_date' => now()->toDateString()]);
        Enrollment::factory()->create(['enrollment_date' => now()->subMonths(2)->toDateString()]);
        Enrollment::factory()->create(['enrollment_date' => now()->subMonths(8)->toDateString()]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('enrollmentTrend', function (array $trend): bool {
                return count($trend) === 6
                    && array_column($trend, 'count') === [0, 0, 0, 1, 0, 2]
                    && end($trend)['name'] === now()->format('F Y');
            });
    }

    public function test_latest_activity_is_shown_to_admins_but_not_staff(): void
    {
        ActivityLog::query()->create(['action' => 'course.created', 'description' => 'Created course TST-101']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertSee('Latest activity')
            ->assertSee('Created course TST-101');

        $this->actingAs(User::factory()->staff()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Latest activity')
            ->assertDontSee('Created course TST-101');
    }
}
