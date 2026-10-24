<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CertificateVerificationController extends Controller
{
    /**
     * Show the verification form and, when a number is submitted, its result.
     */
    public function index(Request $request, CertificateService $certificates): View|Response
    {
        $request->validate(['number' => ['nullable', 'string', 'max:64']]);

        $number = trim((string) $request->query('number', ''));

        if ($number === '') {
            return view('public.verify', ['searched' => false, 'certificate' => null, 'number' => '']);
        }

        return $this->result($certificates->findForVerification($number), $number);
    }

    /**
     * Verification target of the QR code printed on each certificate.
     */
    public function show(string $identifier, CertificateService $certificates): Response
    {
        return $this->result($certificates->findForVerification($identifier), $identifier);
    }

    private function result(?Certificate $certificate, string $number): Response
    {
        return response()->view('public.verify', [
            'searched' => true,
            'certificate' => $certificate,
            'number' => $number,
        ], $certificate ? 200 : 404);
    }
}
