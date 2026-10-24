<?php

namespace App\Services;

use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CertificatePdfService
{
    public function __construct(private InstituteSettings $settings) {}

    /**
     * View data shared by the PDF, preview and print templates.
     *
     * @return array<string, mixed>
     */
    public function viewData(Certificate $certificate, bool $forPdf = false): array
    {
        $certificate->loadMissing(['course', 'enrollment']);

        return [
            'certificate' => $certificate,
            'settings' => $this->settings->all(),
            'qrCode' => $this->qrDataUri($certificate->verificationUrl()),
            'verificationUrl' => $certificate->verificationUrl(),
            'logo' => $this->image('logo_path', $forPdf),
            'signature1' => $this->image('signatory_1_signature_path', $forPdf),
            'signature2' => $this->image('signatory_2_signature_path', $forPdf),
        ];
    }

    public function download(Certificate $certificate): Response
    {
        return Pdf::setOption(['isFontSubsettingEnabled' => true, 'isRemoteEnabled' => false])
            ->loadView('certificates.pdf', $this->viewData($certificate, forPdf: true))
            ->setPaper('a4', 'landscape')
            ->download($certificate->certificate_number.'.pdf');
    }

    public function qrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => true,
            'scale' => 6,
            'quietzoneSize' => 1,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * Embed images as data URIs so dompdf never fetches remote URLs.
     */
    private function image(string $key, bool $forPdf): ?string
    {
        $path = $this->settings->localPath($key);

        if (! $path) {
            return null;
        }

        if (! $forPdf) {
            return Storage::disk('public')->url($this->settings->get($key));
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
