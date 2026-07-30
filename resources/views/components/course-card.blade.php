@props(['course'])

<a href="{{ route('courses.show', $course) }}"
   class="group flex flex-col rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden hover:ring-indigo-300 hover:shadow-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
    <div class="relative aspect-video overflow-hidden bg-gray-100">
        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}"
             class="h-full w-full object-cover group-hover:scale-105 transition duration-300" loading="lazy">
        <span class="absolute top-2 left-2 rounded-full bg-white/90 backdrop-blur px-2.5 py-1 text-xs font-medium text-gray-700">
            {{ $course->level_label }}
        </span>
    </div>

    <div class="flex flex-1 flex-col p-4">
        @if ($course->category)
            <span class="text-xs font-medium text-indigo-600">{{ $course->category->name }}</span>
        @endif

        <h3 class="mt-1 font-semibold leading-snug line-clamp-2 group-hover:text-indigo-700">
            {{ $course->title }}
        </h3>

        <p class="mt-1 text-sm text-gray-500">{{ $course->instructor->name ?? 'ผู้สอน' }}</p>

        <div class="mt-2 flex items-center gap-1 text-sm">
            <span class="font-semibold text-amber-600">{{ number_format((float) $course->rating_avg, 1) }}</span>
            <div class="flex items-center gap-0.5 text-amber-400 text-xs">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="bi {{ $i <= round($course->rating_avg) ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i>
                @endfor
            </div>
            <span class="text-gray-400 text-xs">({{ $course->rating_count }})</span>
        </div>

        <div class="mt-3 flex items-center justify-between pt-3 border-t border-gray-100">
            <span class="flex items-center gap-1 text-xs text-gray-500"><x-icon name="users" class="text-sm" /> {{ number_format($course->students_count) }} ผู้เรียน</span>
            <span class="font-bold {{ $course->isFree() ? 'text-green-600' : 'text-gray-900' }}">{{ $course->price_label }}</span>
        </div>
    </div>
</a>
