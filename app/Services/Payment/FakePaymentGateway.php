<?php

namespace App\Services\Payment;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Simulated gateway for local development and demos. It does NOT talk to any
 * real payment provider and never handles real card data — the checkout form
 * only sends a chosen outcome (success/fail) and a method label.
 *
 * Replace the binding in AppServiceProvider with a real gateway for production.
 */
class FakePaymentGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'fake';
    }

    public function charge(Order $order, array $paymentData = []): PaymentResult
    {
        $method = $paymentData['method'] ?? 'card';
        $reference = 'FAKE-' . strtoupper(Str::random(12));

        // The demo checkout lets the user pick the outcome to exercise both paths.
        if (($paymentData['outcome'] ?? 'success') === 'fail') {
            return PaymentResult::failure($reference, 'การชำระเงินถูกปฏิเสธ (จำลอง)', $method);
        }

        return PaymentResult::success($reference, $method, [
            'simulated' => true,
            'amount' => (float) $order->total,
        ]);
    }
}
