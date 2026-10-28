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

    public function test_old_course_detail_links_redirect_to_the_admission_enquiry(): void
    {
        $course = Course::factory()->create();

        $this->get('/courses/'.$course->slug)
            ->assertMovedPermanently()
            ->assertRedirect(route('admissions.create', ['course' => $course->slug]));
    }

    public function test_catalogue_can_be_searched_and_filtered_by_category(): void
    {
        $design = CourseCategory::factory()->create(['name' => 'Design', 'slug' => 'design']);
        Course::factory()->create(['title' => 'Logo Design', 'category_id' => $design->id]);
        Course::factory()->create(['title' => 'Python Basics']);

        $this->get(route('courses.index', ['q' => 'Python']))->assertSee('Python Basics')->assertDontSee('Logo Design');
        $this->get(route('courses.index', ['category' => 'design']))->assertSee('Logo Design')->assertDontSee('Python Basics');
    }

    public function test_course_cards_link_to_the_admission_enquiry_without_showing_fees(): void
    {
        $course = Course::factory()->create(['fee' => 4999]);

        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Enquire Now')
            ->assertSee(route('admissions.create', ['course' => $course->slug]), false)
            ->assertDontSee('4,999');
    }

    public function test_admissions_page_preselects_the_course_and_omits_faq_and_process(): void
    {
        $course = Course::factory()->create();

        $this->get(route('admissions.create', ['course' => $course->slug]))
            ->assertOk()
            ->assertSee('value="'.$course->id.'" selected', false)
            ->assertDontSee('How admission works')
            ->assertDontSee('Admission <em>questions</em>', false);
    }

    public function test_old_admissions_url_redirects_to_enquire_now(): void
    {
        $this->get('/admissions?course=web-design')
            ->assertMovedPermanently()
            ->assertRedirect(route('admissions.create', ['course' => 'web-design']));

        $this->get(route('admissions.create'))->assertOk()->assertSee('Enquire Now');
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
