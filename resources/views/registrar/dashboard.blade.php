@php use App\Models\CreditRecord; @endphp
<x-site-layout title="นายทะเบียน">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold"><i class="bi bi-mortarboard text-indigo-600" aria-hidden="true"></i> สำนักงานนายทะเบียน</h1>
                <p class="text-gray-500 mt-1">จัดการหลักสูตรสะสมหน่วยกิตและอนุมัติการเทียบโอน</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('registrar.transfers.index') }}" class="relative rounded-full ring-1 ring-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    คำขอเทียบโอน
                    @if ($stats['pending_transfers'] > 0)
                        <span class="absolute -top-1.5 -right-1.5 grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-rose-500 px-1 text-xs text-white">{{ $stats['pending_transfers'] }}</span>
                    @endif
                </a>
                <a href="{{ route('registrar.programs.index') }}" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">จัดการหลักสูตร</a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="mt-6 grid grid-cols-2 lg:grid-cols-5 gap-4">
            @foreach ([
                ['หลักสูตรทั้งหมด', $stats['programs'], 'bi-journal-bookmark', 'text-indigo-600'],
                ['เผยแพร่แล้ว', $stats['published'], 'bi-broadcast', 'text-emerald-600'],
                ['ผู้ลงทะเบียน', $stats['enrollments'], 'bi-people', 'text-violet-600'],
                ['สำเร็จการศึกษา', $stats['completed'], 'bi-patch-check', 'text-amber-600'],
            ] as [$label, $value, $icon, $tone])
                <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                    <i class="bi {{ $icon }} {{ $tone }} text-xl" aria-hidden="true"></i>
                    <p class="mt-2 text-2xl font-bold">{{ number_format($value) }}</p>
                    <p class="text-gray-500 text-sm">{{ $label }}</p>
                </div>
            @endforeach
            <div class="rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white p-5">
                <i class="bi bi-mortarboard text-xl text-indigo-100" aria-hidden="true"></i>
                <p class="mt-2 text-2xl font-bold">{{ CreditRecord::fmt($stats['credits_banked']) }}</p>
                <p class="text-indigo-100 text-sm">หน่วยกิตในคลังรวม</p>
            </div>
        </div>

        {{-- Recent completions --}}
        <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-bold">สำเร็จการศึกษาล่าสุด</h2></div>
            @if ($recentCompletions->isEmpty())
                <p class="p-8 text-center text-gray-400 text-sm">ยังไม่มีผู้สำเร็จการศึกษา</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-left">
                            <tr>
                                <th class="px-5 py-3 font-medium">ผู้เรียน</th>
                                <th class="px-5 py-3 font-medium">หลักสูตร</th>
                                <th class="px-5 py-3 font-medium">เลขคุณวุฒิ</th>
                                <th class="px-5 py-3 font-medium text-right">วันที่</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recentCompletions as $pe)
                                <tr>
                                    <td class="px-5 py-3">{{ $pe->user?->name ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $pe->program?->title ?? '—' }}</td>
                                    <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $pe->certificate_number }}</td>
                                    <td class="px-5 py-3 text-right text-gray-500">{{ optional($pe->completed_at)->format('d/m/Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-site-layout>
