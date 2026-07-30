@php
    use App\Models\CreditRecord;
    $roleLabel = ['owner' => 'เจ้าของ', 'manager' => 'ผู้จัดการ', 'member' => 'สมาชิก'];
    $roleBadge = ['owner' => 'bg-indigo-100 text-indigo-700', 'manager' => 'bg-violet-100 text-violet-700', 'member' => 'bg-gray-100 text-gray-600'];
@endphp
<x-admin-layout :title="$organization->name" active="organizations">
    <a href="{{ route('admin.organizations.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> องค์กร</a>

    <div class="mt-2 flex items-center gap-3">
        <span class="grid place-items-center h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 text-2xl shrink-0"><i class="bi bi-buildings" aria-hidden="true"></i></span>
        <div>
            <h1 class="text-2xl font-bold">{{ $organization->name }}</h1>
            <p class="text-sm text-gray-500">เจ้าของ: {{ $organization->owner?->name ?? '—' }} · {{ $organization->members->count() }}/{{ $organization->seats }} ที่นั่ง</p>
        </div>
    </div>

    <div class="mt-6 grid lg:grid-cols-2 gap-5">
        {{-- Members --}}
        <section class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
            <h2 class="font-bold">สมาชิก ({{ $organization->members->count() }})</h2>
            <ul class="mt-3 divide-y divide-gray-100">
                @foreach ($organization->members->sortBy('user.name') as $member)
                    <li class="flex items-center gap-3 py-2.5">
                        <span class="grid place-items-center h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 font-semibold text-sm shrink-0">{{ mb_strtoupper(mb_substr($member->user->name ?? '?', 0, 1)) }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ $member->user->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $member->user->email ?? '' }}</p>
                        </div>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $roleBadge[$member->role] ?? 'bg-gray-100 text-gray-600' }}">{{ $roleLabel[$member->role] ?? $member->role }}</span>
                        <span class="text-xs text-gray-500 shrink-0" title="หน่วยกิตสะสม"><i class="bi bi-mortarboard" aria-hidden="true"></i> {{ CreditRecord::fmt($member->user?->total_credits ?? 0) }}</span>
                    </li>
                @endforeach
            </ul>
        </section>

        <div class="space-y-5">
            {{-- Assigned courses --}}
            <section class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <h2 class="font-bold">คอร์สที่มอบหมาย ({{ $organization->assignedCourses->count() }})</h2>
                <ul class="mt-3 divide-y divide-gray-100">
                    @forelse ($organization->assignedCourses as $course)
                        <li class="flex items-center gap-3 py-2.5">
                            <img src="{{ $course->thumbnail_url }}" alt="" class="h-9 w-14 rounded object-cover shrink-0">
                            <p class="text-sm font-medium truncate flex-1">{{ $course->title }}</p>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-400">ยังไม่มีคอร์สที่มอบหมาย</li>
                    @endforelse
                </ul>
            </section>

            {{-- Assigned programs --}}
            <section class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                <h2 class="font-bold"><i class="bi bi-mortarboard text-indigo-600" aria-hidden="true"></i> หลักสูตรที่มอบหมาย ({{ $organization->assignedPrograms->count() }})</h2>
                <ul class="mt-3 divide-y divide-gray-100">
                    @forelse ($organization->assignedPrograms as $program)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <a href="{{ route('admin.programs.edit', $program) }}" class="text-sm font-medium truncate hover:text-indigo-600">{{ $program->title }}</a>
                            <span class="text-xs text-gray-400 shrink-0">{{ CreditRecord::fmt($program->required_credits) }} นก.</span>
                        </li>
                    @empty
                        <li class="py-2 text-sm text-gray-400">ยังไม่มีหลักสูตรที่มอบหมาย</li>
                    @endforelse
                </ul>
            </section>
        </div>
    </div>
</x-admin-layout>
