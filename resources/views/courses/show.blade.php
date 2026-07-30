<x-site-layout :title="$course->title">
    {{-- ===== Hero ===== --}}
    <section class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 lg:py-14 grid lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                @if ($course->category)
                    <a href="{{ route('courses.index', ['category' => $course->category->slug]) }}"
                       class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-300 hover:underline"><i class="bi {{ $course->category->icon }}" aria-hidden="true"></i> {{ $course->category->name }}</a>
                @endif
                <h1 class="mt-2 text-3xl sm:text-4xl font-bold leading-tight">{{ $course->title }}</h1>
                @if ($course->subtitle)
                    <p class="mt-3 text-lg text-gray-300">{{ $course->subtitle }}</p>
                @endif

                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-300">
                    <span class="flex items-center gap-1">
                        <span class="font-semibold text-amber-400">{{ number_format((float) $course->rating_avg, 1) }}</span>
                        <i class="bi bi-star-fill text-amber-400" aria-hidden="true"></i>
                        <span class="text-gray-400">({{ $course->rating_count }} รีวิว)</span>
                    </span>
                    <span class="flex items-center gap-1.5"><x-icon name="users" class="text-base" /> {{ number_format($course->students_count) }} ผู้เรียน</span>
                    <span class="flex items-center gap-1.5"><x-icon name="chart" class="text-base" /> {{ $course->level_label }}</span>
                    <span class="flex items-center gap-1.5"><x-icon name="film" class="text-base" /> {{ $course->lessons_count }} บทเรียน</span>
                </div>

                <p class="mt-4 text-sm text-gray-400">สอนโดย <span class="text-white font-medium">{{ $course->instructor->name }}</span></p>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid lg:grid-cols-3 gap-10">
        {{-- ===== Left: details ===== --}}
        <div class="lg:col-span-2 space-y-10">
            {{-- Description --}}
            @if ($course->description)
                <section>
                    <h2 class="text-xl font-bold mb-3">รายละเอียดคอร์ส</h2>
                    <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-line">{{ $course->description }}</div>
                </section>
            @endif

            {{-- Curriculum --}}
            <section>
                <h2 class="text-xl font-bold mb-3">เนื้อหาคอร์ส</h2>
                <p class="text-sm text-gray-500 mb-4">{{ $course->sections->count() }} บท · {{ $course->lessons_count }} บทเรียน · {{ $course->duration_label }}</p>

                <div class="space-y-3">
                    @foreach ($course->sections as $section)
                        <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="rounded-xl ring-1 ring-gray-200 bg-white overflow-hidden">
                            <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-indigo-500">
                                <span class="font-semibold">{{ $loop->iteration }}. {{ $section->title }}</span>
                                <span class="text-sm text-gray-400">{{ $section->lessons->count() }} บทเรียน</span>
                            </button>
                            <div x-show="open" x-cloak>
                                <ul class="divide-y divide-gray-100 border-t border-gray-100">
                                    @foreach ($section->lessons as $lesson)
                                        <li class="flex items-center justify-between px-4 py-3 text-sm">
                                            <span class="flex items-center gap-3">
                                                @if ($lesson->type === 'video')
                                                    <x-icon name="play" class="text-base text-gray-400 shrink-0" />
                                                @elseif ($lesson->type === 'quiz')
                                                    <x-icon name="question" class="text-base text-gray-400 shrink-0" />
                                                @else
                                                    <x-icon name="document" class="text-base text-gray-400 shrink-0" />
                                                @endif
                                                <span>{{ $lesson->title }}</span>
                                                @if ($lesson->is_preview)
                                                    <a href="{{ route('learn.lesson', [$course, $lesson]) }}" class="text-indigo-600 text-xs font-medium hover:underline">ดูตัวอย่าง</a>
                                                @endif
                                            </span>
                                            <span class="flex items-center gap-2 text-gray-400">
                                                @unless ($lesson->is_preview || $isEnrolled)
                                                    <x-icon name="lock" class="text-sm" />
                                                @endunless
                                                @if ($lesson->type === 'video')<span>{{ $lesson->duration_label }}</span>@endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Instructor --}}
            <section>
                <h2 class="text-xl font-bold mb-3">ผู้สอน</h2>
                <div class="flex items-start gap-4 rounded-xl ring-1 ring-gray-200 bg-white p-5">
                    <span class="grid place-items-center h-14 w-14 rounded-full bg-indigo-100 text-indigo-700 text-xl font-bold shrink-0">
                        {{ mb_strtoupper(mb_substr($course->instructor->name, 0, 1)) }}
                    </span>
                    <div>
                        <p class="font-semibold">{{ $course->instructor->name }}</p>
                        @if ($course->instructor->headline)<p class="text-sm text-indigo-600">{{ $course->instructor->headline }}</p>@endif
                        @if ($course->instructor->bio)<p class="mt-2 text-sm text-gray-600">{{ $course->instructor->bio }}</p>@endif
                    </div>
                </div>
            </section>

            {{-- Reviews --}}
            <section>
                <h2 class="text-xl font-bold mb-4">รีวิวจากผู้เรียน ({{ $course->rating_count }})</h2>

                @if ($isEnrolled)
                    <form method="POST" action="{{ route('reviews.store', $course) }}" class="rounded-xl ring-1 ring-gray-200 bg-white p-5 mb-6"
                          x-data="{ rating: 5 }">
                        @csrf
                        <p class="font-medium text-sm mb-2">ให้คะแนนคอร์สนี้</p>
                        <div class="flex gap-0.5 text-2xl mb-3">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}" aria-label="ให้ {{ $i }} ดาว"
                                        class="leading-none px-1 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                                    <i class="bi" :class="rating >= {{ $i }} ? 'bi-star-fill text-amber-400' : 'bi-star text-gray-300'" aria-hidden="true"></i>
                                </button>
                            @endfor
                            <input type="hidden" name="rating" :value="rating">
                        </div>
                        <textarea name="comment" rows="2" placeholder="เล่าประสบการณ์การเรียน (ไม่บังคับ)"
                                  class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        <button class="mt-3 rounded-full bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">ส่งรีวิว</button>
                    </form>
                @endif

                <div class="space-y-4">
                    @forelse ($course->reviews as $review)
                        <div class="rounded-xl ring-1 ring-gray-200 bg-white p-5">
                            <div class="flex items-center gap-3">
                                <span class="grid place-items-center h-9 w-9 rounded-full bg-gray-100 text-gray-600 text-sm font-semibold">
                                    {{ mb_strtoupper(mb_substr($review->user->name ?? '?', 0, 1)) }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium">{{ $review->user->name ?? 'ผู้เรียน' }}</p>
                                    <p class="flex items-center gap-0.5 text-amber-400 text-sm">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <i class="bi {{ $s <= $review->rating ? 'bi-star-fill' : 'bi-star text-gray-300' }}" aria-hidden="true"></i>
                                        @endfor
                                    </p>
                                </div>
                            </div>
                            @if ($review->comment)<p class="mt-3 text-sm text-gray-700">{{ $review->comment }}</p>@endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">ยังไม่มีรีวิว เป็นคนแรกที่รีวิวคอร์สนี้สิ!</p>
                    @endforelse
                </div>
            </section>
        </div>

        {{-- ===== Right: sticky enroll card ===== --}}
        <div class="lg:col-span-1">
            <div class="lg:sticky lg:top-24 rounded-2xl ring-1 ring-gray-200 bg-white overflow-hidden shadow-sm">
                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="aspect-video w-full object-cover">
                <div class="p-6">
                    <div class="text-3xl font-bold {{ $course->isFree() ? 'text-green-600' : '' }}">{{ $course->price_label }}</div>

                    <div class="mt-5 space-y-2">
                        @auth
                            @if ($isEnrolled)
                                <a href="{{ route('learn.show', $course) }}"
                                   class="flex items-center justify-center gap-2 w-full rounded-full bg-green-600 py-3 text-center font-semibold text-white hover:bg-green-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2">
                                    เข้าเรียนต่อ <x-icon name="arrow-right" class="text-sm" />
                                </a>
                            @elseif ($course->isFree())
                                <form method="POST" action="{{ route('enroll', $course) }}">
                                    @csrf
                                    <button class="w-full rounded-full bg-indigo-600 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">ลงทะเบียนเรียนฟรี</button>
                                </form>
                            @elseif ($inCart)
                                <a href="{{ route('cart.index') }}"
                                   class="flex items-center justify-center gap-2 w-full rounded-full bg-indigo-600 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                                    ไปที่ตะกร้า <x-icon name="arrow-right" class="text-sm" />
                                </a>
                                <p class="text-xs text-center text-green-600">คอร์สนี้อยู่ในตะกร้าแล้ว</p>
                            @else
                                <form method="POST" action="{{ route('cart.store', $course) }}">
                                    @csrf
                                    <button class="flex items-center justify-center gap-2 w-full rounded-full bg-indigo-600 py-3 font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                                        <x-icon name="cart" class="text-lg" /> เพิ่มลงตะกร้า
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="block w-full rounded-full bg-indigo-600 py-3 text-center font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                                {{ $course->isFree() ? 'เข้าสู่ระบบเพื่อเรียนฟรี' : 'เข้าสู่ระบบเพื่อซื้อ' }}
                            </a>
                        @endauth
                    </div>

                    <ul class="mt-6 space-y-2.5 text-sm text-gray-600">
                        <li class="flex items-center gap-2.5"><x-icon name="film" class="text-base text-gray-400 shrink-0" /> <span>{{ $course->lessons_count }} บทเรียน</span></li>
                        <li class="flex items-center gap-2.5"><x-icon name="clock" class="text-base text-gray-400 shrink-0" /> <span>{{ $course->duration_label }}</span></li>
                        <li class="flex items-center gap-2.5"><x-icon name="chart" class="text-base text-gray-400 shrink-0" /> <span>ระดับ{{ $course->level_label }}</span></li>
                        <li class="flex items-center gap-2.5"><x-icon name="device" class="text-base text-gray-400 shrink-0" /> <span>เรียนได้ทุกอุปกรณ์</span></li>
                        <li class="flex items-center gap-2.5"><x-icon name="award" class="text-base text-gray-400 shrink-0" /> <span>ใบประกาศเมื่อเรียนจบ</span></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
