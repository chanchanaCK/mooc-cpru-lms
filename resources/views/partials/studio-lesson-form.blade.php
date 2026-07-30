@php $lesson = $lesson ?? null; @endphp

<form method="POST" action="{{ $action }}"
      x-data="{ type: '{{ $lesson->type ?? 'video' }}' }"
      class="rounded-xl bg-gray-50 ring-1 ring-gray-200 p-4 space-y-3">
    @csrf
    @if (($method ?? 'POST') === 'PUT') @method('PUT') @endif

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">ชื่อบทเรียน</label>
        <input type="text" name="title" value="{{ $lesson->title ?? '' }}" required
               class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="flex flex-wrap gap-3">
        <label class="text-xs font-medium text-gray-600">
            ประเภท
            <select name="type" x-model="type" class="mt-1 block rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="video">วิดีโอ</option>
                <option value="article">บทความ</option>
                <option value="quiz">แบบทดสอบ</option>
            </select>
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-600 self-end pb-1">
            <input type="checkbox" name="is_preview" value="1" @checked($lesson->is_preview ?? false)
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            ให้ดูตัวอย่างฟรี
        </label>
    </div>

    {{-- Video fields --}}
    <div x-show="type === 'video'" class="grid sm:grid-cols-3 gap-3">
        <label class="text-xs font-medium text-gray-600">
            แหล่งวิดีโอ
            <select name="video_provider" class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="youtube" @selected(($lesson->video_provider ?? 'youtube') === 'youtube')>YouTube</option>
                <option value="vimeo" @selected(($lesson->video_provider ?? '') === 'vimeo')>Vimeo</option>
            </select>
        </label>
        <label class="text-xs font-medium text-gray-600 sm:col-span-2">
            ลิงก์หรือรหัสวิดีโอ
            <input type="text" name="video" value="{{ $lesson->video_id ?? '' }}" placeholder="เช่น https://youtu.be/xxxx หรือรหัสวิดีโอ"
                   class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </label>
        <label class="text-xs font-medium text-gray-600">
            ความยาว (นาที)
            <input type="number" name="duration_minutes" min="0" value="{{ $lesson ? intdiv($lesson->duration_seconds, 60) : '' }}"
                   class="mt-1 block w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </label>
    </div>

    {{-- Article fields --}}
    <div x-show="type === 'article'" x-cloak>
        <label class="block text-xs font-medium text-gray-600 mb-1">เนื้อหาบทความ</label>
        <textarea name="content" rows="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $lesson->content ?? '' }}</textarea>
    </div>

    {{-- Quiz note --}}
    <div x-show="type === 'quiz'" x-cloak class="rounded-lg bg-indigo-50 text-indigo-800 text-sm px-3 py-2">
        บันทึกบทเรียนก่อน แล้วกด <span class="font-semibold">จัดการคำถาม</span> ที่รายการบทเรียนเพื่อเพิ่มคำถาม
    </div>

    <div class="flex items-center gap-2">
        <button class="rounded-full bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">บันทึก</button>
        <button type="button" @click="{{ $cancel ?? '' }}" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</button>
    </div>
</form>
