<x-site-layout title="สร้างคอร์สใหม่">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('studio.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> กลับสตูดิโอ</a>
        <h1 class="text-2xl font-bold mt-2">สร้างคอร์สใหม่</h1>

        <form method="POST" action="{{ route('studio.courses.store') }}" enctype="multipart/form-data"
              class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-6">
            @csrf
            @include('partials.studio-course-fields')

            <div class="mt-6 flex items-center gap-3">
                <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    สร้างคอร์ส & เพิ่มบทเรียน
                </button>
                <a href="{{ route('studio.index') }}" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</a>
            </div>
        </form>
    </div>
</x-site-layout>
