<x-site-layout title="องค์กรของฉัน">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">องค์กรของฉัน</h1>
                <p class="text-gray-500 mt-1">อบรมพนักงานด้วยการมอบหมายคอร์สและติดตามผล</p>
            </div>
            <a href="{{ route('org.create') }}" class="inline-flex items-center gap-2 rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                <i class="bi bi-plus-lg" aria-hidden="true"></i> สร้างองค์กร
            </a>
        </div>

        @if ($organizations->isEmpty())
            <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 p-12 text-center">
                <i class="bi bi-buildings text-5xl text-gray-300" aria-hidden="true"></i>
                <p class="mt-3 font-semibold">ยังไม่มีองค์กร</p>
                <p class="text-gray-500 text-sm mt-1">สร้างองค์กรเพื่อเริ่มอบรมทีมของคุณ</p>
            </div>
        @else
            <div class="mt-6 grid sm:grid-cols-2 gap-4">
                @foreach ($organizations as $org)
                    <a href="{{ route('org.show', $org) }}" class="rounded-2xl bg-white ring-1 ring-gray-200 p-5 hover:ring-indigo-300 hover:shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        <div class="flex items-center gap-3">
                            <span class="grid place-items-center h-11 w-11 rounded-xl bg-indigo-50 text-indigo-600 text-xl shrink-0"><i class="bi bi-buildings" aria-hidden="true"></i></span>
                            <div class="min-w-0">
                                <p class="font-semibold truncate">{{ $org->name }}</p>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $org->pivot->role === 'member' ? 'bg-gray-100 text-gray-600' : 'bg-violet-100 text-violet-700' }}">{{ ['owner' => 'เจ้าของ', 'manager' => 'ผู้จัดการ', 'member' => 'สมาชิก'][$org->pivot->role] }}</span>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1"><i class="bi bi-people" aria-hidden="true"></i> {{ $org->members_count }} สมาชิก</span>
                            <span class="flex items-center gap-1"><i class="bi bi-journal-check" aria-hidden="true"></i> {{ $org->assignments_count }} คอร์สที่มอบหมาย</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-site-layout>
