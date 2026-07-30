<x-site-layout :title="'รายงาน · ' . $organization->name">
    @php
        $cellClass = function ($p) {
            if ($p === null) return 'text-gray-300';
            if ($p >= 100) return 'text-green-700 font-semibold';
            if ($p > 0) return 'text-amber-600';
            return 'text-gray-400';
        };
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('org.show', $organization) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> {{ $organization->name }}</a>
        <h1 class="text-2xl font-bold mt-2">รายงานความคืบหน้า</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $members->count() }} สมาชิก · {{ $courses->count() }} คอร์สที่มอบหมาย · {{ $organization->assignedPrograms->count() }} หลักสูตร</p>

        @if ($members->isEmpty())
            <div class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-10 text-center text-gray-500">
                ยังไม่มีข้อมูล — เพิ่มสมาชิกก่อน
            </div>
        @else
            <div class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium sticky left-0 bg-gray-50 min-w-[12rem]">สมาชิก</th>
                                <th class="px-3 py-3 text-center font-medium min-w-[6rem] bg-indigo-50/50">หน่วยกิต<br>สะสม</th>
                                <th class="px-3 py-3 text-center font-medium min-w-[5rem] bg-indigo-50/50">หลักสูตร<br>สำเร็จ</th>
                                @foreach ($courses as $course)
                                    <th class="px-3 py-3 text-center font-medium min-w-[7rem]"><span class="line-clamp-2">{{ $course->title }}</span></th>
                                @endforeach
                                <th class="px-4 py-3 text-center font-medium">เฉลี่ย</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($members as $member)
                                @php
                                    $vals = $courses->map(fn ($c) => $progress["{$member->id}-{$c->id}"] ?? null);
                                    $avg = $courses->count() ? (int) round($vals->map(fn ($v) => (int) $v)->avg()) : 0;
                                @endphp
                                <tr>
                                    <td class="px-4 py-3 sticky left-0 bg-white">
                                        <div class="flex items-center gap-2">
                                            <span class="grid place-items-center h-7 w-7 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold shrink-0">{{ mb_strtoupper(mb_substr($member->name, 0, 1)) }}</span>
                                            <span class="truncate">{{ $member->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-3 text-center font-semibold text-indigo-600 bg-indigo-50/30">{{ \App\Models\CreditRecord::fmt($creditTotals[$member->id] ?? 0) }}</td>
                                    <td class="px-3 py-3 text-center bg-indigo-50/30">{{ $programsDone[$member->id] ?? 0 }}</td>
                                    @foreach ($courses as $course)
                                        @php $p = $progress["{$member->id}-{$course->id}"] ?? null; @endphp
                                        <td class="px-3 py-3 text-center {{ $cellClass($p) }}">{{ $p === null ? '—' : $p . '%' }}</td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold {{ $avg >= 100 ? 'bg-green-100 text-green-700' : ($avg > 0 ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500') }}">{{ $avg }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 text-gray-600">
                            <tr>
                                <td class="px-4 py-3 font-medium sticky left-0 bg-gray-50">รวมทั้งทีม</td>
                                <td class="px-3 py-3 text-center font-semibold text-indigo-700">{{ \App\Models\CreditRecord::fmt($creditTotals->sum()) }}</td>
                                <td class="px-3 py-3 text-center font-semibold">{{ $programsDone->sum() }}</td>
                                @foreach ($courses as $course)
                                    @php $done = $members->filter(fn ($m) => ($progress["{$m->id}-{$course->id}"] ?? 0) >= 100)->count(); @endphp
                                    <td class="px-3 py-3 text-center">{{ $done }}/{{ $members->count() }}</td>
                                @endforeach
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-400">— = ยังไม่ได้ลงเรียน · สีเหลือง = กำลังเรียน · สีเขียว = เรียนจบ</p>
        @endif
    </div>
</x-site-layout>
