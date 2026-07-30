@php
    use App\Models\CreditRecord;
    $attachedCredits = $program->courses->sum(fn ($c) => (float) $c->credits);
@endphp
<x-site-layout :title="'แก้ไข · ' . $program->title">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('registrar.programs.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> จัดการหลักสูตร</a>

        <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold">{{ $program->title }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    @if ($program->status === 'published')
                        <span class="text-emerald-600"><i class="bi bi-broadcast" aria-hidden="true"></i> เผยแพร่แล้ว</span>
                    @else
                        <span class="text-gray-500"><i class="bi bi-pencil" aria-hidden="true"></i> ฉบับร่าง</span>
                    @endif
                    · แนบแล้ว {{ CreditRecord::fmt($attachedCredits) }} / {{ CreditRecord::fmt($program->required_credits) }} หน่วยกิต
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if ($program->status === 'published')
                    <a href="{{ route('programs.show', $program) }}" target="_blank" rel="noopener" class="rounded-full ring-1 ring-gray-200 px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50">ดูหน้าเว็บ</a>
                @endif
                <form method="POST" action="{{ route('registrar.programs.publish', $program) }}">
                    @csrf
                    <button type="submit" class="rounded-full px-5 py-2 text-sm font-semibold {{ $program->status === 'published' ? 'ring-1 ring-gray-200 text-gray-600 hover:bg-gray-50' : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                        {{ $program->status === 'published' ? 'ถอนการเผยแพร่' : 'เผยแพร่หลักสูตร' }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Details --}}
        <form method="POST" action="{{ route('registrar.programs.update', $program) }}" class="mt-6 rounded-2xl bg-white ring-1 ring-gray-200 p-6">
            @csrf @method('PUT')
            @include('partials.registrar-program-fields')
            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">บันทึกข้อมูล</button>
            </div>
        </form>

        {{-- Courses in program --}}
        <div class="mt-8 rounded-2xl bg-white ring-1 ring-gray-200 p-6">
            <h2 class="font-bold">รายวิชาในหลักสูตร ({{ $program->courses->count() }})</h2>

            @if ($program->courses->isEmpty())
                <p class="mt-3 text-sm text-gray-400">ยังไม่มีรายวิชา — เพิ่มรายวิชาด้านล่าง</p>
            @else
                <div class="mt-4 space-y-2">
                    @foreach ($program->courses as $course)
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50 px-4 py-3">
                            <span class="grid place-items-center h-8 w-8 rounded-full bg-white ring-1 ring-gray-200 text-gray-500 shrink-0"><i class="bi bi-book" aria-hidden="true"></i></span>
                            <div class="min-w-0 flex-1">
                                <p class="font-medium truncate">{{ $course->title }}</p>
                                <p class="text-xs text-gray-400">{{ $course->course_code ? $course->course_code . ' · ' : '' }}{{ CreditRecord::fmt($course->credits) }} หน่วยกิต</p>
                            </div>
                            <span class="rounded-full px-2.5 py-0.5 text-xs {{ $course->pivot->requirement === 'required' ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $course->pivot->requirement === 'required' ? 'บังคับ' : 'เลือก' }}
                            </span>
                            <form method="POST" action="{{ route('registrar.programs.courses.detach', [$program, $course]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-lg px-2 py-1.5 text-rose-600 hover:bg-rose-50" title="นำออก"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Attach course --}}
            @if ($availableCourses->isNotEmpty())
                <form method="POST" action="{{ route('registrar.programs.courses.attach', $program) }}" class="mt-5 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-5">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <label for="course_id" class="block text-sm font-medium mb-1">เพิ่มรายวิชา (เฉพาะคอร์สที่ให้หน่วยกิต)</label>
                        <select name="course_id" id="course_id" required class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— เลือกคอร์ส —</option>
                            @foreach ($availableCourses as $course)
                                <option value="{{ $course->id }}">{{ $course->course_code ? $course->course_code . ' · ' : '' }}{{ $course->title }} ({{ CreditRecord::fmt($course->credits) }} นก.)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="requirement" class="block text-sm font-medium mb-1">ประเภท</label>
                        <select name="requirement" id="requirement" class="rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="required">บังคับ</option>
                            <option value="elective">เลือก</option>
                        </select>
                    </div>
                    <button type="submit" class="rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"><i class="bi bi-plus-lg" aria-hidden="true"></i> เพิ่ม</button>
                </form>
            @else
                <p class="mt-5 border-t border-gray-100 pt-5 text-sm text-gray-400">คอร์สที่ให้หน่วยกิตถูกเพิ่มครบแล้ว หรือยังไม่มีคอร์สที่ให้หน่วยกิต</p>
            @endif
        </div>
    </div>
</x-site-layout>
