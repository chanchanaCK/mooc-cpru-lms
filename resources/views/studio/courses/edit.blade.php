<x-site-layout :title="'แก้ไข: ' . $course->title">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('studio.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> กลับสตูดิโอ</a>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold">ข้อมูลคอร์ส</h1>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('studio.courses.curriculum', $course) }}" class="inline-flex items-center gap-1.5 rounded-full ring-1 ring-gray-300 px-4 py-1.5 font-semibold hover:bg-gray-50"><i class="bi bi-list-check" aria-hidden="true"></i> จัดการเนื้อหา</a>
                @if ($course->status === 'published')
                    <a href="{{ route('courses.show', $course) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-full ring-1 ring-gray-300 px-4 py-1.5 font-semibold hover:bg-gray-50"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> ดูหน้าคอร์ส</a>
                @endif
            </div>
        </div>

        <form method="POST" action="{{ route('studio.courses.update', $course) }}" enctype="multipart/form-data"
              class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-6">
            @csrf @method('PUT')
            @include('partials.studio-course-fields')

            <div class="mt-6 flex items-center gap-3">
                <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    บันทึกการเปลี่ยนแปลง
                </button>
            </div>
        </form>

        {{-- Danger zone --}}
        <div class="mt-8 rounded-2xl ring-1 ring-red-200 bg-red-50 p-6" x-data="{ confirm: false }">
            <h2 class="font-semibold text-red-800">ลบคอร์ส</h2>
            <p class="text-sm text-red-700 mt-1">การลบจะซ่อนคอร์สออกจากระบบ (ผู้เรียนเดิมจะเข้าถึงไม่ได้)</p>
            <button type="button" @click="confirm = true" x-show="!confirm"
                    class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">
                <i class="bi bi-trash3" aria-hidden="true"></i> ลบคอร์สนี้
            </button>
            <div x-show="confirm" x-cloak class="mt-3 flex items-center gap-2">
                <span class="text-sm text-red-800 font-medium">แน่ใจไหม?</span>
                <form method="POST" action="{{ route('studio.courses.destroy', $course) }}">
                    @csrf @method('DELETE')
                    <button class="rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">ยืนยันลบ</button>
                </form>
                <button type="button" @click="confirm = false" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</button>
            </div>
        </div>
    </div>
</x-site-layout>
