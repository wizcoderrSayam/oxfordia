<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Support\Facades\URL;

/**
 * Simulated gateway for development and demos. It never moves real money.
 */
class DummyGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'dummy';
    }

    public function checkoutUrl(Order $order): string
    {
        return URL::temporarySignedRoute('payments.dummy.show', now()->addMinutes(30), ['order' => $order]);
    }
}
