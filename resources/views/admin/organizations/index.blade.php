<x-admin-layout title="องค์กร" active="organizations">
    <h1 class="text-2xl font-bold">องค์กร (B2B) ({{ $organizations->count() }})</h1>
    <p class="text-gray-500 mt-1 text-sm">ภาพรวมองค์กรที่ใช้ระบบ มอบหมายคอร์ส/หลักสูตรให้พนักงาน</p>

    <div class="mt-5 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
        @if ($organizations->isEmpty())
            <p class="p-10 text-center text-gray-400 text-sm">ยังไม่มีองค์กร</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">องค์กร</th>
                            <th class="px-4 py-3 font-medium">เจ้าของ</th>
                            <th class="px-4 py-3 font-medium text-center">สมาชิก</th>
                            <th class="px-4 py-3 font-medium text-center">คอร์ส</th>
                            <th class="px-4 py-3 font-medium text-center">หลักสูตร</th>
                            <th class="px-4 py-3 font-medium text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($organizations as $org)
                            <tr class="hover:bg-gray-50/60">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="grid place-items-center h-9 w-9 rounded-xl bg-indigo-50 text-indigo-600 shrink-0"><i class="bi bi-buildings" aria-hidden="true"></i></span>
                                        <a href="{{ route('admin.organizations.show', $org) }}" class="font-medium hover:text-indigo-600">{{ $org->name }}</a>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $org->owner?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-center text-gray-500">{{ $org->members_count }}/{{ $org->seats }}</td>
                                <td class="px-4 py-3 text-center text-gray-500">{{ $org->assignments_count }}</td>
                                <td class="px-4 py-3 text-center text-gray-500">{{ $org->program_assignments_count }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.organizations.show', $org) }}" class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-indigo-600 hover:bg-indigo-50 text-xs font-semibold">ดูรายละเอียด <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-admin-layout>
