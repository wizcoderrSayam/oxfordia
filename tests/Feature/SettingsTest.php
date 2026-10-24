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
            'grade_bands' => [['grade' => 'Distinction', 'min' => 80], ['grade' => 'Pass', 'min' => 50]],
        ], $overrides);
    }

    public function test_admin_can_update_branding_and_grade_bands(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.update'), $this->payload([
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ]))->assertRedirect(route('admin.settings.edit'));

        $settings = app(InstituteSettings::class);
        $settings->flush();

        $this->assertSame('Oxfordia Academy', $settings->get('name'));
        $this->assertSame('Distinction', $settings->gradeBands()[0]['grade']);
        Storage::disk('public')->assertExists($settings->get('logo_path'));

        $this->get('/')->assertSee('Oxfordia Academy');
    }

    public function test_settings_are_validated(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->put(route('admin.settings.update'), $this->payload([
            'email' => 'invalid',
            'map_embed_url' => 'https://evil.example.com/frame',
            'grade_bands' => [['grade' => 'A', 'min' => 150]],
        ]))->assertSessionHasErrors(['email', 'map_embed_url', 'grade_bands.0.min']);
    }
}
