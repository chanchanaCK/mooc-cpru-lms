@php use App\Models\CreditRecord; @endphp
<x-site-layout title="เทียบโอนหน่วยกิต">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('credit-bank.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> คลังหน่วยกิต</a>
        <h1 class="mt-2 text-2xl font-bold">เทียบโอนหน่วยกิต (RPL)</h1>
        <p class="text-gray-500 mt-1 text-sm">ขอเทียบโอนหน่วยกิตจากสถาบันอื่นหรือจากประสบการณ์การทำงาน เมื่อนายทะเบียนอนุมัติ หน่วยกิตจะถูกโอนเข้าคลังของคุณ</p>

        {{-- Submit form --}}
        <form method="POST" action="{{ route('credit-bank.transfers.store') }}" enctype="multipart/form-data" class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-6 space-y-5">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="source_type" class="block text-sm font-medium mb-1">ประเภทที่มา <span class="text-red-500">*</span></label>
                    <select name="source_type" id="source_type" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach (['institution' => 'สถาบันการศึกษา', 'experience' => 'ประสบการณ์ทำงาน', 'other' => 'อื่น ๆ'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('source_type') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="source_name" class="block text-sm font-medium mb-1">ชื่อสถาบัน / ที่มา <span class="text-red-500">*</span></label>
                    <input type="text" name="source_name" id="source_name" value="{{ old('source_name') }}" required placeholder="เช่น มหาวิทยาลัย ก."
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('source_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="course_name" class="block text-sm font-medium mb-1">ชื่อวิชา / ความสามารถ <span class="text-red-500">*</span></label>
                    <input type="text" name="course_name" id="course_name" value="{{ old('course_name') }}" required placeholder="เช่น สถิติเบื้องต้น"
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('course_name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="credits_requested" class="block text-sm font-medium mb-1">หน่วยกิตที่ขอเทียบโอน <span class="text-red-500">*</span></label>
                    <input type="number" name="credits_requested" id="credits_requested" step="0.5" min="0.5" max="99" value="{{ old('credits_requested') }}" required
                           class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    @error('credits_requested')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="program_id" class="block text-sm font-medium mb-1">โอนเข้าหลักสูตร (ถ้ามี)</label>
                    <select name="program_id" id="program_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— ไม่ระบุ (เก็บเข้าคลังทั่วไป) —</option>
                        @foreach ($programs as $program)
                            <option value="{{ $program->id }}" @selected((int) old('program_id') === $program->id)>{{ $program->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="evidence_note" class="block text-sm font-medium mb-1">รายละเอียด / หลักฐานประกอบ</label>
                <textarea name="evidence_note" id="evidence_note" rows="3" placeholder="อธิบายที่มา เช่น เกรดที่ได้ ปีที่เรียน หรือประสบการณ์ที่เกี่ยวข้อง"
                          class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">{{ old('evidence_note') }}</textarea>
                @error('evidence_note')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="evidence" class="block text-sm font-medium mb-1">แนบไฟล์หลักฐาน (PDF/รูป ไม่เกิน 4MB)</label>
                <input type="file" name="evidence" id="evidence" accept=".pdf,image/*"
                       class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                @error('evidence')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end">
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="bi bi-send" aria-hidden="true"></i> ส่งคำขอเทียบโอน</button>
            </div>
        </form>

        {{-- My requests --}}
        <h2 class="mt-8 font-bold">คำขอของฉัน</h2>
        @if ($transfers->isEmpty())
            <div class="mt-3 rounded-2xl bg-white ring-1 ring-gray-200 p-8 text-center text-sm text-gray-400">ยังไม่มีคำขอเทียบโอน</div>
        @else
            <div class="mt-3 space-y-3">
                @foreach ($transfers as $t)
                    @php
                        $tone = match ($t->status) {
                            'approved' => 'bg-emerald-50 text-emerald-700',
                            'rejected' => 'bg-rose-50 text-rose-700',
                            default => 'bg-amber-50 text-amber-700',
                        };
                    @endphp
                    <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold">{{ $t->course_name }}</p>
                                <p class="text-sm text-gray-500">{{ $t->source_type_label }} · {{ $t->source_name }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs {{ $tone }}">{{ $t->status_label }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-500">
                            <span>ขอเทียบโอน: <strong class="text-gray-700">{{ CreditRecord::fmt($t->credits_requested) }}</strong> หน่วยกิต</span>
                            @if ($t->status === 'approved')
                                <span>อนุมัติ: <strong class="text-emerald-700">{{ CreditRecord::fmt($t->credits_awarded) }}</strong> หน่วยกิต ({{ $t->grade }})</span>
                            @endif
                            @if ($t->program)
                                <span>หลักสูตร: {{ $t->program->title }}</span>
                            @endif
                        </div>
                        @if ($t->review_note)
                            <p class="mt-2 text-sm text-gray-500"><i class="bi bi-chat-left-text" aria-hidden="true"></i> หมายเหตุนายทะเบียน: {{ $t->review_note }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-site-layout>
