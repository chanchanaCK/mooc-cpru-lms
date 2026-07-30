<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $items = $this->cart->items($user);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'ตะกร้าว่างเปล่า');
        }

        $subtotal = (float) $items->sum(fn ($item) => (float) $item->course->price);
        $evaluation = $this->checkout->evaluateCoupon($request->session()->get('coupon_code'), $subtotal);

        return view('checkout.index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $evaluation['coupon'],
            'discount' => (float) $evaluation['discount'],
            'total' => round($subtotal - (float) $evaluation['discount'], 2),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'method' => ['required', 'in:card,promptpay'],
            'outcome' => ['required', 'in:success,fail'], // simulated gateway only
        ]);

        $user = $request->user();

        if ($this->cart->count($user) === 0) {
            return redirect()->route('cart.index')->with('error', 'ตะกร้าว่างเปล่า');
        }

        $order = $this->checkout->placeOrder(
            $user,
            $request->session()->get('coupon_code'),
            ['method' => $data['method'], 'outcome' => $data['outcome']],
        );

        if ($order->isPaid()) {
            $request->session()->forget('coupon_code');

            return redirect()->route('orders.show', $order)
                ->with('success', 'ชำระเงินสำเร็จ! ปลดล็อกคอร์สเรียบร้อยแล้ว');
        }

        return redirect()->route('orders.show', $order)
            ->with('error', 'การชำระเงินไม่สำเร็จ ลองใหม่อีกครั้งได้จากตะกร้า');
    }
}
