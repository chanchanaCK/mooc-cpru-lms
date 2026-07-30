<x-site-layout title="เรียนออนไลน์กับผู้เชี่ยวชาญ">
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-indigo-600 to-violet-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-24">
            <div class="max-w-2xl">
                <h1 class="text-4xl sm:text-5xl font-bold leading-tight">
                    เรียนทักษะใหม่ <br>ได้ทุกที่ทุกเวลา
                </h1>
                <p class="mt-4 text-lg text-indigo-100">
                    คอร์สออนไลน์คุณภาพจากผู้เชี่ยวชาญตัวจริง เรียนจบรับใบประกาศนียบัตร
                </p>
                <form action="{{ route('courses.index') }}" method="GET" class="mt-8 flex gap-2 max-w-md">
                    <input type="search" name="q" placeholder="อยากเรียนอะไรดี?" aria-label="ค้นหาคอร์ส"
                           class="flex-1 rounded-full border-0 px-5 py-3 text-gray-900 focus:ring-2 focus:ring-white">
                    <button class="rounded-full bg-gray-900 px-6 py-3 font-semibold hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-indigo-700">ค้นหา</button>
                </form>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-bold">หมวดหมู่ยอดนิยม</h2>
        <div class="mt-6 flex flex-wrap gap-3">
            @foreach ($categories as $category)
                <a href="{{ route('courses.index', ['category' => $category->slug]) }}"
                   class="flex items-center gap-2 rounded-full bg-white ring-1 ring-gray-200 px-4 py-2 text-sm font-medium hover:ring-indigo-300 hover:text-indigo-600 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    <i class="bi {{ $category->icon }} text-indigo-500" aria-hidden="true"></i>
                    <span>{{ $category->name }}</span>
                    <span class="text-gray-400">{{ $category->published_courses_count }}</span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Featured --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold">คอร์สยอดนิยม</h2>
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">ดูทั้งหมด →</a>
        </div>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse ($featured as $course)
                <x-course-card :course="$course" />
            @empty
                <p class="text-gray-500 col-span-full">ยังไม่มีคอร์ส — รัน seeder เพื่อเพิ่มข้อมูลตัวอย่าง</p>
            @endforelse
        </div>
    </section>

    {{-- Newest --}}
    @if ($newest->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
            <h2 class="text-2xl font-bold">มาใหม่ล่าสุด</h2>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach ($newest as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>
        </section>
    @endif
</x-site-layout>
