<?php

namespace App\Services\Payment;

use App\Models\Order;

/**
 * Payment gateway contract. Swap the bound implementation in AppServiceProvider
 * to plug in a real provider (Omise, 2C2P, Stripe, PromptPay) later.
 */
interface PaymentGateway
{
    /**
     * Attempt to charge the order.
     *
     * @param  array<string, mixed>  $paymentData  method + provider-specific fields
     */
    public function charge(Order $order, array $paymentData = []): PaymentResult;

    /** Machine name of the gateway (stored on the payment record). */
    public function name(): string;
}
