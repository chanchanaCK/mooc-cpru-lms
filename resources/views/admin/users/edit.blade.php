@php
    $roleLabel = ['admin' => 'แอดมิน', 'registrar' => 'นายทะเบียน', 'instructor' => 'ผู้สอน', 'student' => 'ผู้เรียน'];
    $roleBadge = ['admin' => 'bg-rose-100 text-rose-700', 'registrar' => 'bg-indigo-100 text-indigo-700', 'instructor' => 'bg-violet-100 text-violet-700', 'student' => 'bg-gray-100 text-gray-600'];
@endphp
<x-admin-layout :title="'ประวัติ · ' . $user->name" active="users">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> ผู้ใช้</a>

    <div class="mt-2 flex items-center gap-3">
        <h1 class="text-2xl font-bold">แก้ไขประวัติผู้ใช้</h1>
        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $roleBadge[$user->role] ?? 'bg-gray-100 text-gray-600' }}">{{ $roleLabel[$user->role] ?? $user->role }}</span>
    </div>
    <p class="text-gray-500 mt-1 text-sm">ประวัติจะแสดงในหน้าคอร์สที่ผู้สอนคนนี้เป็นเจ้าของ</p>

    <form method="POST" action="{{ route('admin.users.profile.update', $user) }}" class="mt-5 max-w-2xl rounded-2xl bg-white ring-1 ring-gray-200 p-6 space-y-5">
        @csrf @method('PUT')

        <div class="flex items-center gap-4">
            @if ($user->avatar)
                <img src="{{ $user->avatar }}" alt="" class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200">
            @else
                <span class="grid place-items-center h-16 w-16 rounded-full bg-indigo-100 text-indigo-700 text-2xl font-bold shrink-0">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
            @endif
            <div class="text-sm text-gray-500">
                <p>{{ $user->email }}</p>
                <p class="text-xs text-gray-400">สมัครเมื่อ {{ $user->created_at->format('d/m/Y') }}</p>
            </div>
        </div>

        <div>
            <label for="name" class="block text-sm font-medium mb-1">ชื่อ <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="headline" class="block text-sm font-medium mb-1">ตำแหน่ง / หัวเรื่อง (headline)</label>
            <input type="text" name="headline" id="headline" value="{{ old('headline', $user->headline) }}" placeholder="เช่น Senior UX/UI Designer"
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('headline')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="bio" class="block text-sm font-medium mb-1">ประวัติ / คำอธิบาย (bio)</label>
            <textarea name="bio" id="bio" rows="5" placeholder="ประวัติผู้สอน ประสบการณ์ ความเชี่ยวชาญ ฯลฯ"
                      class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('bio', $user->bio) }}</textarea>
            @error('bio')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="avatar" class="block text-sm font-medium mb-1">ลิงก์รูปโปรไฟล์ (URL)</label>
            <input type="url" name="avatar" id="avatar" value="{{ old('avatar', $user->avatar) }}" placeholder="https://..."
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">วางลิงก์รูปภาพ (ถ้ามี) — เว้นว่างไว้จะใช้อักษรย่อชื่อ</p>
            @error('avatar')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <a href="{{ route('admin.users.index') }}" class="rounded-lg px-5 py-2.5 text-sm font-semibold text-gray-600 hover:bg-gray-100">ยกเลิก</a>
            <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">บันทึกประวัติ</button>
        </div>
    </form>
</x-admin-layout>
