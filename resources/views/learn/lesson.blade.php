<x-learn-layout :course="$course" :enrollment="$enrollment">
    @php
        $isDone = in_array($lesson->id, $completedIds, true);
        $nextLesson = $course->lessons()->where('sort_order', '>', $lesson->sort_order)->orderBy('sort_order')->first();
    @endphp

    <div class="grid lg:grid-cols-[1fr_360px] min-h-[calc(100vh-3.5rem)]">
        {{-- ===== Main ===== --}}
        <div class="min-w-0">
            {{-- Video / content / quiz --}}
            @if ($lesson->isQuiz())
                @include('partials.quiz-player')
            @elseif ($lesson->type === 'article')
                <div class="bg-white">
                    <div class="max-w-3xl mx-auto p-8 prose prose-sm sm:prose max-w-none whitespace-pre-line">{{ $lesson->content }}</div>
                </div>
            @else
                <div class="bg-black">
                    @if ($lesson->embed_url)
                        <div class="aspect-video max-h-[70vh] mx-auto">
                            <iframe src="{{ $lesson->embed_url }}" class="h-full w-full" title="วิดีโอ: {{ $lesson->title }}" allowfullscreen
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                        </div>
                    @else
                        <div class="aspect-video max-h-[70vh] grid place-items-center text-gray-500">
                            <p>ยังไม่มีสื่อสำหรับบทเรียนนี้</p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Lesson info + actions --}}
            <div class="p-5 sm:p-8 max-w-3xl">
                <p class="text-sm text-indigo-600 font-medium">{{ $lesson->section->title ?? '' }}</p>
                <h1 class="mt-1 text-2xl font-bold">{{ $lesson->title }}</h1>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    @if ($enrolled)
                        @unless ($lesson->isQuiz())
                            <form method="POST" action="{{ route('learn.complete', [$course, $lesson]) }}">
                                @csrf
                                @if ($isDone)
                                    <button class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-5 py-2.5 text-sm font-semibold text-gray-500 hover:bg-gray-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2">
                                        <x-icon name="undo" class="text-base" /> ยกเลิกเรียนจบ
                                    </button>
                                @else
                                    <button class="inline-flex items-center gap-2 rounded-full bg-green-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-green-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2">
                                        <x-icon name="check" class="text-base" /> ทำเครื่องหมายว่าเรียนจบ
                                    </button>
                                @endif
                            </form>
                        @endunless
                        @if ($isDone)
                            <span class="inline-flex items-center gap-1 text-sm text-green-600 font-medium"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> เรียนจบบทนี้แล้ว</span>
                        @endif
                    @else
                        <div class="rounded-xl bg-indigo-50 text-indigo-800 px-4 py-3 text-sm">
                            นี่คือบทเรียนตัวอย่าง —
                            <a href="{{ route('courses.show', $course) }}" class="font-semibold underline">ลงทะเบียนเรียน</a>
                            เพื่อปลดล็อกทั้งคอร์ส
                        </div>
                    @endif

                    @if ($nextLesson)
                        <a href="{{ route('learn.lesson', [$course, $nextLesson]) }}"
                           class="inline-flex items-center gap-2 rounded-full ring-1 ring-gray-300 px-5 py-2.5 text-sm font-semibold hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                            บทถัดไป <x-icon name="arrow-right" class="text-sm" />
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- ===== Sidebar: curriculum ===== --}}
        <aside class="bg-white lg:border-l border-gray-200 lg:h-[calc(100vh-3.5rem)] lg:overflow-y-auto">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-bold">เนื้อหาคอร์ส</h2>
                <p class="text-xs text-gray-500 mt-0.5">{{ count($completedIds) }}/{{ $course->lessons_count }} บทเรียนเรียนจบแล้ว</p>
            </div>

            @foreach ($course->sections as $section)
                <div class="border-b border-gray-100">
                    <div class="px-4 py-2.5 bg-gray-50 text-sm font-semibold">{{ $loop->iteration }}. {{ $section->title }}</div>
                    <ul>
                        @foreach ($section->lessons as $item)
                            @php
                                $done = in_array($item->id, $completedIds, true);
                                $current = $item->id === $lesson->id;
                                $accessible = $enrolled || $item->is_preview;
                            @endphp
                            <li>
                                @if ($accessible)
                                    <a href="{{ route('learn.lesson', [$course, $item]) }}" @if($current) aria-current="true" @endif
                                       class="flex items-center gap-3 px-4 py-3 text-sm hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500 {{ $current ? 'bg-indigo-50 border-l-2 border-indigo-600' : '' }}">
                                        <span class="shrink-0">
                                            @if ($done)
                                                <span class="grid place-items-center h-5 w-5 rounded-full bg-green-500 text-white"><x-icon name="check" class="text-xs" /></span>
                                            @else
                                                <span class="grid place-items-center h-5 w-5 rounded-full ring-1 ring-gray-300 text-gray-400"><x-icon :name="$item->type === 'video' ? 'play' : ($item->type === 'quiz' ? 'question' : 'document')" class="text-xs" /></span>
                                            @endif
                                        </span>
                                        <span class="flex-1 {{ $current ? 'font-semibold text-indigo-700' : 'text-gray-700' }}">{{ $item->title }}</span>
                                        @if ($item->type === 'video')<span class="text-xs text-gray-400">{{ $item->duration_label }}</span>@endif
                                    </a>
                                @else
                                    <div class="flex items-center gap-3 px-4 py-3 text-sm text-gray-400">
                                        <span class="grid place-items-center h-5 w-5 shrink-0"><x-icon name="lock" class="text-sm" /></span>
                                        <span class="flex-1">{{ $item->title }}</span>
                                        @if ($item->type === 'video')<span class="text-xs">{{ $item->duration_label }}</span>@endif
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </aside>
    </div>
</x-learn-layout>
