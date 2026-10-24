<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * Simulated payment page for the dummy gateway. Only reachable through signed URLs.
 */
class DummyPaymentController extends Controller
{
    public function show(Request $request, Order $order): View|RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if (! $order->isPending()) {
            return redirect()->route('checkout.show', $order);
        }

        $order->load('course');

        $completeUrl = URL::temporarySignedRoute('payments.dummy.complete', now()->addMinutes(30), ['order' => $order]);

        return view('payments.dummy', compact('order', 'completeUrl'));
    }

    public function complete(Request $request, Order $order, CheckoutService $checkout): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        $outcome = $request->validate(['outcome' => ['required', 'in:success,failure']])['outcome'];

        if ($outcome === 'success') {
            $order = $checkout->markPaid($order, 'DUMMY-'.Str::upper(Str::random(16)));
        } else {
            $order = $checkout->markFailed($order, 'Payment declined (simulated).');
        }

        return redirect()->route('checkout.show', $order);
    }

    private function authorizeOrder(Request $request, Order $order): void
    {
        abort_unless(config('institute.payment_driver') === 'dummy' && ! app()->isProduction() && $order->gateway === 'dummy', 404);
        abort_unless($order->user_id === $request->user()->id, 404);
    }
}
