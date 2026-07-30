<x-site-layout title="ตะกร้าของฉัน">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold">ตะกร้าของฉัน</h1>

        @if ($items->isEmpty())
            <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 p-12 text-center">
                <i class="bi bi-cart3 text-5xl text-gray-300" aria-hidden="true"></i>
                <p class="mt-3 font-semibold">ตะกร้ายังว่างอยู่</p>
                <p class="text-gray-500 text-sm mt-1">เลือกคอร์สที่สนใจแล้วกลับมาชำระเงินได้เลย</p>
                <a href="{{ route('courses.index') }}" class="mt-5 inline-block rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">เลือกคอร์สเรียน</a>
            </div>
        @else
            <p class="text-gray-500 mt-1">{{ $items->count() }} คอร์สในตะกร้า</p>

            <div class="mt-6 grid lg:grid-cols-3 gap-8">
                {{-- Items --}}
                <div class="lg:col-span-2 space-y-4">
                    @foreach ($items as $item)
                        @php $course = $item->course; @endphp
                        <div class="flex gap-4 rounded-2xl bg-white ring-1 ring-gray-200 p-4">
                            <a href="{{ route('courses.show', $course) }}" class="shrink-0">
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="h-20 w-32 rounded-lg object-cover">
                            </a>
                            <div class="flex-1 min-w-0">
                                @if ($course->category)<span class="text-xs font-medium text-indigo-600">{{ $course->category->name }}</span>@endif
                                <a href="{{ route('courses.show', $course) }}" class="block font-semibold leading-snug line-clamp-2 hover:text-indigo-700">{{ $course->title }}</a>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $course->instructor->name ?? '' }}</p>
                            </div>
                            <div class="flex flex-col items-end justify-between">
                                <span class="font-bold whitespace-nowrap">{{ $course->price_label }}</span>
                                <form method="POST" action="{{ route('cart.destroy', $course) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" aria-label="นำ {{ $course->title }} ออกจากตะกร้า"
                                            class="grid place-items-center h-10 w-10 rounded-full text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                                        <i class="bi bi-trash3 text-xl" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Summary --}}
                <div class="lg:col-span-1">
                    <div class="lg:sticky lg:top-24 rounded-2xl bg-white ring-1 ring-gray-200 p-6 space-y-4">
                        <h2 class="font-bold">สรุปคำสั่งซื้อ</h2>

                        {{-- Coupon --}}
                        @if ($coupon)
                            <div class="flex items-center justify-between rounded-lg bg-green-50 ring-1 ring-green-200 px-3 py-2">
                                <span class="flex items-center gap-2 text-sm text-green-700">
                                    <i class="bi bi-tag text-base" aria-hidden="true"></i>
                                    <span class="font-semibold">{{ $coupon->code }}</span> · {{ $coupon->label }}
                                </span>
                                <form method="POST" action="{{ route('cart.coupon.remove') }}">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-gray-500 hover:text-red-600 underline">ยกเลิก</button>
                                </form>
                            </div>
                        @else
                            <form method="POST" action="{{ route('cart.coupon.apply') }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="code" placeholder="โค้ดส่วนลด" aria-label="โค้ดส่วนลด"
                                       class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase placeholder:normal-case">
                                <button class="rounded-lg bg-gray-900 px-4 text-sm font-semibold text-white hover:bg-gray-800">ใช้</button>
                            </form>
                        @endif

                        <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">ราคารวม</span><span>฿{{ number_format($subtotal) }}</span></div>
                            @if ($discount > 0)
                                <div class="flex justify-between text-green-600"><span>ส่วนลด</span><span>-฿{{ number_format($discount) }}</span></div>
                            @endif
                            <div class="flex justify-between text-base font-bold pt-2 border-t border-gray-100">
                                <span>ยอดชำระ</span><span>฿{{ number_format($total) }}</span>
                            </div>
                        </div>

                        <a href="{{ route('checkout.index') }}"
                           class="flex items-center justify-center gap-2 w-full rounded-full bg-indigo-600 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            ดำเนินการชำระเงิน
                        </a>
                        <a href="{{ route('courses.index') }}" class="block text-center text-sm text-gray-500 hover:text-indigo-600">← เลือกคอร์สเพิ่ม</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-site-layout>
