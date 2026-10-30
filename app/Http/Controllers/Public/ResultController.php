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
        return view('public.results', ['searched' => false, 'enrollments' => collect()]);
    }

    /**
     * Find results by the student's registration number (or an enrollment roll number)
     * and the student's date of birth.
     *
     * A registration number shows every course the student is enrolled in; a roll number
     * shows just that course. Both values must match, and a miss gives the same response
     * whichever one was wrong, so the lookup cannot be used to discover which numbers exist.
     */
    public function lookup(Request $request): View
    {
        $validated = $request->validate([
            'registration_number' => ['required', 'string', 'max:30'],
            'dob_day' => ['required', 'integer', 'between:1,31'],
            'dob_month' => ['required', 'integer', 'between:1,12'],
            'dob_year' => ['required', 'integer', 'between:1900,'.now()->year],
        ], [], [
            'registration_number' => 'registration number',
            'dob_day' => 'day of birth',
            'dob_month' => 'month of birth',
            'dob_year' => 'year of birth',
        ]);

        if (! checkdate((int) $validated['dob_month'], (int) $validated['dob_day'], (int) $validated['dob_year'])) {
            throw ValidationException::withMessages(['date_of_birth' => 'Please choose a valid date of birth.']);
        }

        $dateOfBirth = Carbon::createFromDate((int) $validated['dob_year'], (int) $validated['dob_month'], (int) $validated['dob_day'])->startOfDay();
        $registrationNumber = Str::upper(trim($validated['registration_number']));

        $enrollments = Enrollment::query()
            ->where('status', '!=', EnrollmentStatus::Withdrawn)
            ->whereHas('student', fn (Builder $query) => $query->whereDate('date_of_birth', $dateOfBirth->toDateString()))
            ->where(fn (Builder $query) => $query
                ->where('enrollment_number', $registrationNumber)
                ->orWhereHas('student', fn (Builder $query) => $query->where('registration_number', $registrationNumber)))
            ->with(['student', 'course.modules', 'marks'])
            ->latest('enrollment_date')
            ->get();

        return view('public.results', [
            'searched' => true,
            'enrollments' => $enrollments,
            'registrationNumber' => $registrationNumber,
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
