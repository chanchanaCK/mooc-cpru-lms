@php
    $question = $question ?? null;
    $opts = $question ? $question->options->values() : collect();
    $correctIndex = $question ? (int) $opts->search(fn ($o) => $o->is_correct) : 0;
    if ($correctIndex < 0) $correctIndex = 0;
@endphp

<form method="POST" action="{{ $action }}" class="rounded-xl bg-gray-50 ring-1 ring-gray-200 p-4 space-y-3">
    @csrf
    @if (($method ?? 'POST') === 'PUT') @method('PUT') @endif

    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">คำถาม</label>
        <input type="text" name="text" value="{{ $question->text ?? '' }}" required
               class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <p class="text-xs font-medium text-gray-600">ตัวเลือก (เลือกวงกลมหน้าข้อที่เป็นคำตอบถูก · กรอกอย่างน้อย 2 ข้อ)</p>
    @for ($i = 0; $i < 4; $i++)
        <div class="flex items-center gap-2">
            <input type="radio" name="correct" value="{{ $i }}" @checked($correctIndex === $i) aria-label="ข้อถูกข้อที่ {{ $i + 1 }}"
                   class="text-green-600 focus:ring-green-500 shrink-0">
            <input type="text" name="options[{{ $i }}]" value="{{ $opts[$i]->text ?? '' }}" placeholder="ตัวเลือกที่ {{ $i + 1 }}"
                   class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
    @endfor

    @error('options')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
    @error('correct')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror

    <div class="flex items-center gap-2">
        <button class="rounded-full bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">บันทึกคำถาม</button>
        @isset($cancel)<button type="button" @click="{{ $cancel }}" class="text-sm text-gray-500 hover:text-gray-700">ยกเลิก</button>@endisset
    </div>
</form>
