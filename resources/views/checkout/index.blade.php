<x-site-layout title="ชำระเงิน">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('cart.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← กลับไปที่ตะกร้า</a>
        <h1 class="text-2xl font-bold mt-2">ชำระเงิน</h1>

        <form method="POST" action="{{ route('checkout.store') }}"
              x-data="{ method: 'card', outcome: 'success', submitting: false }"
              @submit="submitting = true"
              class="mt-6 grid lg:grid-cols-3 gap-8">
            @csrf

            {{-- Payment --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Simulated gateway notice --}}
                <div class="flex gap-3 rounded-xl bg-amber-50 ring-1 ring-amber-200 p-4 text-sm text-amber-800">
                    <i class="bi bi-exclamation-triangle text-xl shrink-0 leading-none" aria-hidden="true"></i>
                    <p><span class="font-semibold">โหมดทดสอบ (จำลอง)</span> — นี่คือ payment gateway จำลองสำหรับเดโม ไม่มีการตัดเงินจริงและไม่รับข้อมูลบัตรจริง ต่อ Omise / 2C2P / PromptPay จริงได้ภายหลัง</p>
                </div>

                {{-- Method --}}
                <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-6">
                    <h2 class="font-bold mb-4">วิธีชำระเงิน</h2>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <label :class="method === 'card' ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'ring-1 ring-gray-200'"
                               class="flex items-center gap-3 rounded-xl p-4 cursor-pointer">
                            <input type="radio" name="method" value="card" x-model="method" class="text-indigo-600 focus:ring-indigo-500">
                            <i class="bi bi-credit-card text-2xl text-gray-600" aria-hidden="true"></i>
                            <span class="font-medium text-sm">บัตรเครดิต / เดบิต</span>
                        </label>
                        <label :class="method === 'promptpay' ? 'ring-2 ring-indigo-500 bg-indigo-50' : 'ring-1 ring-gray-200'"
                               class="flex items-center gap-3 rounded-xl p-4 cursor-pointer">
                            <input type="radio" name="method" value="promptpay" x-model="method" class="text-indigo-600 focus:ring-indigo-500">
                            <i class="bi bi-qr-code text-2xl text-gray-600" aria-hidden="true"></i>
                            <span class="font-medium text-sm">พร้อมเพย์ (QR)</span>
                        </label>
                    </div>
                </div>

                {{-- Simulated outcome (demo only) --}}
                <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-6">
                    <h2 class="font-bold mb-1">ผลการชำระเงิน (จำลอง)</h2>
                    <p class="text-sm text-gray-500 mb-4">เลือกเพื่อทดสอบเส้นทางสำเร็จหรือไม่สำเร็จ</p>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <label :class="outcome === 'success' ? 'ring-2 ring-green-500 bg-green-50' : 'ring-1 ring-gray-200'"
                               class="flex items-center gap-3 rounded-xl p-4 cursor-pointer">
                            <input type="radio" name="outcome" value="success" x-model="outcome" class="text-green-600 focus:ring-green-500">
                            <span class="font-medium text-sm text-green-700">ชำระสำเร็จ</span>
                        </label>
                        <label :class="outcome === 'fail' ? 'ring-2 ring-red-500 bg-red-50' : 'ring-1 ring-gray-200'"
                               class="flex items-center gap-3 rounded-xl p-4 cursor-pointer">
                            <input type="radio" name="outcome" value="fail" x-model="outcome" class="text-red-600 focus:ring-red-500">
                            <span class="font-medium text-sm text-red-700">ชำระไม่สำเร็จ</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="lg:col-span-1">
                <div class="lg:sticky lg:top-24 rounded-2xl bg-white ring-1 ring-gray-200 p-6 space-y-4">
                    <h2 class="font-bold">คำสั่งซื้อของคุณ</h2>
                    <ul class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach ($items as $item)
                            <li class="flex gap-3">
                                <img src="{{ $item->course->thumbnail_url }}" alt="" class="h-12 w-16 rounded object-cover shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium line-clamp-2 leading-snug">{{ $item->course->title }}</p>
                                </div>
                                <span class="text-sm font-semibold whitespace-nowrap">{{ $item->course->price_label }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">ราคารวม</span><span>฿{{ number_format($subtotal) }}</span></div>
                        @if ($discount > 0)
                            <div class="flex justify-between text-green-600"><span>ส่วนลด{{ $coupon ? ' (' . $coupon->code . ')' : '' }}</span><span>-฿{{ number_format($discount) }}</span></div>
                        @endif
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-100">
                            <span>ยอดชำระ</span><span>฿{{ number_format($total) }}</span>
                        </div>
                    </div>

                    <button type="submit" :disabled="submitting"
                            class="flex items-center justify-center gap-2 w-full rounded-full bg-indigo-600 py-3 font-semibold text-white hover:bg-indigo-700 disabled:opacity-70 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                        <i x-show="submitting" x-cloak class="bi bi-arrow-repeat animate-spin text-xl" aria-hidden="true"></i>
                        <span x-show="!submitting">ยืนยันการชำระเงิน · ฿{{ number_format($total) }}</span>
                        <span x-show="submitting" x-cloak>กำลังดำเนินการ...</span>
                    </button>

                    <p class="flex items-center justify-center gap-1.5 text-xs text-gray-400">
                        <i class="bi bi-shield-lock text-sm" aria-hidden="true"></i>
                        การชำระเงินปลอดภัย (จำลองสำหรับเดโม)
                    </p>
                </div>
            </div>
        </form>
    </div>
</x-site-layout>
