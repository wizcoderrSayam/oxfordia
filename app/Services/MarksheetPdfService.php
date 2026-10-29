<?php

namespace App\Services;

use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class MarksheetPdfService
{
    public function __construct(private InstituteSettings $settings) {}

    /**
     * Download the student's marksheet as an A4 PDF.
     */
    public function download(Enrollment $enrollment): Response
    {
        $enrollment->loadMissing(['student', 'course.modules', 'marks']);

        return Pdf::setOption(['isFontSubsettingEnabled' => true, 'isRemoteEnabled' => false])
            ->loadView('results.marksheet-pdf', [
                'enrollment' => $enrollment,
                'marks' => $enrollment->marks->keyBy('course_module_id'),
                'settings' => $this->settings->all(),
                'logo' => $this->logoDataUri(),
            ])
            ->setPaper('a4')
            ->download(Str::replace('/', '-', $enrollment->enrollment_number).'-marksheet.pdf');
    }

    /**
     * Embed the logo as a data URI so dompdf never fetches remote URLs.
     */
    private function logoDataUri(): ?string
    {
        $path = $this->settings->localPath('logo_path');

        if (! $path) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
