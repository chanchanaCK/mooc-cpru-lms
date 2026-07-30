@php use App\Models\CreditRecord; @endphp
<x-site-layout title="หลักสูตรสะสมหน่วยกิต">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-2xl">
            <h1 class="text-2xl font-bold">หลักสูตรสะสมหน่วยกิต</h1>
            <p class="text-gray-500 mt-1">เรียนคอร์สสะสมหน่วยกิตให้ครบเกณฑ์ แล้วรับคุณวุฒิ/สัมฤทธิบัตรตามระบบธนาคารหน่วยกิต — เหมาะกับการ upskill &amp; reskill</p>
        </div>

        @if ($programs->isEmpty())
            <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 p-12 text-center">
                <x-icon name="book" class="text-5xl text-gray-300" />
                <p class="mt-3 font-semibold">ยังไม่มีหลักสูตรที่เปิดรับ</p>
                <p class="text-gray-500 text-sm mt-1">โปรดกลับมาใหม่เร็ว ๆ นี้</p>
            </div>
        @else
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($programs as $program)
                    <a href="{{ route('programs.show', $program) }}"
                       class="group rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden hover:ring-indigo-300 hover:shadow-lg transition flex flex-col">
                        <div class="aspect-[16/9] overflow-hidden">
                            <img src="{{ $program->thumbnail_url }}" alt="{{ $program->title }}" class="h-full w-full object-cover group-hover:scale-105 transition">
                        </div>
                        <div class="p-5 flex flex-col flex-1">
                            <span class="inline-flex w-max items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-2.5 py-0.5 text-xs font-medium">
                                <i class="bi bi-patch-check" aria-hidden="true"></i> {{ $program->type_label }}
                            </span>
                            <h2 class="mt-2 font-bold leading-snug group-hover:text-indigo-600">{{ $program->title }}</h2>
                            @if ($program->subtitle)
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $program->subtitle }}</p>
                            @endif
                            <div class="mt-4 pt-4 border-t border-gray-100 flex items-center justify-between text-sm text-gray-500">
                                <span class="inline-flex items-center gap-1"><i class="bi bi-mortarboard" aria-hidden="true"></i> {{ CreditRecord::fmt($program->required_credits) }} หน่วยกิต</span>
                                <span class="inline-flex items-center gap-1"><i class="bi bi-collection-play" aria-hidden="true"></i> {{ $program->courses_count }} วิชา</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-site-layout>
