@php
    $thaiMonths = [1=>'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    $d = $certificate->issued_at;
    $thaiDate = $d->day . ' ' . $thaiMonths[(int) $d->month] . ' ' . ($d->year + 543);
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ใบประกาศนียบัตร · {{ $course->title }}</title>
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
            <a href="{{ route('learn.show', $course) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> กลับไปเรียน</a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="bi bi-printer" aria-hidden="true"></i> พิมพ์ / บันทึกเป็น PDF
            </button>
        </div>
    </div>

    {{-- Certificate --}}
    <div class="max-w-5xl mx-auto p-4 sm:p-8">
        <div class="cert-sheet relative bg-white shadow-xl rounded-lg overflow-hidden mx-auto" style="aspect-ratio: 297/210;">
            {{-- decorative frame --}}
            <div class="absolute inset-3 sm:inset-5 border-2 border-indigo-200 rounded"></div>
            <div class="absolute inset-4 sm:inset-6 border border-amber-300/70 rounded"></div>

            <div class="relative h-full flex flex-col items-center justify-center text-center px-6 sm:px-16 py-8">
                <div class="flex items-center gap-2 text-indigo-600">
                    <span class="grid place-items-center h-9 w-9 rounded-lg bg-indigo-600 text-white font-bold">M</span>
                    <span class="text-lg font-bold tracking-tight">Mooc<span class="text-indigo-600">LMS</span></span>
                </div>

                <p class="mt-4 sm:mt-6 text-xs sm:text-sm tracking-[0.3em] text-amber-600 uppercase">Certificate of Completion</p>
                <h1 class="mt-1 text-2xl sm:text-4xl font-bold text-gray-800">ใบประกาศนียบัตร</h1>

                <p class="mt-4 sm:mt-6 text-sm text-gray-500">มอบให้เพื่อแสดงว่า</p>
                <p class="mt-1 text-2xl sm:text-3xl font-bold text-indigo-700">{{ $user->name }}</p>

                <p class="mt-3 sm:mt-4 text-sm text-gray-500">ได้สำเร็จการอบรมหลักสูตร</p>
                <p class="mt-1 text-lg sm:text-2xl font-semibold text-gray-800 max-w-2xl">“{{ $course->title }}”</p>

                <div class="mt-6 sm:mt-10 w-full max-w-2xl flex items-end justify-between text-xs sm:text-sm text-gray-500">
                    <div class="text-center">
                        <p class="font-semibold text-gray-700">{{ $course->instructor->name }}</p>
                        <p class="border-t border-gray-300 mt-1 pt-1">ผู้สอน</p>
                    </div>
                    <div class="grid place-items-center h-14 w-14 sm:h-16 sm:w-16 rounded-full ring-2 ring-amber-300 text-amber-500 shrink-0">
                        <i class="bi bi-award text-2xl sm:text-3xl" aria-hidden="true"></i>
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-gray-700">{{ $thaiDate }}</p>
                        <p class="border-t border-gray-300 mt-1 pt-1">วันที่</p>
                    </div>
                </div>

                <p class="mt-4 text-[10px] sm:text-xs text-gray-400">เลขที่ {{ $certificate->certificate_number }}</p>
            </div>
        </div>
    </div>
</body>
</html>
