@props(['course', 'enrollment' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $course->title }} · เรียน</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <header class="sticky top-0 z-40 bg-gray-900 text-white">
        <div class="px-4 sm:px-6 flex h-14 items-center gap-4">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 text-sm text-gray-300 hover:text-white shrink-0 rounded px-1 focus:outline-none focus-visible:ring-2 focus-visible:ring-white">
                <span aria-hidden="true">←</span><span class="hidden sm:inline">การเรียนของฉัน</span>
            </a>
            <span class="text-gray-600">|</span>
            <a href="{{ route('courses.show', $course) }}" class="font-medium truncate hover:text-indigo-300">{{ $course->title }}</a>

            <div class="flex-1"></div>

            @if ($enrollment)
                <div class="hidden sm:flex items-center gap-3 shrink-0">
                    <div class="w-32 h-2 rounded-full bg-gray-700 overflow-hidden">
                        <div class="h-full bg-green-500" style="width: {{ $enrollment->progress_percent }}%"></div>
                    </div>
                    <span class="text-sm text-gray-300">{{ $enrollment->progress_percent }}%</span>
                </div>
                @if ($enrollment->progress_percent >= 100)
                    <a href="{{ route('certificate.show', $course) }}" class="inline-flex items-center gap-1.5 rounded-full bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600 shrink-0"><i class="bi bi-award" aria-hidden="true"></i> ใบประกาศ</a>
                @endif
            @endif
        </div>
    </header>

    {{ $slot }}
</body>
</html>
