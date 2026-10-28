<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The scanned result (image or PDF) shown to students on the public results page.
 * Files live on the private disk and are only served through authorised routes.
 */
class EnrollmentResultDocumentController extends Controller
{
    public function __construct(private ActivityLogger $logger) {}

    public function store(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $request->validate([
            'result_document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:5120'],
        ], [], ['result_document' => 'result document']);

        $file = $request->file('result_document');
        $oldPath = $enrollment->result_document_path;

        $enrollment->forceFill([
            'result_document_path' => $file->store('result-documents', 'local'),
            'result_document_name' => $file->getClientOriginalName(),
        ])->save();

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        $this->logger->log('enrollment.result_document_uploaded', $enrollment, "Uploaded result document for {$enrollment->enrollment_number}");

        return redirect()->route('admin.enrollments.show', $enrollment)->with('success', 'Result document uploaded.');
    }

    public function show(Enrollment $enrollment): StreamedResponse
    {
        abort_unless($enrollment->hasResultDocument(), 404);

        return Storage::disk('local')->response($enrollment->result_document_path, $enrollment->result_document_name);
    }

    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        if ($enrollment->result_document_path) {
            Storage::disk('local')->delete($enrollment->result_document_path);
        }

        $enrollment->forceFill(['result_document_path' => null, 'result_document_name' => null])->save();

        $this->logger->log('enrollment.result_document_removed', $enrollment, "Removed result document for {$enrollment->enrollment_number}");

        return redirect()->route('admin.enrollments.show', $enrollment)->with('success', 'Result document removed.');
    }
}
