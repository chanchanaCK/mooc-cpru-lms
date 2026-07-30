@php use App\Models\CreditRecord; @endphp
<x-site-layout title="จัดการหลักสูตร">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('registrar.dashboard') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> นายทะเบียน</a>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-bold">จัดการหลักสูตรสะสมหน่วยกิต</h1>
            <a href="{{ route('registrar.programs.create') }}" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="bi bi-plus-lg" aria-hidden="true"></i> สร้างหลักสูตร</a>
        </div>

        <div class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
            @if ($programs->isEmpty())
                <p class="p-10 text-center text-gray-400 text-sm">ยังไม่มีหลักสูตร — เริ่มสร้างหลักสูตรแรกได้เลย</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-gray-500 text-left">
                            <tr>
                                <th class="px-5 py-3 font-medium">หลักสูตร</th>
                                <th class="px-5 py-3 font-medium text-center">ประเภท</th>
                                <th class="px-5 py-3 font-medium text-center">หน่วยกิต</th>
                                <th class="px-5 py-3 font-medium text-center">วิชา</th>
                                <th class="px-5 py-3 font-medium text-center">ผู้เรียน</th>
                                <th class="px-5 py-3 font-medium text-center">สถานะ</th>
                                <th class="px-5 py-3 font-medium text-right">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($programs as $program)
                                <tr class="hover:bg-gray-50/60">
                                    <td class="px-5 py-3 font-medium">{{ $program->title }}</td>
                                    <td class="px-5 py-3 text-center text-gray-500">{{ $program->type_label }}</td>
                                    <td class="px-5 py-3 text-center">{{ CreditRecord::fmt($program->required_credits) }}</td>
                                    <td class="px-5 py-3 text-center text-gray-500">{{ $program->courses_count }}</td>
                                    <td class="px-5 py-3 text-center text-gray-500">{{ $program->enrollments_count }}</td>
                                    <td class="px-5 py-3 text-center">
                                        @if ($program->status === 'published')
                                            <span class="inline-block rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-0.5 text-xs">เผยแพร่</span>
                                        @else
                                            <span class="inline-block rounded-full bg-gray-100 text-gray-500 px-2.5 py-0.5 text-xs">ฉบับร่าง</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('registrar.programs.edit', $program) }}" class="rounded-lg px-2.5 py-1.5 text-indigo-600 hover:bg-indigo-50" title="แก้ไข"><i class="bi bi-pencil" aria-hidden="true"></i></a>
                                            <form method="POST" action="{{ route('registrar.programs.publish', $program) }}">
                                                @csrf
                                                <button type="submit" class="rounded-lg px-2.5 py-1.5 text-gray-500 hover:bg-gray-100" title="{{ $program->status === 'published' ? 'ถอนการเผยแพร่' : 'เผยแพร่' }}">
                                                    <i class="bi {{ $program->status === 'published' ? 'bi-eye-slash' : 'bi-broadcast' }}" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('registrar.programs.destroy', $program) }}" onsubmit="return confirm('ลบหลักสูตรนี้?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="rounded-lg px-2.5 py-1.5 text-rose-600 hover:bg-rose-50" title="ลบ"><i class="bi bi-trash" aria-hidden="true"></i></button>
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
    </div>
</x-site-layout>
