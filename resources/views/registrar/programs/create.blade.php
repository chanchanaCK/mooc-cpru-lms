<x-site-layout title="สร้างหลักสูตร">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('registrar.programs.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> จัดการหลักสูตร</a>
        <h1 class="mt-2 text-2xl font-bold">สร้างหลักสูตรใหม่</h1>
        <p class="text-gray-500 mt-1 text-sm">กรอกข้อมูลหลักสูตร แล้วจึงเพิ่มรายวิชาในขั้นตอนถัดไป</p>

        <form method="POST" action="{{ route('registrar.programs.store') }}" class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-6">
            @csrf
            @include('partials.registrar-program-fields')
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('registrar.programs.index') }}" class="rounded-full px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-100">ยกเลิก</a>
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">สร้างหลักสูตร</button>
            </div>
        </form>
    </div>
</x-site-layout>
