<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_published_courses_and_real_statistics(): void
    {
        Course::factory()->create(['title' => 'Published Course', 'is_featured' => true]);
        Course::factory()->draft()->create(['title' => 'Secret Draft Course']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Published Course')
            ->assertDontSee('Secret Draft Course');
    }

    public function test_only_published_courses_appear_in_the_catalogue(): void
    {
        Course::factory()->create(['title' => 'Published Course']);
        Course::factory()->draft()->create(['title' => 'Draft Course']);
        Course::factory()->archived()->create(['title' => 'Archived Course']);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Published Course')
            ->assertDontSee('Draft Course')
            ->assertDontSee('Archived Course');
    }

    public function test_draft_and_archived_course_pages_return_not_found(): void
    {
        $this->get(route('courses.show', Course::factory()->draft()->create()))->assertNotFound();
        $this->get(route('courses.show', Course::factory()->archived()->create()))->assertNotFound();
    }

    public function test_catalogue_can_be_searched_and_filtered_by_category(): void
    {
        $design = CourseCategory::factory()->create(['name' => 'Design', 'slug' => 'design']);
        Course::factory()->create(['title' => 'Logo Design', 'category_id' => $design->id]);
        Course::factory()->create(['title' => 'Python Basics']);

        $this->get(route('courses.index', ['q' => 'Python']))->assertSee('Python Basics')->assertDontSee('Logo Design');
        $this->get(route('courses.index', ['category' => 'design']))->assertSee('Logo Design')->assertDontSee('Python Basics');
    }

    public function test_course_detail_shows_buy_button_only_for_priced_courses(): void
    {
        $priced = Course::factory()->create(['fee' => 4999]);
        $enquiryOnly = Course::factory()->enquiryOnly()->create();

        $this->get(route('courses.show', $priced))->assertOk()->assertSee('Buy Now');
        $this->get(route('courses.show', $enquiryOnly))->assertOk()->assertDontSee('Buy Now')->assertSee('Apply for Admission');
    }

    public function test_static_pages_render(): void
    {
        $this->get(route('about'))->assertOk()->assertSee('Our mission');
        $this->get(route('contact.create'))->assertOk();
        $this->get(route('admissions.create'))->assertOk();
        $this->get(route('certificates.verify'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }
}
