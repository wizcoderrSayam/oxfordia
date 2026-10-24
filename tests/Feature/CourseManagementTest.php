<?php

namespace Tests\Feature;

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->staff()->create());
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Web Development Bootcamp',
            'course_code' => 'web-101',
            'category_id' => CourseCategory::factory()->create()->id,
            'short_description' => 'Learn to build websites.',
            'description' => 'Full description.',
            'duration_value' => 3,
            'duration_unit' => 'months',
            'fee' => '9999.00',
            'status' => CourseStatus::Published->value,
        ], $overrides);
    }

    public function test_course_can_be_created_with_an_image(): void
    {
        $response = $this->post(route('admin.courses.store'), $this->payload([
            'image' => UploadedFile::fake()->image('cover.jpg', 800, 500),
        ]));

        $course = Course::query()->firstOrFail();
        $response->assertRedirect(route('admin.courses.show', $course));

        $this->assertSame('WEB-101', $course->course_code);
        $this->assertSame('web-development-bootcamp', $course->slug);
        Storage::disk('public')->assertExists($course->image_path);
    }

    public function test_slugs_are_made_unique_automatically(): void
    {
        $this->post(route('admin.courses.store'), $this->payload());
        $this->post(route('admin.courses.store'), $this->payload(['course_code' => 'WEB-102']));

        $this->assertEqualsCanonicalizing(
            ['web-development-bootcamp', 'web-development-bootcamp-2'],
            Course::query()->pluck('slug')->all(),
        );
    }

    public function test_course_code_must_be_unique(): void
    {
        Course::factory()->create(['course_code' => 'WEB-101']);

        $this->post(route('admin.courses.store'), $this->payload())->assertSessionHasErrors('course_code');
    }

    public function test_invalid_uploads_are_rejected(): void
    {
        $this->post(route('admin.courses.store'), $this->payload([
            'image' => UploadedFile::fake()->create('malware.php', 10, 'application/x-php'),
            'syllabus' => UploadedFile::fake()->create('syllabus.exe', 10),
        ]))->assertSessionHasErrors(['image', 'syllabus']);

        $this->assertDatabaseCount('courses', 0);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->post(route('admin.courses.store'), ['status' => 'bogus'])
            ->assertSessionHasErrors(['title', 'course_code', 'status']);
    }

    public function test_replacing_the_image_deletes_the_old_file(): void
    {
        $this->post(route('admin.courses.store'), $this->payload(['image' => UploadedFile::fake()->image('a.jpg', 800, 500)]));
        $course = Course::query()->firstOrFail();
        $oldPath = $course->image_path;

        $this->put(route('admin.courses.update', $course), $this->payload([
            'category_id' => $course->category_id,
            'image' => UploadedFile::fake()->image('b.jpg', 800, 500),
        ]))->assertRedirect();

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($course->fresh()->image_path);
    }

    public function test_course_status_can_be_changed(): void
    {
        $course = Course::factory()->draft()->create();

        $this->patch(route('admin.courses.status', $course), ['status' => 'published'])->assertRedirect();
        $this->assertSame(CourseStatus::Published, $course->fresh()->status);

        $this->patch(route('admin.courses.status', $course), ['status' => 'archived'])->assertRedirect();
        $this->assertSame(CourseStatus::Archived, $course->fresh()->status);
    }

    public function test_course_without_history_can_be_deleted(): void
    {
        $course = Course::factory()->withModules()->create();

        $this->delete(route('admin.courses.destroy', $course))->assertRedirect(route('admin.courses.index'));

        $this->assertModelMissing($course);
    }

    public function test_course_with_enrollments_cannot_be_deleted(): void
    {
        $enrollment = Enrollment::factory()->create();

        $this->delete(route('admin.courses.destroy', $enrollment->course))->assertSessionHas('error');

        $this->assertModelExists($enrollment->course);
    }

    public function test_admin_course_list_can_be_searched_and_filtered(): void
    {
        Course::factory()->create(['title' => 'Python Programming', 'course_code' => 'PY-101']);
        Course::factory()->draft()->create(['title' => 'Graphic Design', 'course_code' => 'GD-201']);

        $this->get(route('admin.courses.index', ['q' => 'PY-101']))->assertSee('Python Programming')->assertDontSee('Graphic Design');
        $this->get(route('admin.courses.index', ['status' => 'draft']))->assertSee('Graphic Design')->assertDontSee('Python Programming');
    }
}
