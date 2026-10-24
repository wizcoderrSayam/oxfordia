<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $student = $user->student;

        $enrollments = $student
            ? $student->enrollments()->with(['course', 'validCertificate'])->latest('enrollment_date')->latest('id')->get()
            : collect();

        $orders = $user->orders()->with('course')->latest()->limit(10)->get();

        return view('portal.dashboard', compact('user', 'student', 'enrollments', 'orders'));
    }
}
