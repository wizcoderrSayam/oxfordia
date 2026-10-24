<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\AdmissionEnquiry;
use App\Services\EmailReplyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AdmissionEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::enum(EnquiryStatus::class)],
        ]);

        $enquiries = AdmissionEnquiry::query()
            ->with(['course', 'student'])
            ->when($filters['q'] ?? null, function ($query, string $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.enquiries.index', compact('enquiries', 'filters'));
    }

    public function show(AdmissionEnquiry $enquiry, EmailReplyService $replies): View
    {
        $enquiry->load(['course', 'student']);

        return view('admin.enquiries.show', [
            'enquiry' => $enquiry,
            'replies' => $replies->history($enquiry),
        ]);
    }

    public function update(Request $request, AdmissionEnquiry $enquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(EnquiryStatus::class)],
        ]);

        $enquiry->status = EnquiryStatus::from($validated['status']);
        $enquiry->save();

        return back()->with('success', "Enquiry marked as {$enquiry->status->label()}.");
    }

    public function reply(Request $request, AdmissionEnquiry $enquiry, EmailReplyService $replies): RedirectResponse
    {
        if (! $enquiry->email) {
            return back()->with('error', 'This enquiry has no email address. Contact the visitor by phone instead.');
        }

        $validated = $request->validate(EmailReplyService::rules());

        try {
            $replies->send($enquiry, $enquiry->email, $enquiry->name, $validated['reply_subject'], $validated['reply_body']);
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withInput()->with('error', 'The reply could not be sent: '.$exception->getMessage());
        }

        if ($enquiry->status === EnquiryStatus::New) {
            $enquiry->status = EnquiryStatus::Contacted;
            $enquiry->save();
        }

        return back()->with('success', "Reply sent to {$enquiry->email}.");
    }
}
