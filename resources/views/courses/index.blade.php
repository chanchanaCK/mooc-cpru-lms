<x-site-layout title="คอร์สทั้งหมด">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold">คอร์สทั้งหมด</h1>
        <p class="text-gray-500 mt-1">{{ $courses->total() }} คอร์ส</p>

        <form method="GET" action="{{ route('courses.index') }}" id="filterForm" class="mt-6 grid grid-cols-1 lg:grid-cols-4 gap-8">
            {{-- Sidebar filters --}}
            <aside class="lg:col-span-1 space-y-6">
                <div>
                    <div class="relative">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="ค้นหา..." aria-label="ค้นหาคอร์ส"
                               class="w-full rounded-lg border-gray-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <i class="bi bi-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" aria-hidden="true"></i>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-sm mb-2">หมวดหมู่</h3>
                    <div class="space-y-1 text-sm">
                        <a href="{{ route('courses.index', array_merge(request()->except('category', 'page'), [])) }}"
                           class="block px-2 py-1 rounded {{ ! request('category') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">ทั้งหมด</a>
                        @foreach ($categories as $category)
                            <a href="{{ route('courses.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                               class="flex items-center gap-2 px-2 py-1 rounded {{ request('category') === $category->slug ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                <i class="bi {{ $category->icon }}" aria-hidden="true"></i> {{ $category->name }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-sm mb-2">ระดับ</h3>
                    <select name="level" onchange="document.getElementById('filterForm').submit()"
                            class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">ทุกระดับ</option>
                        <option value="beginner" @selected(request('level')==='beginner')>เริ่มต้น</option>
                        <option value="intermediate" @selected(request('level')==='intermediate')>ปานกลาง</option>
                        <option value="advanced" @selected(request('level')==='advanced')>ขั้นสูง</option>
                    </select>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="free" value="1" @checked(request()->boolean('free'))
                           onchange="document.getElementById('filterForm').submit()"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    เฉพาะคอร์สฟรี
                </label>

                @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
            </aside>

            {{-- Results --}}
            <div class="lg:col-span-3">
                <div class="flex items-center justify-end mb-4">
                    <select name="sort" onchange="document.getElementById('filterForm').submit()"
                            class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">ยอดนิยม</option>
                        <option value="newest" @selected(request('sort')==='newest')>มาใหม่</option>
                        <option value="rating" @selected(request('sort')==='rating')>คะแนนสูงสุด</option>
                        <option value="price_low" @selected(request('sort')==='price_low')>ราคาต่ำ–สูง</option>
                        <option value="price_high" @selected(request('sort')==='price_high')>ราคาสูง–ต่ำ</option>
                    </select>
                </div>

                @if ($courses->isEmpty())
                    <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-12 text-center text-gray-500">
                        ไม่พบคอร์สที่ตรงกับเงื่อนไข ลองปรับตัวกรองดูนะ
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach ($courses as $course)
                            <x-course-card :course="$course" />
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $courses->links() }}</div>
                @endif
            </div>
        </form>
    </div>
</x-site-layout>
