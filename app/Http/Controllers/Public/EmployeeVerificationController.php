<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmployeeVerificationController extends Controller
{
    /**
     * Show the verification form, pre-filling a registration number passed in the query string.
     */
    public function index(Request $request): View
    {
        $request->validate(['registration_number' => ['nullable', 'string', 'max:30']]);

        return view('public.verify-employee', [
            'searched' => false,
            'employee' => null,
            'registrationNumber' => strtoupper(trim((string) $request->query('registration_number', ''))),
        ]);
    }

    /**
     * Find an employee by registration number and date of birth.
     *
     * Both values must match, and a miss gives the same response whichever one was wrong,
     * so the lookup cannot be used to discover which registration numbers exist.
     */
    public function lookup(Request $request, EmployeeService $employees): Response
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
        $registrationNumber = strtoupper(trim($validated['registration_number']));

        $employee = $employees->findForVerification($registrationNumber, $dateOfBirth);

        return response()->view('public.verify-employee', [
            'searched' => true,
            'employee' => $employee,
            'registrationNumber' => $registrationNumber,
            'dateOfBirth' => $dateOfBirth,
        ], $employee ? 200 : 404);
    }
}
