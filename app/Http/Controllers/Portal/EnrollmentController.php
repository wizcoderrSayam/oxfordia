<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * The student's marksheet for one enrollment.
     */
    public function show(Request $request, Enrollment $enrollment): View
    {
        $student = $request->user()->student;

        abort_unless($student && $enrollment->student_id === $student->id, 404);

        $enrollment->load(['course.modules', 'marks', 'validCertificate']);
        $marks = $enrollment->marks->keyBy('course_module_id');

        return view('portal.enrollment', compact('enrollment', 'marks', 'student'));
    }
}
