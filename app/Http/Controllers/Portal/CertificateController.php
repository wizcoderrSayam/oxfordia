<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificatePdfService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CertificateController extends Controller
{
    public function download(Request $request, Certificate $certificate, CertificatePdfService $pdf): Response
    {
        abort_unless(Gate::allows('view-certificate', $certificate) && $certificate->isValid(), 404);

        return $pdf->download($certificate);
    }
}
