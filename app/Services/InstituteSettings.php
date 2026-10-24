<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Editable institute settings, stored as key/value rows and cached.
 */
class InstituteSettings
{
    private const CACHE_KEY = 'institute.settings';

    /**
     * Default values used until an administrator saves the settings page.
     *
     * @var array<string, mixed>
     */
    public const DEFAULTS = [
        'name' => 'Oxfordia Institute',
        'tagline' => 'Career-focused technical training',
        'logo_path' => null,
        'address' => '[Placeholder] 123 Learning Avenue, City, State 000000',
        'phone' => '+91 00000 00000',
        'email' => 'info@oxfordia.example',
        'website' => 'https://oxfordia.example',
        'map_embed_url' => null,
        'office_hours' => 'Mon – Sat, 9:00 AM – 6:00 PM',
        'facebook_url' => null,
        'instagram_url' => null,
        'linkedin_url' => null,
        'youtube_url' => null,
        'signatory_1_name' => '[Placeholder] Director Name',
        'signatory_1_designation' => 'Director',
        'signatory_1_signature_path' => null,
        'signatory_2_name' => '[Placeholder] Coordinator Name',
        'signatory_2_designation' => 'Course Coordinator',
        'signatory_2_signature_path' => null,
        'certificate_title' => 'Certificate of Completion',
        'certificate_footer' => 'This certificate can be verified online by scanning the QR code or visiting our verification page.',
        'accreditation_statement' => null,
        'grade_bands' => [
            ['grade' => 'A+', 'min' => 90],
            ['grade' => 'A', 'min' => 75],
            ['grade' => 'B', 'min' => 60],
            ['grade' => 'C', 'min' => 50],
        ],
        'mail_mailer' => null,
        'mail_host' => null,
        'mail_port' => null,
        'mail_encryption' => null,
        'mail_username' => null,
        'mail_password' => null,
        'mail_from_address' => null,
        'mail_from_name' => null,
        'mail_auto_replies' => '1',
    ];

    /**
     * Keys whose values are stored as JSON.
     */
    private const JSON_KEYS = ['grade_bands'];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $resolved = null;

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->resolved ??= array_merge(self::DEFAULTS, Cache::rememberForever(self::CACHE_KEY, function (): array {
            return Setting::query()->pluck('value', 'key')
                ->map(fn (?string $value, string $key) => in_array($key, self::JSON_KEYS, true) ? json_decode((string) $value, true) : $value)
                ->all();
        }));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Persist the given settings and clear the cache.
     *
     * @param  array<string, mixed>  $values
     */
    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! array_key_exists($key, self::DEFAULTS)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => in_array($key, self::JSON_KEYS, true) ? json_encode($value) : $value],
            );
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->resolved = null;
    }

    /**
     * Grade bands ordered from highest minimum percentage to lowest.
     *
     * @return list<array{grade: string, min: float}>
     */
    public function gradeBands(): array
    {
        $bands = collect($this->get('grade_bands', self::DEFAULTS['grade_bands']))
            ->filter(fn ($band) => is_array($band) && filled($band['grade'] ?? null) && is_numeric($band['min'] ?? null))
            ->map(fn (array $band) => ['grade' => (string) $band['grade'], 'min' => (float) $band['min']])
            ->sortByDesc('min')
            ->values()
            ->all();

        return $bands;
    }

    public function logoUrl(): ?string
    {
        $path = $this->get('logo_path');

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * Absolute filesystem path of an uploaded setting image, for PDF rendering.
     */
    public function localPath(string $key): ?string
    {
        $path = $this->get($key);

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return Storage::disk('public')->path($path);
    }
}
