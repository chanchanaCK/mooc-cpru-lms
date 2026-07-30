@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' · ' : '' }}{{ config('app.name', 'MoocLMS') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        {{-- ===== Navbar ===== --}}
        <nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center gap-4">
                    {{-- Brand --}}
                    <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                        <span class="grid place-items-center h-9 w-9 rounded-lg bg-indigo-600 text-white font-bold">M</span>
                        <span class="text-lg font-bold tracking-tight hidden sm:block">Mooc<span class="text-indigo-600">LMS</span></span>
                    </a>

                    {{-- Search --}}
                    <form action="{{ route('courses.index') }}" method="GET" class="flex-1 max-w-lg hidden md:block">
                        <div class="relative">
                            <input type="search" name="q" value="{{ request('q') }}"
                                   placeholder="ค้นหาคอร์สที่อยากเรียน..." aria-label="ค้นหาคอร์ส"
                                   class="w-full rounded-full border-gray-300 bg-gray-50 pl-11 pr-4 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
                        </div>
                    </form>

                    <a href="{{ route('courses.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 hidden lg:block">คอร์สทั้งหมด</a>

                    <div class="flex-1 md:flex-none"></div>

                    {{-- Auth area --}}
                    @auth
                        @php $cartCount = auth()->user()->cartItems()->count(); @endphp
                        @if (auth()->user()->isInstructor())
                            <a href="{{ route('studio.index') }}" class="items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-indigo-600 hidden sm:inline-flex"><i class="bi bi-easel2" aria-hidden="true"></i> สอน</a>
                        @endif
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 hidden sm:block">การเรียนของฉัน</a>

                        <a href="{{ route('cart.index') }}" aria-label="ตะกร้า ({{ $cartCount }} รายการ)"
                           class="relative grid place-items-center h-10 w-10 rounded-full hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                            <i class="bi bi-cart3 text-gray-600 text-xl" aria-hidden="true"></i>
                            @if ($cartCount > 0)
                                <span class="absolute -top-0.5 -right-0.5 grid place-items-center h-5 min-w-[1.25rem] px-1 rounded-full bg-indigo-600 text-white text-[11px] font-semibold">{{ $cartCount }}</span>
                            @endif
                        </a>

                        <div class="relative" x-data="{ menu: false }">
                            <button @click="menu = !menu" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 hover:bg-gray-100">
                                <span class="grid place-items-center h-8 w-8 rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold">
                                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                                </span>
                                <i class="bi bi-chevron-down text-gray-400 text-xs" aria-hidden="true"></i>
                            </button>
                            <div x-show="menu" @click.outside="menu = false" x-transition x-cloak
                                 class="absolute right-0 mt-2 w-52 rounded-xl bg-white shadow-lg ring-1 ring-black/5 py-1 text-sm">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="font-medium truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-gray-500 text-xs truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ route('dashboard') }}" class="block px-4 py-2 hover:bg-gray-50">การเรียนของฉัน</a>
                                @if (auth()->user()->isInstructor())
                                    <a href="{{ route('studio.index') }}" class="block px-4 py-2 hover:bg-gray-50">สตูดิโอผู้สอน</a>
                                @endif
                                @if (auth()->user()->isAdmin())
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-gray-50 text-rose-600">แผงผู้ดูแลระบบ</a>
                                @endif
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 hover:bg-gray-50">คำสั่งซื้อของฉัน</a>
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-50">โปรไฟล์</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-red-600 hover:bg-red-50">ออกจากระบบ</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="rounded-full px-2 py-1 text-sm font-medium text-gray-600 hover:text-indigo-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">เข้าสู่ระบบ</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">สมัครสมาชิก</a>
                    @endauth
                </div>
            </div>
        </nav>

        {{-- ===== Flash messages ===== --}}
        @if (session('success') || session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
                @if (session('success'))
                    <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
                @endif
            </div>
        @endif

        {{-- ===== Page ===== --}}
        <main class="flex-1">
            {{ $slot }}
        </main>

        {{-- ===== Footer ===== --}}
        <footer class="border-t border-gray-200 bg-white mt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 text-sm text-gray-500 flex flex-col sm:flex-row justify-between gap-4">
                <p>© {{ date('Y') }} MoocLMS — ตัวอย่างระบบ LMS สไตล์ marketplace</p>
                <div class="flex gap-6">
                    <a href="{{ route('courses.index') }}" class="hover:text-indigo-600">คอร์สทั้งหมด</a>
                    <a href="#" class="hover:text-indigo-600">สอนกับเรา</a>
                    <a href="#" class="hover:text-indigo-600">เกี่ยวกับ</a>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
