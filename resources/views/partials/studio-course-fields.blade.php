@php $course = $course ?? null; @endphp

<div class="space-y-5">
    <div>
        <label for="title" class="block text-sm font-medium mb-1">ชื่อคอร์ส <span class="text-red-500">*</span></label>
        <input type="text" name="title" id="title" value="{{ old('title', $course?->title) }}" required
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="subtitle" class="block text-sm font-medium mb-1">คำโปรย (subtitle)</label>
        <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $course?->subtitle) }}"
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('subtitle')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
        <div>
            <label for="category_id" class="block text-sm font-medium mb-1">หมวดหมู่</label>
            <select name="category_id" id="category_id"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— เลือก —</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $course?->category_id) === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="level" class="block text-sm font-medium mb-1">ระดับ <span class="text-red-500">*</span></label>
            <select name="level" id="level"
                    class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['beginner' => 'เริ่มต้น', 'intermediate' => 'ปานกลาง', 'advanced' => 'ขั้นสูง'] as $val => $label)
                    <option value="{{ $val }}" @selected(old('level', $course?->level ?? 'beginner') === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="price" class="block text-sm font-medium mb-1">ราคา (บาท) <span class="text-red-500">*</span></label>
            <input type="number" name="price" id="price" min="0" step="1" value="{{ old('price', $course ? (int) $course->price : 0) }}" required
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <p class="text-xs text-gray-400 mt-1">ใส่ 0 = คอร์สฟรี</p>
            @error('price')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium mb-1">รายละเอียดคอร์ส</label>
        <textarea name="description" id="description" rows="6"
                  class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $course?->description) }}</textarea>
        @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="thumbnail" class="block text-sm font-medium mb-1">ภาพปก</label>
        @if ($course?->thumbnail)
            <img src="{{ $course->thumbnail_url }}" alt="ปกปัจจุบัน" class="mb-2 h-28 w-48 rounded-lg object-cover ring-1 ring-gray-200">
        @endif
        <input type="file" name="thumbnail" id="thumbnail" accept="image/*"
               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
        <p class="text-xs text-gray-400 mt-1">JPG/PNG ไม่เกิน 2MB — ถ้าไม่อัปโหลด จะใช้ปกไล่สีอัตโนมัติ</p>
        @error('thumbnail')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
</div>
