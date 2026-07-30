<x-site-layout :title="$organization->name">
    @php
        $roleLabel = ['owner' => 'เจ้าของ', 'manager' => 'ผู้จัดการ', 'member' => 'สมาชิก'];
        $roleBadge = ['owner' => 'bg-indigo-100 text-indigo-700', 'manager' => 'bg-violet-100 text-violet-700', 'member' => 'bg-gray-100 text-gray-600'];
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('org.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> องค์กรของฉัน</a>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="grid place-items-center h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 text-2xl shrink-0"><i class="bi bi-buildings" aria-hidden="true"></i></span>
                <div>
                    <h1 class="text-2xl font-bold">{{ $organization->name }}</h1>
                    <p class="text-sm text-gray-500">{{ $organization->members->count() }}/{{ $organization->seats }} ที่นั่ง · {{ $organization->assignedCourses->count() }} คอร์สที่มอบหมาย</p>
                </div>
            </div>
            <a href="{{ route('org.report', $organization) }}" class="inline-flex items-center gap-2 rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-500 focus-visible:ring-offset-2">
                <i class="bi bi-bar-chart-line" aria-hidden="true"></i> รายงานผล
            </a>
        </div>

        <div class="mt-6 grid lg:grid-cols-2 gap-6">
            {{-- Members --}}
            <section class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <h2 class="font-bold">สมาชิก ({{ $organization->members->count() }})</h2>

                @if ($canManage)
                    <form method="POST" action="{{ route('org.members.store', $organization) }}" class="mt-3 flex flex-wrap gap-2">
                        @csrf
                        <input type="email" name="email" placeholder="อีเมลสมาชิก" required aria-label="อีเมลสมาชิก"
                               class="flex-1 min-w-[10rem] rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <select name="role" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="member">สมาชิก</option>
                            <option value="manager">ผู้จัดการ</option>
                        </select>
                        <button class="rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">เพิ่ม</button>
                    </form>
                @endif

                <ul class="mt-4 divide-y divide-gray-100">
                    @foreach ($organization->members->sortBy('user.name') as $member)
                        <li class="flex items-center gap-3 py-2.5">
                            <span class="grid place-items-center h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 font-semibold text-sm shrink-0">{{ mb_strtoupper(mb_substr($member->user->name ?? '?', 0, 1)) }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ $member->user->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $member->user->email ?? '' }}</p>
                            </div>
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $roleBadge[$member->role] }}">{{ $roleLabel[$member->role] }}</span>
                            @if ($canManage && $member->role !== 'owner')
                                <form method="POST" action="{{ route('org.members.destroy', [$organization, $member->user]) }}" onsubmit="return confirm('นำสมาชิกนี้ออก?')">
                                    @csrf @method('DELETE')
                                    <button class="grid place-items-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50" aria-label="นำออก"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Assigned courses --}}
            <section class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <h2 class="font-bold">คอร์สที่มอบหมาย ({{ $organization->assignedCourses->count() }})</h2>

                @if ($canManage)
                    <form method="POST" action="{{ route('org.assignments.store', $organization) }}" class="mt-3 flex flex-wrap gap-2">
                        @csrf
                        <select name="course_id" required aria-label="เลือกคอร์ส" class="flex-1 min-w-[10rem] rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— เลือกคอร์ส —</option>
                            @foreach ($assignableCourses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="due_at" aria-label="กำหนดส่ง" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button class="rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">มอบหมาย</button>
                    </form>
                    <p class="text-xs text-gray-400 mt-1">มอบหมายแล้วสมาชิกทุกคนจะถูกลงเรียนอัตโนมัติ</p>
                @endif

                <ul class="mt-4 divide-y divide-gray-100">
                    @forelse ($organization->assignedCourses as $course)
                        <li class="flex items-center gap-3 py-2.5">
                            <img src="{{ $course->thumbnail_url }}" alt="" class="h-10 w-16 rounded object-cover shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ $course->title }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $course->instructor->name ?? '' }}
                                    @if ($course->pivot->due_at) · กำหนดส่ง {{ \Illuminate\Support\Carbon::parse($course->pivot->due_at)->format('d/m/Y') }} @endif
                                </p>
                            </div>
                            @if ($canManage)
                                <form method="POST" action="{{ route('org.assignments.destroy', [$organization, $course]) }}" onsubmit="return confirm('ยกเลิกการมอบหมายคอร์สนี้?')">
                                    @csrf @method('DELETE')
                                    <button class="grid place-items-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50" aria-label="ยกเลิกมอบหมาย"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-400">ยังไม่มีคอร์สที่มอบหมาย</li>
                    @endforelse
                </ul>
            </section>
        </div>

        {{-- Assigned programs (หลักสูตรสะสมหน่วยกิต) --}}
        <section class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-5">
            <h2 class="font-bold"><i class="bi bi-mortarboard text-indigo-600" aria-hidden="true"></i> หลักสูตรที่มอบหมาย ({{ $organization->assignedPrograms->count() }})</h2>

            @if ($canManage)
                <form method="POST" action="{{ route('org.program-assignments.store', $organization) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf
                    <select name="program_id" required aria-label="เลือกหลักสูตร" class="flex-1 min-w-[12rem] rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— เลือกหลักสูตร —</option>
                        @foreach ($assignablePrograms as $program)
                            <option value="{{ $program->id }}">{{ $program->title }}</option>
                        @endforeach
                    </select>
                    <button class="rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">มอบหมาย</button>
                </form>
                <p class="text-xs text-gray-400 mt-1">มอบหมายแล้วสมาชิกทุกคนจะถูกลงทะเบียนหลักสูตรและสะสมหน่วยกิตอัตโนมัติ</p>
            @endif

            <ul class="mt-4 grid sm:grid-cols-2 gap-2">
                @forelse ($organization->assignedPrograms as $program)
                    <li class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3">
                        <span class="grid place-items-center h-9 w-9 rounded-lg bg-indigo-100 text-indigo-600 shrink-0"><i class="bi bi-mortarboard" aria-hidden="true"></i></span>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('programs.show', $program) }}" class="text-sm font-medium truncate hover:text-indigo-600">{{ $program->title }}</a>
                            <p class="text-xs text-gray-400">{{ \App\Models\CreditRecord::fmt($program->required_credits) }} หน่วยกิต</p>
                        </div>
                        @if ($canManage)
                            <form method="POST" action="{{ route('org.program-assignments.destroy', [$organization, $program]) }}" onsubmit="return confirm('ยกเลิกการมอบหมายหลักสูตรนี้?')">
                                @csrf @method('DELETE')
                                <button class="grid place-items-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50" aria-label="ยกเลิกมอบหมาย"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li class="py-3 text-sm text-gray-400">ยังไม่มีหลักสูตรที่มอบหมาย</li>
                @endforelse
            </ul>
        </section>
    </div>
</x-site-layout>
