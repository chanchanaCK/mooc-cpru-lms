<x-site-layout title="การเรียนของฉัน">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-2xl font-bold">การเรียนของฉัน</h1>
        <p class="text-gray-500 mt-1">สวัสดี {{ auth()->user()->name }} มาเรียนต่อกันเถอะ</p>

        @if ($enrollments->isEmpty())
            <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 p-12 text-center">
                <x-icon name="book" class="text-5xl text-gray-300" />
                <p class="mt-3 font-semibold">ยังไม่มีคอร์สที่ลงเรียน</p>
                <p class="text-gray-500 text-sm mt-1">เริ่มเรียนรู้ทักษะใหม่วันนี้เลย</p>
                <a href="{{ route('courses.index') }}" class="mt-5 inline-block rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">เลือกคอร์สเรียน</a>
            </div>
        @else
            {{-- In progress --}}
            @if ($inProgress->isNotEmpty())
                <h2 class="mt-8 text-lg font-bold">กำลังเรียน ({{ $inProgress->count() }})</h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($inProgress as $enrollment)
                        @include('partials.enrollment-card', ['enrollment' => $enrollment])
                    @endforeach
                </div>
            @endif

            {{-- Completed --}}
            @if ($completed->isNotEmpty())
                <h2 class="mt-10 flex items-center gap-2 text-lg font-bold">
                    <x-icon name="trophy" class="text-amber-500" /> เรียนจบแล้ว ({{ $completed->count() }})
                </h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($completed as $enrollment)
                        @include('partials.enrollment-card', ['enrollment' => $enrollment])
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</x-site-layout>
