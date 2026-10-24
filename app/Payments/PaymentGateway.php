<?php

namespace App\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * The identifier stored on orders paid through this gateway.
     */
    public function name(): string;

    /**
     * The URL the buyer is redirected to in order to pay for the order.
     */
    public function checkoutUrl(Order $order): string;
}
