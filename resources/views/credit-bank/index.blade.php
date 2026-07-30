@php use App\Models\CreditRecord; @endphp
<x-site-layout title="คลังหน่วยกิตของฉัน">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">คลังหน่วยกิตของฉัน</h1>
                <p class="text-gray-500 mt-1">สะสมหน่วยกิตจากคอร์สที่เรียนจบ แล้วนำไปต่อยอดเป็นคุณวุฒิได้ตามระบบธนาคารหน่วยกิต</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('credit-bank.transfers.index') }}"
                   class="inline-flex items-center gap-2 rounded-full ring-1 ring-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="bi bi-arrow-left-right" aria-hidden="true"></i> เทียบโอนหน่วยกิต
                </a>
                <a href="{{ route('credit-bank.transcript') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i> ใบแสดงผลการเรียน (ทรานสคริปต์)
                </a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white p-5 shadow-sm">
                <p class="text-indigo-100 text-sm">หน่วยกิตสะสมรวม</p>
                <p class="mt-1 text-4xl font-bold leading-none">{{ CreditRecord::fmt($total_credits) }}</p>
                <p class="text-indigo-100 text-xs mt-2">หน่วยกิต</p>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <p class="text-gray-500 text-sm">จากคอร์สเรียน</p>
                <p class="mt-1 text-2xl font-bold">{{ CreditRecord::fmt($course_credits) }}</p>
                <p class="text-gray-400 text-xs mt-2">หน่วยกิต</p>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <p class="text-gray-500 text-sm">จากการเทียบโอน</p>
                <p class="mt-1 text-2xl font-bold">{{ CreditRecord::fmt($transfer_credits) }}</p>
                <p class="text-gray-400 text-xs mt-2">หน่วยกิต</p>
            </div>
            <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <p class="text-gray-500 text-sm">รายการทั้งหมด</p>
                <p class="mt-1 text-2xl font-bold">{{ number_format($count) }}</p>
                <p class="text-gray-400 text-xs mt-2">รายการ</p>
            </div>
        </div>

        {{-- My programs --}}
        <div class="mt-8 flex items-center justify-between">
            <h2 class="font-bold">หลักสูตรของฉัน</h2>
            <a href="{{ route('programs.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700">เลือกหลักสูตรเพิ่ม <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        @if ($programs->isEmpty())
            <div class="mt-3 rounded-2xl bg-white ring-1 ring-gray-200 p-6 text-center text-sm text-gray-500">
                ยังไม่ได้ลงทะเบียนหลักสูตรใด — สะสมหน่วยกิตให้ครบเพื่อรับคุณวุฒิ
                <a href="{{ route('programs.index') }}" class="text-indigo-600 hover:underline">ดูหลักสูตร</a>
            </div>
        @else
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach ($programs as $pe)
                    @php
                        $prog = $pe->program;
                        $req = (float) ($prog?->required_credits ?? 0);
                        $pct = $req > 0 ? min(100, (int) floor((float) $pe->credits_earned / $req * 100)) : ($pe->isCompleted() ? 100 : 0);
                    @endphp
                    @if ($prog)
                        <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <a href="{{ route('programs.show', $prog) }}" class="font-semibold hover:text-indigo-600">{{ $prog->title }}</a>
                                @if ($pe->isCompleted())
                                    <span class="shrink-0 rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs">สำเร็จ</span>
                                @else
                                    <span class="shrink-0 rounded-full bg-indigo-50 text-indigo-700 px-2.5 py-0.5 text-xs">กำลังเรียน</span>
                                @endif
                            </div>
                            <div class="mt-3 flex items-end justify-between text-sm">
                                <span class="font-bold text-indigo-600">{{ CreditRecord::fmt($pe->credits_earned) }}</span>
                                <span class="text-gray-400">/ {{ CreditRecord::fmt($prog->required_credits) }} หน่วยกิต</span>
                            </div>
                            <div class="mt-1.5 h-2 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full bg-indigo-600 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            @if ($pe->isCompleted())
                                <a href="{{ route('programs.qualification', $prog) }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700"><i class="bi bi-award" aria-hidden="true"></i> รับคุณวุฒิ</a>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- Credit records --}}
        <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h2 class="font-bold">ประวัติหน่วยกิตในคลัง</h2>
            </div>

            @if ($records->isEmpty())
                <div class="p-12 text-center">
                    <x-icon name="book" class="text-5xl text-gray-300" />
                    <p class="mt-3 font-semibold">ยังไม่มีหน่วยกิตในคลัง</p>
                    <p class="text-gray-500 text-sm mt-1">เรียนคอร์สที่ให้หน่วยกิตให้จบ แล้วหน่วยกิตจะถูกฝากเข้าคลังอัตโนมัติ</p>
                    <a href="{{ route('courses.index') }}" class="mt-5 inline-block rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">เลือกคอร์สเรียน</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-left">
                            <tr>
                                <th class="px-5 py-3 font-medium">รหัส</th>
                                <th class="px-5 py-3 font-medium">รายวิชา / รายการ</th>
                                <th class="px-5 py-3 font-medium text-center">ที่มา</th>
                                <th class="px-5 py-3 font-medium text-center">เกรด</th>
                                <th class="px-5 py-3 font-medium text-right">หน่วยกิต</th>
                                <th class="px-5 py-3 font-medium text-right">วันที่ได้รับ</th>
                                <th class="px-5 py-3 font-medium text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($records as $r)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="px-5 py-3 text-gray-500 font-mono text-xs">{{ $r->code ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="font-medium">{{ $r->title }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <span class="inline-block rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600">{{ $r->source_label }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-center font-semibold">{{ $r->grade ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right font-semibold">{{ CreditRecord::fmt($r->credits) }}</td>
                                    <td class="px-5 py-3 text-right text-gray-500">{{ optional($r->earned_at)->format('d/m/') }}{{ $r->earned_at ? $r->earned_at->year + 543 : '' }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @php
                                            $tone = match ($r->status) {
                                                'earned' => 'bg-emerald-50 text-emerald-700',
                                                'pending' => 'bg-amber-50 text-amber-700',
                                                'expired' => 'bg-gray-100 text-gray-500',
                                                default => 'bg-rose-50 text-rose-700',
                                            };
                                        @endphp
                                        <span class="inline-block rounded-full px-2.5 py-0.5 text-xs {{ $tone }}">{{ $r->status_label }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <p class="mt-4 text-xs text-gray-400">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            หน่วยกิตในคลังมีอายุ 8 ปีนับจากวันที่ได้รับ และสามารถนำไปสะสมเป็นหลักสูตร/คุณวุฒิ หรือใช้เทียบโอนได้
        </p>
    </div>
</x-site-layout>
