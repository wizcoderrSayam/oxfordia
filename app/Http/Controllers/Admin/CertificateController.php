<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CertificateStatus;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Services\CertificatePdfService;
use App\Services\CertificateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateService $certificates,
        private CertificatePdfService $pdf,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(CertificateStatus::class)],
        ]);

        $certificates = Certificate::query()
            ->with('issuer')
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('certificate_number', 'like', "%{$search}%")
                        ->orWhere('student_name_snapshot', 'like', "%{$search}%")
                        ->orWhere('course_title_snapshot', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(15, ['*'], 'certificates')
            ->withQueryString();

        $eligible = Enrollment::query()
            ->eligibleForCertificate()
            ->with(['student', 'course'])
            ->orderBy('completion_date')
            ->paginate(10, ['*'], 'eligible')
            ->withQueryString();

        return view('admin.certificates.index', compact('certificates', 'eligible', 'filters'));
    }

    public function show(Certificate $certificate): View
    {
        $certificate->load(['student', 'enrollment', 'issuer', 'revoker', 'replaces']);

        return view('admin.certificates.show', $this->pdf->viewData($certificate) + [
            'replacement' => Certificate::query()->where('replaces_certificate_id', $certificate->id)->first(),
        ]);
    }

    public function pdf(Certificate $certificate): Response
    {
        return $this->pdf->download($certificate);
    }

    public function print(Certificate $certificate): View
    {
        return view('certificates.print', $this->pdf->viewData($certificate));
    }

    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $validated = $request->validate([
            'issue_date' => ['nullable', 'date', 'before_or_equal:today'],
        ]);

        try {
            $certificate = $this->certificates->issue($enrollment, $request->user(), $validated['issue_date'] ?? null);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('admin.certificates.show', $certificate)
            ->with('success', "Certificate {$certificate->certificate_number} issued.");
    }

    public function revoke(Request $request, Certificate $certificate): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        try {
            $this->certificates->revoke($certificate, $request->user(), $validated['reason']);
        } catch (ValidationException $exception) {
            return back()->with('error', $exception->validator->errors()->first());
        }

        return redirect()->route('admin.certificates.show', $certificate)->with('success', 'Certificate revoked.');
    }
}
