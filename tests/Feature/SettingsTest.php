<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\InstituteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Oxfordia Academy',
            'address' => '1 Main Road',
            'phone' => '+91 90000 00000',
            'email' => 'hello@oxfordia.test',
            'signatory_1_name' => 'Dr. Director',
            'signatory_1_designation' => 'Director',
            'signatory_2_name' => 'Ms. Coordinator',
            'signatory_2_designation' => 'Coordinator',
            'certificate_title' => 'Certificate of Achievement',
        ], $overrides);
    }

    public function test_admin_can_update_branding(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.update'), $this->payload([
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ]))->assertRedirect(route('admin.settings.edit'));

        $settings = app(InstituteSettings::class);
        $settings->flush();

        $this->assertSame('Oxfordia Academy', $settings->get('name'));
        Storage::disk('public')->assertExists($settings->get('logo_path'));

        $this->get('/')->assertSee('Oxfordia Academy');
    }

    public function test_every_page_shows_the_contact_details_from_settings(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.update'), $this->payload([
            'phone_alt' => '+91 91111 11111',
        ]))->assertRedirect(route('admin.settings.edit'));

        app(InstituteSettings::class)->flush();

        foreach (['/', route('about')] as $page) {
            $this->get($page)
                ->assertOk()
                ->assertSee('1 Main Road')
                ->assertSee('href="tel:+919000000000"', false)
                ->assertSee('href="tel:+919111111111"', false)
                ->assertSee('hello@oxfordia.test')
                ->assertSee('https://wa.me/919000000000', false)
                ->assertDontSee('98555-62888')
                ->assertDontSee('oxfordiaedu@gmail.com');
        }
    }

    public function test_settings_are_validated(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.update'), $this->payload([
            'email' => 'invalid',
            'map_embed_url' => 'https://evil.example.com/frame',
        ]))->assertSessionHasErrors(['email', 'map_embed_url']);
    }
}
