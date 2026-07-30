<x-admin-layout title="ภาพรวม" active="dashboard">
    <h1 class="text-2xl font-bold">ภาพรวมระบบ</h1>

    {{-- KPI cards --}}
    @php
        $cards = [
            ['label' => 'ผู้ใช้ทั้งหมด', 'value' => number_format($stats['users']), 'icon' => 'bi-people', 'color' => 'indigo'],
            ['label' => 'ผู้สอน', 'value' => number_format($stats['instructors']), 'icon' => 'bi-easel2', 'color' => 'violet'],
            ['label' => 'คอร์ส (เผยแพร่)', 'value' => $stats['published'] . '/' . $stats['courses'], 'icon' => 'bi-collection-play', 'color' => 'blue'],
            ['label' => 'การลงเรียน', 'value' => number_format($stats['enrollments']), 'icon' => 'bi-mortarboard', 'color' => 'green'],
            ['label' => 'รายได้รวม', 'value' => '฿' . number_format($stats['revenue']), 'icon' => 'bi-cash-stack', 'color' => 'amber'],
            ['label' => 'ใบประกาศ', 'value' => number_format($stats['certificates']), 'icon' => 'bi-award', 'color' => 'rose'],
        ];
        $colorMap = [
            'indigo' => 'bg-indigo-50 text-indigo-600', 'violet' => 'bg-violet-50 text-violet-600',
            'blue' => 'bg-blue-50 text-blue-600', 'green' => 'bg-green-50 text-green-600',
            'amber' => 'bg-amber-50 text-amber-600', 'rose' => 'bg-rose-50 text-rose-600',
        ];
    @endphp
    <div class="mt-5 grid grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($cards as $c)
            <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-4 flex items-center gap-4">
                <span class="grid place-items-center h-11 w-11 rounded-xl {{ $colorMap[$c['color']] }} text-xl shrink-0"><i class="bi {{ $c['icon'] }}" aria-hidden="true"></i></span>
                <div class="min-w-0">
                    <p class="text-xl font-bold leading-tight truncate">{{ $c['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $c['label'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid lg:grid-cols-2 gap-6">
        {{-- Top courses bar chart --}}
        <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
            <h2 class="font-bold">คอร์สยอดนิยม (ตามจำนวนผู้เรียน)</h2>
            <div class="mt-4 space-y-3">
                @forelse ($topCourses as $course)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="truncate pr-2">{{ $course->title }}</span>
                            <span class="text-gray-500 shrink-0">{{ number_format($course->students_count) }}</span>
                        </div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ max(3, round($course->students_count / $maxStudents * 100)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">ยังไม่มีข้อมูล</p>
                @endforelse
            </div>
        </div>

        {{-- Recent orders --}}
        <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
            <h2 class="font-bold">คำสั่งซื้อล่าสุด</h2>
            <div class="mt-4 divide-y divide-gray-100">
                @forelse ($recentOrders as $order)
                    <div class="flex items-center justify-between py-2.5 text-sm">
                        <div class="min-w-0">
                            <p class="font-medium truncate">{{ $order->user->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $order->order_number }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold">฿{{ number_format((float) $order->total) }}</p>
                            <p class="text-xs text-gray-400">{{ $order->created_at->format('d/m/y') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-2">ยังไม่มีคำสั่งซื้อ</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
