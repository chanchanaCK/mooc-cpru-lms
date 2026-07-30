<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly CheckoutService $checkout,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $items = $this->cart->items($user);
        $subtotal = (float) $items->sum(fn ($item) => (float) $item->course->price);

        $evaluation = $this->checkout->evaluateCoupon($request->session()->get('coupon_code'), $subtotal);

        return view('cart.index', [
            'items' => $items,
            'subtotal' => $subtotal,
            'coupon' => $evaluation['coupon'],
            'discount' => (float) $evaluation['discount'],
            'total' => round($subtotal - (float) $evaluation['discount'], 2),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $status = $this->cart->add($request->user(), $course);

        return match ($status) {
            'added' => redirect()->route('cart.index')->with('success', 'เพิ่ม "' . $course->title . '" ลงตะกร้าแล้ว'),
            'exists' => redirect()->route('cart.index')->with('success', 'คอร์สนี้อยู่ในตะกร้าอยู่แล้ว'),
            'owned' => redirect()->route('learn.show', $course)->with('success', 'คุณมีคอร์สนี้อยู่แล้ว เริ่มเรียนได้เลย'),
            'free' => redirect()->route('courses.show', $course)->with('success', 'คอร์สนี้ฟรี กดลงทะเบียนเรียนได้เลย'),
            default => back()->with('error', 'ไม่สามารถเพิ่มคอร์สนี้ได้'),
        };
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->cart->remove($request->user(), $course);

        return redirect()->route('cart.index')->with('success', 'นำออกจากตะกร้าแล้ว');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:50']]);

        $subtotal = $this->cart->subtotal($request->user());
        $evaluation = $this->checkout->evaluateCoupon($request->input('code'), $subtotal);

        if ($evaluation['status'] === 'ok') {
            $request->session()->put('coupon_code', $evaluation['coupon']->code);

            return redirect()->route('cart.index')->with('success', $evaluation['message']);
        }

        $request->session()->forget('coupon_code');

        return redirect()->route('cart.index')->with('error', $evaluation['message'] ?: 'ใช้คูปองไม่ได้');
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget('coupon_code');

        return redirect()->route('cart.index')->with('success', 'ยกเลิกคูปองแล้ว');
    }
}
