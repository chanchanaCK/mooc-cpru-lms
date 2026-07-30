<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\PaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly LearningService $learning,
        private readonly PaymentGateway $gateway,
    ) {
    }

    /**
     * Evaluate a coupon code against a subtotal.
     *
     * @return array{status: string, coupon: ?Coupon, discount: float, message: string}
     */
    public function evaluateCoupon(?string $code, float $subtotal): array
    {
        if (! $code) {
            return ['status' => 'none', 'coupon' => null, 'discount' => 0.0, 'message' => ''];
        }

        $coupon = Coupon::findByCode($code);

        if (! $coupon || ! $coupon->isRedeemable()) {
            return ['status' => 'invalid', 'coupon' => null, 'discount' => 0.0,
                    'message' => 'คูปองไม่ถูกต้องหรือหมดอายุแล้ว'];
        }

        if (! $coupon->appliesTo($subtotal)) {
            return ['status' => 'min', 'coupon' => $coupon, 'discount' => 0.0,
                    'message' => 'ต้องมียอดซื้อขั้นต่ำ ฿' . number_format((float) $coupon->min_amount)];
        }

        return ['status' => 'ok', 'coupon' => $coupon, 'discount' => $coupon->discountFor($subtotal),
                'message' => 'ใช้คูปอง ' . $coupon->label . ' แล้ว'];
    }

    /**
     * Turn the user's cart into an order, charge it, and (on success) enrol them.
     *
     * @param  array<string, mixed>  $paymentData
     */
    public function placeOrder(User $user, ?string $couponCode, array $paymentData = []): Order
    {
        // Never charge for courses the user already owns.
        $lines = $this->cart->items($user)
            ->reject(fn ($item) => $user->isEnrolledIn($item->course))
            ->values();

        if ($lines->isEmpty()) {
            throw new \RuntimeException('ตะกร้าว่างเปล่า');
        }

        $subtotal = (float) $lines->sum(fn ($item) => (float) $item->course->price);
        $evaluation = $this->evaluateCoupon($couponCode, $subtotal);
        $coupon = $evaluation['status'] === 'ok' ? $evaluation['coupon'] : null;
        $discount = (float) $evaluation['discount'];
        $total = round($subtotal - $discount, 2);

        return DB::transaction(function () use ($user, $lines, $subtotal, $discount, $total, $coupon, $paymentData) {
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
            ]);

            foreach ($lines as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $item->course->id,
                    'title' => $item->course->title,
                    'price' => $item->course->price,
                ]);
            }

            $result = $this->gateway->charge($order, $paymentData);

            Payment::create([
                'order_id' => $order->id,
                'gateway' => $this->gateway->name(),
                'method' => $result->method,
                'amount' => $total,
                'status' => $result->success ? 'succeeded' : 'failed',
                'transaction_ref' => $result->reference,
                'meta' => $result->success ? $result->meta : ['error' => $result->failureMessage],
            ]);

            if ($result->success) {
                $order->update(['status' => 'paid', 'paid_at' => now()]);

                foreach ($lines as $item) {
                    $this->learning->enroll($user, $item->course);
                }

                $coupon?->increment('used_count');
                $this->cart->clear($user);
            } else {
                $order->update(['status' => 'failed']);
            }

            return $order->load('items');
        });
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'MOOC-' . now()->format('ymd') . '-' . strtoupper(Str::random(5));
        } while (Order::where('order_number', $number)->exists());

        return $number;
    }
}
