@php
    use App\Models\CreditRecord;
    $program = $program ?? null;
    $qualificationTypes = \App\Models\QualificationType::active()->ordered()->get();
    $qualificationLevels = \App\Models\QualificationLevel::ordered()->get();
    $currentType = old('type', $program?->type ?? optional($qualificationTypes->first())->slug);
    $currentLevel = old('nqf_level', $program?->nqf_level);
@endphp

<div class="space-y-5">
    <div>
        <label for="title" class="block text-sm font-medium mb-1">ชื่อหลักสูตร <span class="text-red-500">*</span></label>
        <input type="text" name="title" id="title" value="{{ old('title', $program?->title) }}" required
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('title')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="subtitle" class="block text-sm font-medium mb-1">คำโปรย</label>
        <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $program?->subtitle) }}"
               class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('subtitle')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label for="type" class="block text-sm font-medium mb-1">ประเภทคุณวุฒิ <span class="text-red-500">*</span></label>
            <select name="type" id="type" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($qualificationTypes as $qt)
                    <option value="{{ $qt->slug }}" @selected($currentType === $qt->slug)>{{ $qt->name }}</option>
                @endforeach
            </select>
            @error('type')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="required_credits" class="block text-sm font-medium mb-1">หน่วยกิตที่ต้องสะสม <span class="text-red-500">*</span></label>
            <input type="number" name="required_credits" id="required_credits" step="0.5" min="0" max="999" value="{{ old('required_credits', $program ? CreditRecord::fmt($program->required_credits) : '') }}" required
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('required_credits')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="nqf_level" class="block text-sm font-medium mb-1">ระดับคุณวุฒิ</label>
            <select name="nqf_level" id="nqf_level" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">— ไม่ระบุ —</option>
                @foreach ($qualificationLevels as $ql)
                    <option value="{{ $ql->level }}" @selected((string) $currentLevel === (string) $ql->level)>ระดับ {{ $ql->level }} — {{ $ql->name }}</option>
                @endforeach
            </select>
            @error('nqf_level')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="duration_months" class="block text-sm font-medium mb-1">ระยะเวลา (เดือน)</label>
            <input type="number" name="duration_months" id="duration_months" min="0" max="120" value="{{ old('duration_months', $program?->duration_months) }}"
                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            @error('duration_months')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label for="description" class="block text-sm font-medium mb-1">รายละเอียดหลักสูตร</label>
        <textarea name="description" id="description" rows="5"
                  class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $program?->description) }}</textarea>
        @error('description')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
    </div>
</div>
