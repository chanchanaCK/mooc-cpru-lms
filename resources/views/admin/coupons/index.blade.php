<x-admin-layout title="คูปอง" active="coupons">
    <h1 class="text-2xl font-bold">คูปองส่วนลด</h1>

    <div class="mt-5 grid lg:grid-cols-3 gap-6">
        {{-- Create form --}}
        <div class="lg:col-span-1">
            <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <h2 class="font-bold mb-3">สร้างคูปอง</h2>
                <form method="POST" action="{{ route('admin.coupons.store') }}" class="space-y-3" x-data="{ type: '{{ old('type', 'percent') }}' }">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">โค้ด</label>
                        <input type="text" name="code" value="{{ old('code') }}" required placeholder="เช่น NEWYEAR"
                               class="w-full rounded-lg border-gray-300 text-sm uppercase focus:border-indigo-500 focus:ring-indigo-500">
                        @error('code')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">ประเภท</label>
                            <select name="type" x-model="type" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="percent">เปอร์เซ็นต์ (%)</option>
                                <option value="fixed">จำนวนเงิน (฿)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1"><span x-text="type === 'percent' ? 'ลด (%)' : 'ลด (฿)'"></span></label>
                            <input type="number" name="value" value="{{ old('value') }}" min="0" step="1" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('value')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">ยอดขั้นต่ำ (฿)</label>
                            <input type="number" name="min_amount" value="{{ old('min_amount', 0) }}" min="0"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">ใช้ได้สูงสุด (ครั้ง)</label>
                            <input type="number" name="max_uses" value="{{ old('max_uses') }}" min="1" placeholder="ไม่จำกัด"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">วันหมดอายุ</label>
                        <input type="date" name="expires_at" value="{{ old('expires_at') }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <button class="w-full rounded-full bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">สร้างคูปอง</button>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2 space-y-3">
            @forelse ($coupons as $coupon)
                <div class="flex items-center gap-4 rounded-2xl bg-white ring-1 ring-gray-200 p-4">
                    <span class="grid place-items-center h-11 w-11 rounded-xl bg-indigo-50 text-indigo-600 text-xl shrink-0"><i class="bi bi-tag" aria-hidden="true"></i></span>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-bold font-mono">{{ $coupon->code }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $coupon->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $coupon->is_active ? 'ใช้งาน' : 'ปิด' }}</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ $coupon->label }}
                            @if ((float) $coupon->min_amount > 0) · ขั้นต่ำ ฿{{ number_format((float) $coupon->min_amount) }} @endif
                            · ใช้ไป {{ $coupon->used_count }}{{ $coupon->max_uses ? '/' . $coupon->max_uses : '' }} ครั้ง
                            @if ($coupon->expires_at) · หมดอายุ {{ $coupon->expires_at->format('d/m/Y') }} @endif
                        </p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('admin.coupons.toggle', $coupon) }}">
                            @csrf
                            <button class="rounded-full px-3 py-1.5 text-xs font-semibold ring-1 {{ $coupon->is_active ? 'ring-gray-300 text-gray-600 hover:bg-gray-50' : 'ring-green-300 text-green-700 hover:bg-green-50' }}">{{ $coupon->is_active ? 'ปิด' : 'เปิด' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('ลบคูปอง {{ $coupon->code }}?')">
                            @csrf @method('DELETE')
                            <button class="grid place-items-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50" aria-label="ลบคูปอง"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-10 text-center text-gray-500">ยังไม่มีคูปอง</div>
            @endforelse
        </div>
    </div>
</x-admin-layout>
