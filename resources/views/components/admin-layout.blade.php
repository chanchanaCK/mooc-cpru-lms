@props(['title' => 'ผู้ดูแลระบบ', 'active' => 'dashboard'])

@php
    $pendingTransfers = \App\Models\CreditTransferRequest::where('status', 'pending')->count();
    $nav = [
        ['key' => 'dashboard', 'route' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'label' => 'ภาพรวม'],
        ['key' => 'users', 'route' => 'admin.users.index', 'icon' => 'bi-people', 'label' => 'ผู้ใช้'],
        ['key' => 'courses', 'route' => 'admin.courses.index', 'icon' => 'bi-collection-play', 'label' => 'คอร์ส'],
        ['key' => 'programs', 'route' => 'admin.programs.index', 'icon' => 'bi-mortarboard', 'label' => 'หลักสูตร'],
        ['key' => 'qualifications', 'route' => 'admin.qualifications.index', 'icon' => 'bi-patch-check', 'label' => 'คุณวุฒิ'],
        ['key' => 'transfers', 'route' => 'admin.transfers.index', 'icon' => 'bi-arrow-left-right', 'label' => 'เทียบโอน', 'badge' => $pendingTransfers],
        ['key' => 'organizations', 'route' => 'admin.organizations.index', 'icon' => 'bi-buildings', 'label' => 'องค์กร'],
        ['key' => 'coupons', 'route' => 'admin.coupons.index', 'icon' => 'bi-tag', 'label' => 'คูปอง'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} · MoocLMS Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    {{-- Top bar --}}
    <header class="bg-gray-900 text-white">
        <div class="px-4 sm:px-6 flex h-14 items-center gap-4">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 shrink-0">
                <span class="grid place-items-center h-8 w-8 rounded-lg bg-indigo-600 font-bold">M</span>
                <span class="font-bold">MoocLMS <span class="text-indigo-400">Admin</span></span>
            </a>
            <div class="flex-1"></div>
            <a href="{{ route('home') }}" class="text-sm text-gray-300 hover:text-white inline-flex items-center gap-1.5"><i class="bi bi-box-arrow-up-right" aria-hidden="true"></i> <span class="hidden sm:inline">ดูเว็บไซต์</span></a>
            <span class="text-gray-600">|</span>
            <span class="text-sm text-gray-300">{{ auth()->user()->name }}</span>
        </div>
    </header>

    <div class="max-w-7xl mx-auto lg:grid lg:grid-cols-[220px_1fr] lg:gap-6 px-4 sm:px-6 py-6">
        {{-- Sidebar --}}
        <aside class="mb-4 lg:mb-0">
            <nav class="flex lg:flex-col gap-1 overflow-x-auto lg:overflow-visible">
                @foreach ($nav as $item)
                    <a href="{{ route($item['route']) }}"
                       class="inline-flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium whitespace-nowrap transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 {{ $active === $item['key'] ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                        <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i> {{ $item['label'] }}
                        @if (($item['badge'] ?? 0) > 0)
                            <span class="grid h-5 min-w-[1.25rem] place-items-center rounded-full bg-rose-500 px-1 text-xs font-semibold text-white {{ $active === $item['key'] ? 'ring-2 ring-white/40' : '' }}">{{ $item['badge'] }}</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- Content --}}
        <main class="min-w-0">
            @if (session('success') || session('error'))
                <div class="mb-4">
                    @if (session('success'))<div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>@endif
                    @if (session('error'))<div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>@endif
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
