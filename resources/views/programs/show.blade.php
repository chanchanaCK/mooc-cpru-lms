@php
    use App\Models\CreditRecord;
    $req = (float) $program->required_credits;
    $earned = (float) ($enrollment->credits_earned ?? 0);
    $pct = $req > 0 ? min(100, (int) floor($earned / $req * 100)) : (($enrollment && $enrollment->isCompleted()) ? 100 : 0);
    $required = $program->courses->where('pivot.requirement', 'required');
    $electives = $program->courses->where('pivot.requirement', 'elective');
@endphp
<x-site-layout :title="$program->title">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <a href="{{ route('programs.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> หลักสูตรทั้งหมด</a>

        <div class="mt-4 grid lg:grid-cols-3 gap-8">
            {{-- Main --}}
            <div class="lg:col-span-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1 text-xs font-medium">
                    <i class="bi bi-patch-check" aria-hidden="true"></i> {{ $program->type_label }}
                    @if ($program->level_label) · {{ $program->level_label }} @endif
                </span>
                <h1 class="mt-3 text-2xl sm:text-3xl font-bold leading-tight">{{ $program->title }}</h1>
                @if ($program->subtitle)
                    <p class="mt-2 text-gray-600">{{ $program->subtitle }}</p>
                @endif

                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
                    <span class="inline-flex items-center gap-1.5"><i class="bi bi-mortarboard" aria-hidden="true"></i> ต้องสะสม {{ CreditRecord::fmt($program->required_credits) }} หน่วยกิต</span>
                    <span class="inline-flex items-center gap-1.5"><i class="bi bi-collection-play" aria-hidden="true"></i> {{ $program->courses->count() }} รายวิชา</span>
                    @if ($program->duration_months)
                        <span class="inline-flex items-center gap-1.5"><i class="bi bi-clock-history" aria-hidden="true"></i> ประมาณ {{ $program->duration_months }} เดือน</span>
                    @endif
                </div>

                @if ($program->description)
                    <div class="mt-6 prose prose-sm max-w-none text-gray-700 whitespace-pre-line">{{ $program->description }}</div>
                @endif

                {{-- Curriculum --}}
                <h2 class="mt-8 text-lg font-bold">รายวิชาในหลักสูตร</h2>

                @foreach (['required' => ['วิชาบังคับ', $required], 'elective' => ['วิชาเลือก', $electives]] as [$label, $list])
                    @if ($list->isNotEmpty())
                        <h3 class="mt-5 mb-2 text-sm font-semibold text-gray-500">{{ $label }} ({{ $list->count() }})</h3>
                        <div class="space-y-2">
                            @foreach ($list as $course)
                                @php $banked = in_array($course->id, $bankedCourseIds, true); @endphp
                                <div class="flex items-center gap-3 rounded-xl bg-white ring-1 ring-gray-200 px-4 py-3">
                                    <span class="grid place-items-center h-8 w-8 rounded-full shrink-0 {{ $banked ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400' }}">
                                        <i class="bi {{ $banked ? 'bi-check-lg' : 'bi-book' }}" aria-hidden="true"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('courses.show', $course) }}" class="font-medium hover:text-indigo-600">{{ $course->title }}</a>
                                        <p class="text-xs text-gray-400">{{ $course->course_code ? $course->course_code . ' · ' : '' }}{{ $course->lessons_count }} บทเรียน</p>
                                    </div>
                                    <span class="text-sm text-gray-500 shrink-0">{{ CreditRecord::fmt($course->credits) }} นก.</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Sidebar: enrolment / progress --}}
            <div class="lg:col-span-1">
                <div class="rounded-2xl bg-white ring-1 ring-gray-200 overflow-hidden lg:sticky lg:top-24">
                    <img src="{{ $program->thumbnail_url }}" alt="{{ $program->title }}" class="w-full aspect-[16/9] object-cover">
                    <div class="p-5">
                        @auth
                            @if ($enrollment && $enrollment->isCompleted())
                                <div class="rounded-xl bg-emerald-50 text-emerald-800 px-4 py-3 text-sm text-center">
                                    <i class="bi bi-patch-check-fill" aria-hidden="true"></i> สำเร็จหลักสูตรแล้ว!
                                </div>
                                <a href="{{ route('programs.qualification', $program) }}" target="_blank" rel="noopener"
                                   class="mt-3 block text-center rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                    <i class="bi bi-award" aria-hidden="true"></i> รับคุณวุฒิ / สัมฤทธิบัตร
                                </a>
                            @elseif ($enrollment)
                                <p class="text-sm font-medium">ความคืบหน้าของคุณ</p>
                                <div class="mt-2 flex items-end justify-between">
                                    <span class="text-2xl font-bold text-indigo-600">{{ CreditRecord::fmt($earned) }}</span>
                                    <span class="text-sm text-gray-400">/ {{ CreditRecord::fmt($program->required_credits) }} หน่วยกิต</span>
                                </div>
                                <div class="mt-2 h-2.5 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-full bg-indigo-600 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <p class="mt-2 text-xs text-gray-400">สะสมหน่วยกิตให้ครบเพื่อรับคุณวุฒิ · ลงเรียนแล้ว</p>
                                <a href="{{ route('dashboard') }}" class="mt-3 block text-center rounded-full ring-1 ring-indigo-200 text-indigo-700 px-5 py-2.5 text-sm font-semibold hover:bg-indigo-50">ไปเรียนต่อ</a>
                            @else
                                <p class="text-sm text-gray-600">ลงทะเบียนเพื่อเริ่มสะสมหน่วยกิตในหลักสูตรนี้ หน่วยกิตที่คุณมีอยู่แล้วจะถูกนับให้อัตโนมัติ</p>
                                <form method="POST" action="{{ route('programs.enroll', $program) }}" class="mt-3">
                                    @csrf
                                    <button type="submit" class="w-full rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                        <i class="bi bi-mortarboard" aria-hidden="true"></i> ลงทะเบียนหลักสูตร
                                    </button>
                                </form>
                            @endif
                        @else
                            <p class="text-sm text-gray-600">เข้าสู่ระบบเพื่อลงทะเบียนหลักสูตรและสะสมหน่วยกิต</p>
                            <a href="{{ route('login') }}" class="mt-3 block text-center rounded-full bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">เข้าสู่ระบบ</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-site-layout>
