<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\InstituteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Uploadable images and the settings keys they are stored under.
     */
    private const IMAGES = [
        'logo' => 'logo_path',
        'signature_1' => 'signatory_1_signature_path',
        'signature_2' => 'signatory_2_signature_path',
    ];

    public function __construct(private InstituteSettings $settings) {}

    public function edit(): View
    {
        return view('admin.settings.edit', ['values' => $this->settings->all()]);
    }

    public function update(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $image = ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024', 'dimensions:max_width=3000,max_height=3000'];

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'address' => ['required', 'string', 'max:500'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'office_hours' => ['nullable', 'string', 'max:120'],
            'map_embed_url' => ['nullable', 'url:https', 'max:2000', 'starts_with:https://www.google.com/maps/embed,https://maps.google.com/'],
            'facebook_url' => ['nullable', 'url:http,https', 'max:255'],
            'instagram_url' => ['nullable', 'url:http,https', 'max:255'],
            'linkedin_url' => ['nullable', 'url:http,https', 'max:255'],
            'youtube_url' => ['nullable', 'url:http,https', 'max:255'],
            'signatory_1_name' => ['required', 'string', 'max:120'],
            'signatory_1_designation' => ['required', 'string', 'max:120'],
            'signatory_2_name' => ['required', 'string', 'max:120'],
            'signatory_2_designation' => ['required', 'string', 'max:120'],
            'certificate_title' => ['required', 'string', 'max:80'],
            'certificate_footer' => ['nullable', 'string', 'max:300'],
            'accreditation_statement' => ['nullable', 'string', 'max:300'],
            'grade_bands' => ['required', 'array', 'min:1', 'max:10'],
            'grade_bands.*.grade' => ['required', 'string', 'max:20', 'distinct'],
            'grade_bands.*.min' => ['required', 'numeric', 'min:0', 'max:100', 'distinct'],
            'logo' => $image,
            'signature_1' => $image,
            'signature_2' => $image,
            'remove' => ['nullable', 'array'],
            'remove.*' => ['in:'.implode(',', array_keys(self::IMAGES))],
        ], [
            'map_embed_url.starts_with' => 'Paste the "Embed a map" URL from Google Maps (it starts with https://www.google.com/maps/embed).',
            'grade_bands.*.min.distinct' => 'Each grade band needs a different minimum percentage.',
        ]);

        $values = collect($validated)->except(array_merge(array_keys(self::IMAGES), ['remove']))->all();
        $values['grade_bands'] = collect($validated['grade_bands'])
            ->map(fn (array $band) => ['grade' => trim($band['grade']), 'min' => (float) $band['min']])
            ->sortByDesc('min')
            ->values()
            ->all();

        $obsoleteFiles = [];

        foreach (self::IMAGES as $input => $key) {
            $current = $this->settings->get($key);

            if ($request->hasFile($input)) {
                $values[$key] = $request->file($input)->store('branding', 'public');
            } elseif (in_array($input, $validated['remove'] ?? [], true)) {
                $values[$key] = null;
            } else {
                continue;
            }

            if ($current) {
                $obsoleteFiles[] = $current;
            }
        }

        $this->settings->set($values);
        Storage::disk('public')->delete($obsoleteFiles);

        $logger->log('settings.updated', null, 'Institute settings updated');

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved.');
    }
}
