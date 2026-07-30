<x-admin-layout title="คุณวุฒิ" active="qualifications">
    <h1 class="text-2xl font-bold">ประเภทและระดับคุณวุฒิ</h1>
    <p class="text-gray-500 mt-1 text-sm">กำหนดรายการที่ใช้เลือกตอนสร้างหลักสูตรสะสมหน่วยกิต — เพิ่ม/แก้ไข/ลบได้</p>

    <div class="mt-5 grid lg:grid-cols-2 gap-5 items-start">
        {{-- Qualification types --}}
        <section class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
            <h2 class="font-bold"><i class="bi bi-patch-check text-indigo-600" aria-hidden="true"></i> ประเภทคุณวุฒิ ({{ $types->count() }})</h2>

            <form method="POST" action="{{ route('admin.qualifications.types.store') }}" class="mt-3 flex gap-2">
                @csrf
                <input type="text" name="name" required maxlength="120" placeholder="เช่น ประกาศนียบัตรบัณฑิต" aria-label="ชื่อประเภทคุณวุฒิ"
                       class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button class="rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">เพิ่ม</button>
            </form>
            @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror

            <ul class="mt-4 divide-y divide-gray-100">
                @foreach ($types as $type)
                    <li class="py-2.5">
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.qualifications.types.update', $type) }}" class="flex items-center gap-2 flex-1 min-w-0">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $type->name }}" required maxlength="120"
                                       class="flex-1 min-w-0 rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <label class="inline-flex items-center gap-1 text-xs text-gray-500 shrink-0" title="เปิด/ปิดการใช้งานในฟอร์มหลักสูตร">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($type->is_active) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    ใช้งาน
                                </label>
                                <button class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800 shrink-0">บันทึก</button>
                            </form>
                            <form method="POST" action="{{ route('admin.qualifications.types.destroy', $type) }}" onsubmit="return confirm('ลบประเภทนี้?')">
                                @csrf @method('DELETE')
                                <button class="grid place-items-center h-8 w-8 rounded-lg text-rose-600 hover:bg-rose-50" title="ลบ"><i class="bi bi-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                        <p class="mt-1 text-xs text-gray-400 font-mono">{{ $type->slug }} · ใช้ใน {{ $typeUsage[$type->slug] ?? 0 }} หลักสูตร @unless($type->is_active) · <span class="text-amber-600">ปิดใช้งาน</span> @endunless</p>
                    </li>
                @endforeach
            </ul>
        </section>

        {{-- Qualification levels --}}
        <section class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
            <h2 class="font-bold"><i class="bi bi-bar-chart-steps text-indigo-600" aria-hidden="true"></i> ระดับคุณวุฒิ ({{ $levels->count() }})</h2>

            <form method="POST" action="{{ route('admin.qualifications.levels.store') }}" class="mt-3 flex gap-2">
                @csrf
                <input type="number" name="level" required min="1" max="255" placeholder="ระดับ" aria-label="เลขระดับ"
                       class="w-20 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <input type="text" name="name" required maxlength="120" placeholder="เช่น ปริญญาตรี" aria-label="ชื่อระดับคุณวุฒิ"
                       class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button class="rounded-lg bg-indigo-600 px-4 text-sm font-semibold text-white hover:bg-indigo-700">เพิ่ม</button>
            </form>
            @error('level')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror

            <ul class="mt-4 divide-y divide-gray-100">
                @foreach ($levels as $level)
                    <li class="py-2.5">
                        <div class="flex items-center gap-2">
                            <span class="grid place-items-center h-8 w-8 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-bold shrink-0">{{ $level->level }}</span>
                            <form method="POST" action="{{ route('admin.qualifications.levels.update', $level) }}" class="flex items-center gap-2 flex-1 min-w-0">
                                @csrf @method('PUT')
                                <input type="text" name="name" value="{{ $level->name }}" required maxlength="120"
                                       class="flex-1 min-w-0 rounded-lg border-gray-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <button class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800 shrink-0">บันทึก</button>
                            </form>
                            <form method="POST" action="{{ route('admin.qualifications.levels.destroy', $level) }}" onsubmit="return confirm('ลบระดับนี้?')">
                                @csrf @method('DELETE')
                                <button class="grid place-items-center h-8 w-8 rounded-lg text-rose-600 hover:bg-rose-50" title="ลบ"><i class="bi bi-trash" aria-hidden="true"></i></button>
                            </form>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">ใช้ใน {{ $levelUsage[$level->level] ?? 0 }} หลักสูตร</p>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
</x-admin-layout>
