<x-site-layout title="คำสั่งซื้อของฉัน">
    @php
        $badge = [
            'paid' => 'bg-green-100 text-green-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'failed' => 'bg-red-100 text-red-700',
            'refunded' => 'bg-gray-100 text-gray-600',
        ];
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold">คำสั่งซื้อของฉัน</h1>

        @if ($orders->isEmpty())
            <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 p-12 text-center">
                <p class="font-semibold">ยังไม่มีคำสั่งซื้อ</p>
                <p class="text-gray-500 text-sm mt-1">เมื่อคุณซื้อคอร์ส รายการจะแสดงที่นี่</p>
                <a href="{{ route('courses.index') }}" class="mt-5 inline-block rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">เลือกคอร์สเรียน</a>
            </div>
        @else
            <div class="mt-6 space-y-3">
                @foreach ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}"
                       class="flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white ring-1 ring-gray-200 p-5 hover:ring-indigo-300 hover:shadow-sm transition">
                        <div>
                            <div class="flex items-center gap-3">
                                <span class="font-semibold">{{ $order->order_number }}</span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge[$order->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $order->status_label }}</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $order->created_at->format('d/m/Y H:i') }} · {{ $order->items_count }} คอร์ส</p>
                        </div>
                        <div class="text-right">
                            <p class="font-bold">฿{{ number_format((float) $order->total) }}</p>
                            <span class="text-sm text-indigo-600">ดูรายละเอียด →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-site-layout>
