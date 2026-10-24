<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Course;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(Request $request, Course $course, CheckoutService $checkout): View|RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        try {
            $checkout->assertCanPurchase($request->user(), $course);
        } catch (ValidationException $exception) {
            return redirect()->route('courses.show', $course)->with('error', $exception->validator->errors()->first());
        }

        $user = $request->user();
        $student = $user->student;

        return view('public.checkout', [
            'course' => $course,
            'details' => [
                'full_name' => $student->full_name ?? $user->name,
                'phone' => $student?->phone,
                'date_of_birth' => $student?->date_of_birth?->toDateString(),
                'gender' => $student?->gender,
                'address' => $student?->address,
            ],
            'hasStudentRecord' => (bool) $student,
        ]);
    }

    public function store(StoreCheckoutRequest $request, Course $course, CheckoutService $checkout): RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        try {
            $order = $checkout->start($request->user(), $course, $request->safe()->except('terms'));
        } catch (ValidationException $exception) {
            return redirect()->route('courses.show', $course)->with('error', $exception->validator->errors()->first());
        }

        return redirect()->away($checkout->paymentUrl($order));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $order->load(['course', 'enrollment']);

        return view('public.order', compact('order'));
    }
}
