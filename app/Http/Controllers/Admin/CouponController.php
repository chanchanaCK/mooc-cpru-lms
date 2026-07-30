<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        $coupons = Coupon::latest()->get();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0', $request->type === 'percent' ? 'max:100' : 'max:1000000'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $code = Str::upper(trim($data['code']));

        if (Coupon::whereRaw('UPPER(code) = ?', [$code])->exists()) {
            return back()->withInput()->with('error', 'มีโค้ดนี้อยู่แล้ว');
        }

        Coupon::create([
            'code' => $code,
            'type' => $data['type'],
            'value' => $data['value'],
            'min_amount' => $data['min_amount'] ?? 0,
            'max_uses' => $data['max_uses'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', "สร้างคูปอง {$code} แล้ว");
    }

    public function toggle(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', $coupon->is_active ? "เปิดใช้ {$coupon->code}" : "ปิดใช้ {$coupon->code}");
    }

    public function destroy(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('success', 'ลบคูปองแล้ว');
    }
}
