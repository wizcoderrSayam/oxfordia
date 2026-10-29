<?php

namespace App\Http\Controllers\Public;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Services\MarksheetPdfService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
    /**
     * Show the result lookup form.
     */
    public function index(): View
    {
        return view('public.results', ['searched' => false, 'enrollment' => null]);
    }

    /**
     * Find a result by roll number (the enrollment number) and the student's date of birth.
     *
     * Both values must match, and a miss gives the same response whichever one was wrong,
     * so the lookup cannot be used to discover which roll numbers exist.
     */
    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'roll_number' => ['required', 'string', 'max:30'],
            'dob_day' => ['required', 'integer', 'between:1,31'],
            'dob_month' => ['required', 'integer', 'between:1,12'],
            'dob_year' => ['required', 'integer', 'between:1900,'.now()->year],
        ], [], [
            'roll_number' => 'roll number',
            'dob_day' => 'day of birth',
            'dob_month' => 'month of birth',
            'dob_year' => 'year of birth',
        ]);

        if (! checkdate((int) $validated['dob_month'], (int) $validated['dob_day'], (int) $validated['dob_year'])) {
            throw ValidationException::withMessages(['date_of_birth' => 'Please choose a valid date of birth.']);
        }

        $dateOfBirth = Carbon::createFromDate((int) $validated['dob_year'], (int) $validated['dob_month'], (int) $validated['dob_day'])->startOfDay();
        $rollNumber = Str::upper(trim($validated['roll_number']));

        $enrollment = Enrollment::query()
            ->where('enrollment_number', $rollNumber)
            ->where('status', '!=', EnrollmentStatus::Withdrawn)
            ->whereHas('student', fn (Builder $query) => $query->whereDate('date_of_birth', $dateOfBirth->toDateString()))
            ->with(['student', 'course.modules', 'marks'])
            ->first();

        return view('public.results', [
            'searched' => true,
            'enrollment' => $enrollment,
            'rollNumber' => $rollNumber,
            'dateOfBirth' => $dateOfBirth,
        ]);
    }

    /**
     * Download the marksheet PDF. Only reachable through the short-lived signed
     * link printed on a successful lookup.
     */
    public function marksheet(Enrollment $enrollment, MarksheetPdfService $pdf): Response
    {
        abort_if($enrollment->status === EnrollmentStatus::Withdrawn, 404);

        return $pdf->download($enrollment);
    }

    /**
     * Stream the uploaded result document. Only reachable through the short-lived signed
     * link printed on a successful lookup.
     */
    public function document(Enrollment $enrollment): StreamedResponse
    {
        abort_unless($enrollment->hasResultDocument(), 404);

        $filename = Str::replace('/', '-', $enrollment->enrollment_number).'-result.'.pathinfo($enrollment->result_document_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->response($enrollment->result_document_path, $filename);
    }
}
