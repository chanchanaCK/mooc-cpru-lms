<x-site-layout :title="'เนื้อหา: ' . $course->title">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('studio.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> กลับสตูดิโอ</a>

        <div class="mt-2 flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $course->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $course->status === 'published' ? 'เผยแพร่แล้ว' : 'ฉบับร่าง' }}
                    </span>
                    <span class="text-sm text-gray-500">{{ $course->lessons_count }} บทเรียน · {{ $course->duration_label }}</span>
                </div>
                <h1 class="mt-1 text-2xl font-bold">{{ $course->title }}</h1>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('studio.courses.edit', $course) }}" class="inline-flex items-center gap-1.5 rounded-full ring-1 ring-gray-300 px-4 py-1.5 font-semibold hover:bg-gray-50"><i class="bi bi-pencil" aria-hidden="true"></i> ข้อมูลคอร์ส</a>
                <form method="POST" action="{{ route('studio.courses.publish', $course) }}">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 font-semibold focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 {{ $course->status === 'published' ? 'ring-1 ring-gray-300 text-gray-600 hover:bg-gray-50' : 'bg-green-600 text-white hover:bg-green-700' }}">
                        <i class="bi {{ $course->status === 'published' ? 'bi-eye-slash' : 'bi-globe' }}" aria-hidden="true"></i>
                        {{ $course->status === 'published' ? 'เป็นร่าง' : 'เผยแพร่' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Sections --}}
        <div class="mt-8 space-y-4">
            @forelse ($course->sections as $section)
                <div x-data="{ editSection: false, addLesson: false }" class="rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
                    {{-- Section header --}}
                    <div class="flex items-center justify-between gap-3 px-5 py-3 bg-gray-50 border-b border-gray-100">
                        <template x-if="!editSection">
                            <h2 class="font-semibold">{{ $loop->iteration }}. {{ $section->title }}</h2>
                        </template>
                        <div class="flex items-center gap-1 text-sm shrink-0">
                            <button type="button" @click="editSection = !editSection" aria-label="แก้ไขบท" class="grid place-items-center h-8 w-8 rounded-lg text-gray-500 hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                            <form method="POST" action="{{ route('studio.sections.destroy', $section) }}" onsubmit="return confirm('ลบบทนี้และบทเรียนทั้งหมดในบท?')">
                                @csrf @method('DELETE')
                                <button aria-label="ลบบท" class="grid place-items-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    </div>

                    {{-- Rename section --}}
                    <div x-show="editSection" x-cloak class="px-5 py-3 border-b border-gray-100">
                        <form method="POST" action="{{ route('studio.sections.update', $section) }}" class="flex gap-2">
                            @csrf @method('PUT')
                            <input type="text" name="title" value="{{ $section->title }}" required class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button class="rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">บันทึก</button>
                            <button type="button" @click="editSection = false" class="text-sm text-gray-500 px-2">ยกเลิก</button>
                        </form>
                    </div>

                    {{-- Lessons --}}
                    <ul class="divide-y divide-gray-100">
                        @forelse ($section->lessons as $lesson)
                            <li x-data="{ editLesson: false }" class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <x-icon :name="$lesson->type === 'video' ? 'play' : ($lesson->type === 'quiz' ? 'question' : 'document')" class="text-base text-gray-400 shrink-0" />
                                    <span class="flex-1 text-sm">{{ $lesson->title }}</span>
                                    @if ($lesson->type === 'quiz')
                                        <a href="{{ route('studio.lessons.quiz.edit', $lesson) }}" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:underline"><i class="bi bi-patch-question" aria-hidden="true"></i> จัดการคำถาม ({{ $lesson->questions_count }})</a>
                                    @endif
                                    @if ($lesson->is_preview)<span class="rounded-full bg-indigo-50 text-indigo-600 px-2 py-0.5 text-xs">ตัวอย่าง</span>@endif
                                    @if ($lesson->type === 'video')<span class="text-xs text-gray-400">{{ $lesson->duration_label }}</span>@endif
                                    <button type="button" @click="editLesson = !editLesson" aria-label="แก้ไขบทเรียน" class="grid place-items-center h-8 w-8 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                    <form method="POST" action="{{ route('studio.lessons.destroy', $lesson) }}" onsubmit="return confirm('ลบบทเรียนนี้?')">
                                        @csrf @method('DELETE')
                                        <button aria-label="ลบบทเรียน" class="grid place-items-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                    </form>
                                </div>
                                <div x-show="editLesson" x-cloak class="mt-3">
                                    @include('partials.studio-lesson-form', ['action' => route('studio.lessons.update', $lesson), 'method' => 'PUT', 'lesson' => $lesson, 'cancel' => 'editLesson = false'])
                                </div>
                            </li>
                        @empty
                            <li class="px-5 py-4 text-sm text-gray-400">ยังไม่มีบทเรียนในบทนี้</li>
                        @endforelse
                    </ul>

                    {{-- Add lesson --}}
                    <div class="px-5 py-3 border-t border-gray-100">
                        <button type="button" @click="addLesson = !addLesson" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-700 focus:outline-none focus-visible:underline">
                            <i class="bi bi-plus-circle" aria-hidden="true"></i> เพิ่มบทเรียน
                        </button>
                        <div x-show="addLesson" x-cloak class="mt-3">
                            @include('partials.studio-lesson-form', ['action' => route('studio.lessons.store', $section), 'method' => 'POST', 'lesson' => null, 'cancel' => 'addLesson = false'])
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-10 text-center">
                    <i class="bi bi-collection text-4xl text-gray-300" aria-hidden="true"></i>
                    <p class="mt-2 font-semibold">ยังไม่มีบท</p>
                    <p class="text-sm text-gray-500">เริ่มด้วยการเพิ่มบทแรกด้านล่าง</p>
                </div>
            @endforelse
        </div>

        {{-- Add section --}}
        <form method="POST" action="{{ route('studio.sections.store', $course) }}" class="mt-4 flex gap-2">
            @csrf
            <input type="text" name="title" placeholder="ชื่อบทใหม่ (เช่น บทที่ 1: บทนำ)" required
                   class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button class="inline-flex items-center gap-1.5 rounded-lg bg-gray-900 px-5 text-sm font-semibold text-white hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> เพิ่มบท
            </button>
        </form>
    </div>
</x-site-layout>
