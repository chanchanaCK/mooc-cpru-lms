<x-admin-layout title="คอร์ส" active="courses">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">คอร์ส ({{ $courses->total() }})</h1>
        <form method="GET" action="{{ route('admin.courses.index') }}" class="flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="ค้นหาคอร์ส" aria-label="ค้นหาคอร์ส"
                   class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <select name="status" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">ทุกสถานะ</option>
                <option value="published" @selected(request('status')==='published')>เผยแพร่</option>
                <option value="draft" @selected(request('status')==='draft')>ร่าง</option>
            </select>
            <button class="rounded-lg bg-gray-900 px-4 text-sm font-semibold text-white hover:bg-gray-800">ค้นหา</button>
        </form>
    </div>

    <div class="mt-5 space-y-3">
        @foreach ($courses as $course)
            <div class="flex flex-col sm:flex-row sm:items-center gap-4 rounded-2xl bg-white ring-1 ring-gray-200 p-3">
                <img src="{{ $course->thumbnail_url }}" alt="" class="h-16 w-28 rounded-lg object-cover shrink-0">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $course->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">{{ $course->status === 'published' ? 'เผยแพร่' : 'ร่าง' }}</span>
                        @if ($course->category)<span class="text-xs text-indigo-600">{{ $course->category->name }}</span>@endif
                    </div>
                    <p class="font-semibold truncate mt-0.5">{{ $course->title }}</p>
                    <p class="text-xs text-gray-500">โดย {{ $course->instructor->name ?? '—' }} · {{ number_format($course->students_count) }} ผู้เรียน · {{ $course->price_label }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('courses.show', $course) }}" target="_blank" aria-label="ดูคอร์ส" class="grid place-items-center h-9 w-9 rounded-lg ring-1 ring-gray-300 text-gray-600 hover:bg-gray-50"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></a>
                    <form method="POST" action="{{ route('admin.courses.publish', $course) }}">
                        @csrf
                        <button class="grid place-items-center h-9 w-9 rounded-lg ring-1 ring-gray-300 hover:bg-gray-50 {{ $course->status === 'published' ? 'text-amber-600' : 'text-green-600' }}" aria-label="{{ $course->status === 'published' ? 'เป็นร่าง' : 'เผยแพร่' }}" title="{{ $course->status === 'published' ? 'เป็นร่าง' : 'เผยแพร่' }}">
                            <i class="bi {{ $course->status === 'published' ? 'bi-eye-slash' : 'bi-globe' }}" aria-hidden="true"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" onsubmit="return confirm('ลบคอร์ส “{{ $course->title }}”?')">
                        @csrf @method('DELETE')
                        <button class="grid place-items-center h-9 w-9 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50" aria-label="ลบคอร์ส"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4">{{ $courses->links() }}</div>
</x-admin-layout>
