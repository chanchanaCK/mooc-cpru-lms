{{-- Shared credit-transfer review queue. Params: $pending (collection),
     $approveRoute + $rejectRoute (route names taking the transfer). --}}
@php use App\Models\CreditRecord; @endphp

@forelse ($pending as $t)
    <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-5" x-data="{ mode: null }">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-semibold">{{ $t->course_name }} <span class="text-gray-400 font-normal">· ขอ {{ CreditRecord::fmt($t->credits_requested) }} หน่วยกิต</span></p>
                <p class="text-sm text-gray-500">{{ $t->user?->name }} · {{ $t->source_type_label }}: {{ $t->source_name }}</p>
                @if ($t->program)<p class="text-xs text-indigo-600 mt-0.5">โอนเข้าหลักสูตร: {{ $t->program->title }}</p>@endif
            </div>
            @if ($t->evidence_path)
                <a href="{{ asset('storage/' . $t->evidence_path) }}" target="_blank" rel="noopener" class="text-sm text-indigo-600 hover:underline shrink-0"><i class="bi bi-paperclip" aria-hidden="true"></i> หลักฐาน</a>
            @endif
        </div>
        @if ($t->evidence_note)
            <p class="mt-2 rounded-lg bg-gray-50 px-3 py-2 text-sm text-gray-600">{{ $t->evidence_note }}</p>
        @endif

        <div class="mt-3 flex items-center gap-2">
            <button type="button" @click="mode = mode === 'approve' ? null : 'approve'" class="rounded-full bg-emerald-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700"><i class="bi bi-check-lg" aria-hidden="true"></i> อนุมัติ</button>
            <button type="button" @click="mode = mode === 'reject' ? null : 'reject'" class="rounded-full ring-1 ring-rose-200 text-rose-600 px-4 py-1.5 text-sm font-semibold hover:bg-rose-50"><i class="bi bi-x-lg" aria-hidden="true"></i> ไม่อนุมัติ</button>
        </div>

        {{-- Approve panel --}}
        <form method="POST" action="{{ route($approveRoute, $t) }}" x-show="mode === 'approve'" x-cloak class="mt-3 grid sm:grid-cols-3 gap-3 border-t border-gray-100 pt-3">
            @csrf
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-500">หน่วยกิตที่อนุมัติ</label>
                <input type="number" name="credits_awarded" step="0.5" min="0.5" max="99" value="{{ CreditRecord::fmt($t->credits_requested) }}" required class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-500">เกรด</label>
                <input type="text" name="grade" value="S" maxlength="4" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1 text-gray-500">หมายเหตุ</label>
                <input type="text" name="review_note" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="sm:col-span-3 flex justify-end">
                <button type="submit" class="rounded-full bg-emerald-600 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700">ยืนยันอนุมัติและโอนหน่วยกิต</button>
            </div>
        </form>

        {{-- Reject panel --}}
        <form method="POST" action="{{ route($rejectRoute, $t) }}" x-show="mode === 'reject'" x-cloak class="mt-3 border-t border-gray-100 pt-3">
            @csrf
            <label class="block text-xs font-medium mb-1 text-gray-500">เหตุผลที่ไม่อนุมัติ</label>
            <div class="flex gap-2">
                <input type="text" name="review_note" class="flex-1 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="เช่น หลักฐานไม่เพียงพอ">
                <button type="submit" class="rounded-full bg-rose-600 px-5 py-2 text-sm font-semibold text-white hover:bg-rose-700">ยืนยันไม่อนุมัติ</button>
            </div>
        </form>
    </div>
@empty
    <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-8 text-center text-sm text-gray-400">ไม่มีคำขอที่รอพิจารณา</div>
@endforelse
