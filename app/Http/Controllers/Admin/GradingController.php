<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\InstituteSettings;
use App\Services\ResultService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradingController extends Controller
{
    public function __construct(private InstituteSettings $settings) {}

    public function edit(): View
    {
        return view('admin.grading.edit', ['bands' => $this->settings->gradeBands()]);
    }

    /**
     * Save the grade bands and re-grade existing results that have no certificate yet.
     */
    public function update(Request $request, ResultService $results, ActivityLogger $logger): RedirectResponse
    {
        $validated = $request->validate([
            'grade_bands' => ['required', 'array', 'min:1', 'max:10'],
            'grade_bands.*.grade' => ['required', 'string', 'max:20', 'distinct'],
            'grade_bands.*.min' => ['required', 'numeric', 'min:0', 'max:100', 'distinct'],
        ], [
            'grade_bands.*.grade.required' => 'Every row needs a grade.',
            'grade_bands.*.grade.distinct' => 'Each grade can only be used once.',
            'grade_bands.*.min.required' => 'Every row needs a minimum percentage.',
            'grade_bands.*.min.max' => 'A minimum percentage cannot be more than 100.',
            'grade_bands.*.min.distinct' => 'Each grade needs a different minimum percentage.',
        ]);

        $bands = collect($validated['grade_bands'])
            ->map(fn (array $band) => ['grade' => trim($band['grade']), 'min' => (float) $band['min']])
            ->sortByDesc('min')
            ->values()
            ->all();

        $regraded = DB::transaction(function () use ($bands, $results, $logger) {
            $this->settings->set(['grade_bands' => $bands]);
            $logger->log('settings.grading_updated', null, 'Grade bands updated', ['bands' => $bands]);

            return $results->regradeAll();
        });

        return redirect()->route('admin.grading.edit')
            ->with('success', $regraded > 0 ? "Grades saved. {$regraded} existing ".str('result')->plural($regraded).' re-graded.' : 'Grades saved.');
    }
}
