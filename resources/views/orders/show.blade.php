<x-site-layout :title="'คำสั่งซื้อ ' . $order->order_number">
    @php
        $methodLabel = match ($order->payment?->method) {
            'card' => 'บัตรเครดิต / เดบิต',
            'promptpay' => 'พร้อมเพย์',
            default => '—',
        };
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-indigo-600">← คำสั่งซื้อทั้งหมด</a>

        {{-- Status banner --}}
        @if ($order->isPaid())
            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-green-50 ring-1 ring-green-200 p-5">
                <i class="bi bi-check-circle-fill text-green-600 text-4xl shrink-0 leading-none" aria-hidden="true"></i>
                <div>
                    <p class="font-bold text-green-800">ชำระเงินสำเร็จ</p>
                    <p class="text-sm text-green-700">ปลดล็อกคอร์สเรียบร้อยแล้ว เริ่มเรียนได้เลย</p>
                </div>
            </div>
        @elseif ($order->status === 'failed')
            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-red-50 ring-1 ring-red-200 p-5">
                <i class="bi bi-x-circle-fill text-red-600 text-4xl shrink-0 leading-none" aria-hidden="true"></i>
                <div class="flex-1">
                    <p class="font-bold text-red-800">การชำระเงินไม่สำเร็จ</p>
                    <p class="text-sm text-red-700">คอร์สยังอยู่ในตะกร้า ลองชำระใหม่ได้</p>
                </div>
                <a href="{{ route('cart.index') }}" class="rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 whitespace-nowrap">ไปที่ตะกร้า</a>
            </div>
        @endif

        {{-- Receipt --}}
        <div class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
            <div class="flex flex-wrap items-center justify-between gap-2 p-6 border-b border-gray-100">
                <div>
                    <h1 class="text-lg font-bold">{{ $order->order_number }}</h1>
                    <p class="text-sm text-gray-500">{{ $order->created_at->format('d/m/Y H:i') }} น.</p>
                </div>
                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $order->isPaid() ? 'bg-green-100 text-green-700' : ($order->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $order->status_label }}</span>
            </div>

            {{-- Items --}}
            <ul class="divide-y divide-gray-100">
                @foreach ($order->items as $item)
                    <li class="flex items-center gap-4 p-5">
                        <img src="{{ $item->course?->thumbnail_url }}" alt="" class="h-14 w-20 rounded-lg object-cover shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-medium leading-snug line-clamp-2">{{ $item->title }}</p>
                            @if ($order->isPaid() && $item->course)
                                <a href="{{ route('learn.show', $item->course) }}" class="text-sm font-medium text-indigo-600 hover:underline">เริ่มเรียน →</a>
                            @endif
                        </div>
                        <span class="font-semibold whitespace-nowrap">฿{{ number_format((float) $item->price) }}</span>
                    </li>
                @endforeach
            </ul>

            {{-- Totals --}}
            <div class="p-6 border-t border-gray-100 space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">ราคารวม</span><span>฿{{ number_format((float) $order->subtotal) }}</span></div>
                @if ((float) $order->discount > 0)
                    <div class="flex justify-between text-green-600"><span>ส่วนลด{{ $order->coupon_code ? ' (' . $order->coupon_code . ')' : '' }}</span><span>-฿{{ number_format((float) $order->discount) }}</span></div>
                @endif
                <div class="flex justify-between text-base font-bold pt-2 border-t border-gray-100"><span>ยอดชำระ</span><span>฿{{ number_format((float) $order->total) }}</span></div>
            </div>

            {{-- Payment details --}}
            @if ($order->payment)
                <div class="px-6 pb-6 text-sm text-gray-500 space-y-1">
                    <div class="flex justify-between"><span>วิธีชำระเงิน</span><span class="text-gray-700">{{ $methodLabel }}</span></div>
                    <div class="flex justify-between"><span>ช่องทาง</span><span class="text-gray-700">{{ strtoupper($order->payment->gateway) }} (จำลอง)</span></div>
                    @if ($order->payment->transaction_ref)
                        <div class="flex justify-between"><span>เลขอ้างอิง</span><span class="text-gray-700 font-mono text-xs">{{ $order->payment->transaction_ref }}</span></div>
                    @endif
                </div>
            @endif
        </div>

        @if ($order->isPaid())
            <a href="{{ route('dashboard') }}" class="mt-6 flex items-center justify-center gap-2 w-full rounded-full bg-indigo-600 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                ไปที่การเรียนของฉัน →
            </a>
        @endif
    </div>
</x-site-layout>
