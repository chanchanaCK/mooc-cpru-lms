<x-site-layout title="สตูดิโอผู้สอน">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold">สตูดิโอผู้สอน</h1>
                <p class="text-gray-500 mt-1">สร้างและจัดการคอร์สของคุณ</p>
            </div>
            <a href="{{ route('studio.courses.create') }}"
               class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> สร้างคอร์สใหม่
            </a>
        </div>

        @if ($courses->isEmpty())
            <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 p-12 text-center">
                <i class="bi bi-easel2 text-5xl text-gray-300" aria-hidden="true"></i>
                <p class="mt-3 font-semibold">ยังไม่มีคอร์ส</p>
                <p class="text-gray-500 text-sm mt-1">เริ่มสร้างคอร์สแรกของคุณวันนี้</p>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($courses as $course)
                    <div class="flex flex-col sm:flex-row gap-4 rounded-2xl bg-white ring-1 ring-gray-200 p-4">
                        <img src="{{ $course->thumbnail_url }}" alt="" class="h-24 w-full sm:w-40 rounded-lg object-cover shrink-0">

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $course->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $course->status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                                </span>
                                @if ($course->category)<span class="text-xs text-indigo-600">{{ $course->category->name }}</span>@endif
                            </div>
                            <h3 class="mt-1 font-semibold leading-snug">{{ $course->title }}</h3>
                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500">
                                <span class="flex items-center gap-1"><i class="bi bi-collection-play" aria-hidden="true"></i> {{ $course->lessons_count }} บทเรียน</span>
                                <span class="flex items-center gap-1"><i class="bi bi-people" aria-hidden="true"></i> {{ number_format($course->students_count) }} ผู้เรียน</span>
                                <span class="flex items-center gap-1"><i class="bi bi-tag" aria-hidden="true"></i> {{ $course->price_label }}</span>
                            </div>
                        </div>

                        <div class="flex sm:flex-col flex-wrap items-stretch gap-2 sm:w-44 shrink-0">
                            <a href="{{ route('studio.courses.curriculum', $course) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                <i class="bi bi-list-check" aria-hidden="true"></i> จัดการเนื้อหา
                            </a>
                            <a href="{{ route('studio.courses.edit', $course) }}"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg ring-1 ring-gray-300 px-3 py-2 text-sm font-semibold hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                                <i class="bi bi-pencil" aria-hidden="true"></i> แก้ไข
                            </a>
                            <form method="POST" action="{{ route('studio.courses.publish', $course) }}" class="flex-1">
                                @csrf
                                <button class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 {{ $course->status === 'published' ? 'ring-1 ring-gray-300 text-gray-600 hover:bg-gray-50 focus-visible:ring-gray-400' : 'bg-green-600 text-white hover:bg-green-700 focus-visible:ring-green-500' }}">
                                    <i class="bi {{ $course->status === 'published' ? 'bi-eye-slash' : 'bi-globe' }}" aria-hidden="true"></i>
                                    {{ $course->status === 'published' ? 'เป็นร่าง' : 'เผยแพร่' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-site-layout>
