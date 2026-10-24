<?php

namespace App\Http\Controllers\Public;

use App\Enums\CourseStatus;
use App\Enums\EnquiryStatus;
use App\Http\Controllers\Controller;
use App\Models\AdmissionEnquiry;
use App\Models\Course;
use App\Notifications\AdmissionEnquiryReceived;
use App\Notifications\NewAdmissionEnquiry;
use App\Services\InstituteSettings;
use App\Services\MailSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;

class AdmissionEnquiryController extends Controller
{
    public function create(Request $request): View
    {
        $courses = Course::query()->published()->orderBy('title')->pluck('title', 'id');
        $selectedCourse = Course::query()->published()->where('slug', $request->query('course'))->value('id');

        return view('public.admissions', compact('courses', 'selectedCourse'));
    }

    public function store(Request $request, InstituteSettings $settings, MailSettings $mail): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{7,20}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'course_id' => ['nullable', Rule::exists('courses', 'id')->where('status', CourseStatus::Published->value)],
            'message' => ['nullable', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'Please enter a valid phone number.',
        ]);

        $enquiry = new AdmissionEnquiry($validated);
        $enquiry->status = EnquiryStatus::New;
        $enquiry->save();

        Notification::route('mail', $settings->get('email'))->notify(new NewAdmissionEnquiry($enquiry));

        if ($mail->mayAcknowledge($enquiry->email)) {
            Notification::route('mail', $enquiry->email)->notify(new AdmissionEnquiryReceived($enquiry));
        }

        return redirect()->route('admissions.create')
            ->with('success', 'Thank you! Your enquiry has been received. Our admissions team will contact you shortly.');
    }
}
