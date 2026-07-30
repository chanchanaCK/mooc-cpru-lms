@php $course = $enrollment->course; @endphp

<div class="flex flex-col rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
    <a href="{{ route('learn.show', $course) }}" class="relative aspect-video overflow-hidden bg-gray-100">
        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="h-full w-full object-cover">
        @if ($enrollment->progress_percent >= 100)
            <span class="absolute top-2 right-2 inline-flex items-center gap-1 rounded-full bg-green-600 text-white px-2.5 py-1 text-xs font-semibold">เรียนจบ <i class="bi bi-check-lg" aria-hidden="true"></i></span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-4">
        @if ($course->category)
            <span class="text-xs font-medium text-indigo-600">{{ $course->category->name }}</span>
        @endif
        <h3 class="mt-1 font-semibold leading-snug line-clamp-2">{{ $course->title }}</h3>
        <p class="text-sm text-gray-500 mt-0.5">{{ $course->instructor->name ?? '' }}</p>

        <div class="mt-auto pt-4">
            <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                <span>ความคืบหน้า</span>
                <span class="font-semibold text-gray-700">{{ $enrollment->progress_percent }}%</span>
            </div>
            <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                <div class="h-full bg-green-500 transition-all" style="width: {{ $enrollment->progress_percent }}%"></div>
            </div>
            @if ($enrollment->progress_percent >= 100)
                <a href="{{ route('certificate.show', $course) }}"
                   class="mt-3 flex items-center justify-center gap-2 w-full rounded-full bg-amber-500 py-2 text-center text-sm font-semibold text-white hover:bg-amber-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2">
                    <i class="bi bi-award" aria-hidden="true"></i> รับใบประกาศนียบัตร
                </a>
                <a href="{{ route('learn.show', $course) }}" class="mt-2 block text-center text-xs text-gray-500 hover:text-indigo-600">ทบทวนบทเรียน</a>
            @else
                <a href="{{ route('learn.show', $course) }}"
                   class="mt-3 block w-full rounded-full bg-indigo-600 py-2 text-center text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    {{ $enrollment->progress_percent > 0 ? 'เรียนต่อ' : 'เริ่มเรียน' }}
                </a>
            @endif
        </div>
    </div>
</div>
