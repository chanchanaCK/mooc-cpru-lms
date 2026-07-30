@php
    use App\Models\CreditRecord;
    $thaiMonths = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    $d = $enrollment->completed_at ?? now();
    $thaiDate = $d->day . ' ' . $thaiMonths[(int) $d->month] . ' ' . ($d->year + 543);
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>คุณวุฒิ · {{ $program->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .cert-sheet { box-shadow: none !important; margin: 0 !important; }
            @page { size: A4 landscape; margin: 0; }
        }
    </style>
</head>
<body class="font-sans bg-gray-100 text-gray-900">
    {{-- Toolbar --}}
    <div class="no-print sticky top-0 bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('programs.show', $program) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> กลับหน้าหลักสูตร</a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-printer" aria-hidden="true"></i> พิมพ์ / บันทึกเป็น PDF
            </button>
        </div>
    </div>

    {{-- Qualification --}}
    <div class="max-w-5xl mx-auto p-4 sm:p-8">
        <div class="cert-sheet relative bg-white shadow-xl rounded-lg overflow-hidden mx-auto" style="aspect-ratio: 297/210;">
            <div class="absolute inset-3 sm:inset-5 border-2 border-indigo-200 rounded"></div>
            <div class="absolute inset-4 sm:inset-6 border border-amber-300/70 rounded"></div>

            <div class="relative h-full flex flex-col items-center justify-center text-center px-6 sm:px-16 py-8">
                <div class="flex items-center gap-2 text-indigo-600">
                    <i class="bi bi-mortarboard-fill text-2xl" aria-hidden="true"></i>
                    <span class="text-lg font-bold tracking-tight">MoocLMS</span>
                </div>
                <p class="mt-1 text-xs text-gray-400">สถาบันการเรียนรู้ตลอดชีวิต · ระบบธนาคารหน่วยกิต</p>

                <p class="mt-5 text-sm text-gray-500">ขอมอบ{{ $program->type_label }}ฉบับนี้เพื่อแสดงว่า</p>
                <h1 class="mt-2 text-3xl sm:text-4xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="mt-4 text-sm text-gray-500">ได้สำเร็จการศึกษาหลักสูตร</p>
                <h2 class="mt-1 text-xl sm:text-2xl font-semibold text-indigo-700">{{ $program->title }}</h2>
                <p class="mt-3 text-sm text-gray-600">โดยสะสมหน่วยกิตครบตามเกณฑ์ {{ CreditRecord::fmt($program->required_credits) }} หน่วยกิต</p>

                <div class="mt-6 flex items-end justify-center gap-12 text-xs text-gray-500">
                    <div class="text-center">
                        <p class="font-mono text-gray-700">{{ $enrollment->certificate_number }}</p>
                        <div class="mt-1 w-40 border-t border-gray-300 pt-1">เลขที่คุณวุฒิ</div>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-700">{{ $thaiDate }}</p>
                        <div class="mt-1 w-40 border-t border-gray-300 pt-1">วันที่สำเร็จการศึกษา</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
