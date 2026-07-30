@php
    use App\Models\CreditRecord;
    $thaiMonths = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    $today = now();
    $issueDate = $today->day . ' ' . $thaiMonths[(int) $today->month] . ' พ.ศ. ' . ($today->year + 543);
    $docNo = 'CB-' . $today->format('Y') . '-' . str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ใบแสดงผลการเรียน (คลังหน่วยกิต) · {{ $user->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .sheet { box-shadow: none !important; margin: 0 !important; }
            @page { size: A4 portrait; margin: 14mm; }
        }
    </style>
</head>
<body class="font-sans bg-gray-100 text-gray-900">
    {{-- Toolbar --}}
    <div class="no-print sticky top-0 bg-white border-b border-gray-200 z-10">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('credit-bank.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> กลับคลังหน่วยกิต</a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-printer" aria-hidden="true"></i> พิมพ์ / บันทึกเป็น PDF
            </button>
        </div>
    </div>

    {{-- Document --}}
    <div class="max-w-4xl mx-auto p-4 sm:p-8">
        <div class="sheet bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="p-8 sm:p-12">
                {{-- Header --}}
                <div class="text-center border-b-2 border-indigo-600 pb-5">
                    <div class="flex items-center justify-center gap-2 text-indigo-600">
                        <i class="bi bi-mortarboard-fill text-2xl" aria-hidden="true"></i>
                        <span class="text-xl font-bold tracking-tight">MoocLMS</span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">สถาบันการเรียนรู้ตลอดชีวิต · ระบบธนาคารหน่วยกิต (Credit Bank)</p>
                    <h1 class="mt-3 text-lg font-bold">ใบแสดงผลการเรียนและหน่วยกิตสะสม</h1>
                    <p class="text-xs text-gray-400">Academic Transcript / Credit Bank Statement</p>
                </div>

                {{-- Learner info --}}
                <div class="mt-5 grid grid-cols-2 gap-y-1 gap-x-6 text-sm">
                    <div><span class="text-gray-500">ชื่อผู้เรียน:</span> <span class="font-semibold">{{ $user->name }}</span></div>
                    <div class="text-right"><span class="text-gray-500">เลขที่เอกสาร:</span> <span class="font-mono">{{ $docNo }}</span></div>
                    <div><span class="text-gray-500">อีเมล:</span> {{ $user->email }}</div>
                    <div class="text-right"><span class="text-gray-500">วันที่ออก:</span> {{ $issueDate }}</div>
                </div>

                {{-- Records table --}}
                <table class="mt-6 w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-indigo-50 text-indigo-900 text-left">
                            <th class="border border-indigo-100 px-3 py-2 font-semibold w-24">รหัสวิชา</th>
                            <th class="border border-indigo-100 px-3 py-2 font-semibold">รายวิชา / รายการ</th>
                            <th class="border border-indigo-100 px-3 py-2 font-semibold text-center w-20">หน่วยกิต</th>
                            <th class="border border-indigo-100 px-3 py-2 font-semibold text-center w-16">เกรด</th>
                            <th class="border border-indigo-100 px-3 py-2 font-semibold text-center w-24">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($records as $r)
                            <tr>
                                <td class="border border-gray-200 px-3 py-2 font-mono text-xs text-gray-600">{{ $r->code ?? '—' }}</td>
                                <td class="border border-gray-200 px-3 py-2">{{ $r->title }}</td>
                                <td class="border border-gray-200 px-3 py-2 text-center">{{ CreditRecord::fmt($r->credits) }}</td>
                                <td class="border border-gray-200 px-3 py-2 text-center font-semibold">{{ $r->grade ?? '—' }}</td>
                                <td class="border border-gray-200 px-3 py-2 text-center text-xs text-gray-500">{{ $r->source_label }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="border border-gray-200 px-3 py-6 text-center text-gray-400">ยังไม่มีหน่วยกิตสะสม</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 font-bold">
                            <td class="border border-gray-200 px-3 py-2 text-right" colspan="2">รวมหน่วยกิตสะสม (Total Credits Earned)</td>
                            <td class="border border-gray-200 px-3 py-2 text-center text-indigo-700">{{ CreditRecord::fmt($total_credits) }}</td>
                            <td class="border border-gray-200 px-3 py-2" colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>

                {{-- Legend + signature --}}
                <div class="mt-6 flex items-end justify-between gap-6">
                    <p class="text-xs text-gray-400 leading-relaxed max-w-md">
                        เกรด: S = ผ่าน, U = ไม่ผ่าน, A–F = ระดับคะแนน · หน่วยกิตในคลังมีอายุ 8 ปีนับจากวันที่ได้รับ<br>
                        เอกสารนี้ออกจากระบบธนาคารหน่วยกิตเพื่อแสดงหน่วยกิตสะสมของผู้เรียน
                    </p>
                    <div class="text-center shrink-0">
                        <div class="h-12"></div>
                        <div class="w-48 border-t border-gray-400 pt-1 text-xs text-gray-500">นายทะเบียน</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
