@php use App\Models\CreditRecord; @endphp
<x-admin-layout title="เทียบโอนหน่วยกิต" active="transfers">
    <h1 class="text-2xl font-bold">พิจารณาคำขอเทียบโอนหน่วยกิต</h1>
    <p class="text-gray-500 mt-1 text-sm">ตรวจสอบคำขอและอนุมัติหน่วยกิตเข้าคลังของผู้เรียน (RPL)</p>

    {{-- Pending queue --}}
    <h2 class="mt-6 font-bold flex items-center gap-2">รอพิจารณา <span class="rounded-full bg-amber-100 text-amber-700 px-2 py-0.5 text-xs">{{ $pending->count() }}</span></h2>
    <div class="mt-3 space-y-4">
        @include('partials.transfer-queue', ['pending' => $pending, 'approveRoute' => 'admin.transfers.approve', 'rejectRoute' => 'admin.transfers.reject'])
    </div>

    {{-- Reviewed history --}}
    @if ($reviewed->isNotEmpty())
        <h2 class="mt-8 font-bold">ประวัติการพิจารณา</h2>
        <div class="mt-3 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">ผู้เรียน</th>
                            <th class="px-4 py-3 font-medium">วิชา / ที่มา</th>
                            <th class="px-4 py-3 font-medium text-center">หน่วยกิต</th>
                            <th class="px-4 py-3 font-medium text-center">ผล</th>
                            <th class="px-4 py-3 font-medium text-right">โดย</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($reviewed as $t)
                            <tr>
                                <td class="px-4 py-3">{{ $t->user?->name }}</td>
                                <td class="px-4 py-3">{{ $t->course_name }} <span class="text-gray-400">· {{ $t->source_name }}</span></td>
                                <td class="px-4 py-3 text-center">{{ $t->status === 'approved' ? CreditRecord::fmt($t->credits_awarded) : '—' }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($t->status === 'approved')
                                        <span class="rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs">อนุมัติ</span>
                                    @else
                                        <span class="rounded-full bg-rose-50 text-rose-700 px-2.5 py-0.5 text-xs">ไม่อนุมัติ</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $t->reviewer?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-admin-layout>
