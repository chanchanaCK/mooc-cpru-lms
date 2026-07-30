<x-admin-layout title="ผู้ใช้" active="users">
    @php
        $roleBadge = ['admin' => 'bg-rose-100 text-rose-700', 'instructor' => 'bg-violet-100 text-violet-700', 'student' => 'bg-gray-100 text-gray-600'];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">ผู้ใช้ ({{ $users->total() }})</h1>
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="ค้นหาชื่อ/อีเมล" aria-label="ค้นหาผู้ใช้"
                   class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <select name="role" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">ทุกบทบาท</option>
                <option value="student" @selected(request('role')==='student')>ผู้เรียน</option>
                <option value="instructor" @selected(request('role')==='instructor')>ผู้สอน</option>
                <option value="admin" @selected(request('role')==='admin')>แอดมิน</option>
            </select>
            <button class="rounded-lg bg-gray-900 px-4 text-sm font-semibold text-white hover:bg-gray-800">ค้นหา</button>
        </form>
    </div>

    <div class="mt-5 rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">ผู้ใช้</th>
                        <th class="px-4 py-3 font-medium">คอร์ส/ลงเรียน</th>
                        <th class="px-4 py-3 font-medium">สมัครเมื่อ</th>
                        <th class="px-4 py-3 font-medium">บทบาท</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($users as $u)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="grid place-items-center h-9 w-9 rounded-full bg-indigo-100 text-indigo-700 font-semibold shrink-0">{{ mb_strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">{{ $u->name }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ $u->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                                <span title="คอร์สที่สอน"><i class="bi bi-easel2" aria-hidden="true"></i> {{ $u->courses_count }}</span>
                                <span class="ml-2" title="คอร์สที่เรียน"><i class="bi bi-mortarboard" aria-hidden="true"></i> {{ $u->enrollments_count }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $u->created_at->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                @if ($u->id === auth()->id())
                                    <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $roleBadge[$u->role] }}">{{ $u->role }} (คุณ)</span>
                                @else
                                    <form method="POST" action="{{ route('admin.users.role', $u) }}">
                                        @csrf @method('PUT')
                                        <select name="role" onchange="this.form.submit()" aria-label="เปลี่ยนบทบาทของ {{ $u->name }}"
                                                class="rounded-lg border-gray-300 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="student" @selected($u->role==='student')>ผู้เรียน</option>
                                            <option value="instructor" @selected($u->role==='instructor')>ผู้สอน</option>
                                            <option value="admin" @selected($u->role==='admin')>แอดมิน</option>
                                        </select>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</x-admin-layout>
