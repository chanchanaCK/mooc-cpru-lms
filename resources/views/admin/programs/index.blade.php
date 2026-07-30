@php use App\Models\CreditRecord; @endphp
<x-admin-layout title="หลักสูตร" active="programs">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">หลักสูตรสะสมหน่วยกิต ({{ $programs->count() }})</h1>
        <a href="{{ route('admin.programs.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"><i class="bi bi-plus-lg" aria-hidden="true"></i> สร้างหลักสูตร</a>
    </div>

    <div class="mt-5 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
        @if ($programs->isEmpty())
            <p class="p-10 text-center text-gray-400 text-sm">ยังไม่มีหลักสูตร — เริ่มสร้างหลักสูตรแรกได้เลย</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">หลักสูตร</th>
                            <th class="px-4 py-3 font-medium text-center">ประเภท</th>
                            <th class="px-4 py-3 font-medium text-center">หน่วยกิต</th>
                            <th class="px-4 py-3 font-medium text-center">วิชา</th>
                            <th class="px-4 py-3 font-medium text-center">ผู้เรียน</th>
                            <th class="px-4 py-3 font-medium text-center">สถานะ</th>
                            <th class="px-4 py-3 font-medium text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($programs as $program)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.programs.edit', $program) }}" class="font-medium hover:text-indigo-600">{{ $program->title }}</a>
                                    @if ($program->subtitle)<p class="text-xs text-gray-400 truncate max-w-xs">{{ $program->subtitle }}</p>@endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-500 whitespace-nowrap">{{ $program->type_label }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">{{ CreditRecord::fmt($program->required_credits) }}</td>
                                <td class="px-4 py-3 text-center text-gray-500">{{ $program->courses_count }}</td>
                                <td class="px-4 py-3 text-center text-gray-500">{{ $program->enrollments_count }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if ($program->status === 'published')
                                        <span class="inline-block rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs">เผยแพร่</span>
                                    @else
                                        <span class="inline-block rounded-full bg-gray-100 text-gray-500 px-2.5 py-0.5 text-xs">ฉบับร่าง</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('admin.programs.edit', $program) }}" class="grid place-items-center h-8 w-8 rounded-lg text-indigo-600 hover:bg-indigo-50" title="แก้ไข"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                        <form method="POST" action="{{ route('admin.programs.publish', $program) }}">
                                            @csrf
                                            <button type="submit" class="grid place-items-center h-8 w-8 rounded-lg text-gray-500 hover:bg-gray-100" title="{{ $program->status === 'published' ? 'ถอนการเผยแพร่' : 'เผยแพร่' }}">
                                                <i class="bi {{ $program->status === 'published' ? 'bi-eye-slash' : 'bi-broadcast' }}" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.programs.destroy', $program) }}" onsubmit="return confirm('ลบหลักสูตรนี้?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="grid place-items-center h-8 w-8 rounded-lg text-rose-600 hover:bg-rose-50" title="ลบ"><i class="bi bi-trash" aria-hidden="true"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-admin-layout>
