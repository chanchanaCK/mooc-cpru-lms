<x-site-layout :title="'แบบทดสอบ: ' . $lesson->title">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <a href="{{ route('studio.courses.curriculum', $lesson->course) }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600"><i class="bi bi-arrow-left" aria-hidden="true"></i> กลับไปที่เนื้อหาคอร์ส</a>

        <div class="mt-2 flex items-center gap-2 text-indigo-600">
            <i class="bi bi-patch-question text-xl" aria-hidden="true"></i>
            <h1 class="text-2xl font-bold text-gray-900">แบบทดสอบ: {{ $lesson->title }}</h1>
        </div>
        <p class="text-gray-500 mt-1 text-sm">ผู้เรียนต้องได้ {{ \App\Models\Lesson::QUIZ_PASS_PERCENT }}% ขึ้นไปจึงจะผ่านและนับว่าเรียนจบบทนี้</p>

        {{-- Questions --}}
        <div class="mt-6 space-y-4">
            @forelse ($lesson->questions as $question)
                <div x-data="{ edit: false }" class="rounded-2xl bg-white ring-1 ring-gray-200 p-5">
                    <div x-show="!edit">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-medium">{{ $loop->iteration }}. {{ $question->text }}</p>
                            <div class="flex items-center gap-1 shrink-0">
                                <button type="button" @click="edit = true" aria-label="แก้ไขคำถาม" class="grid place-items-center h-8 w-8 rounded-lg text-gray-500 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                <form method="POST" action="{{ route('studio.questions.destroy', $question) }}" onsubmit="return confirm('ลบคำถามนี้?')">
                                    @csrf @method('DELETE')
                                    <button aria-label="ลบคำถาม" class="grid place-items-center h-8 w-8 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                </form>
                            </div>
                        </div>
                        <ul class="mt-3 space-y-1.5">
                            @foreach ($question->options as $option)
                                <li class="flex items-center gap-2 text-sm {{ $option->is_correct ? 'text-green-700 font-medium' : 'text-gray-600' }}">
                                    <i class="bi {{ $option->is_correct ? 'bi-check-circle-fill text-green-600' : 'bi-circle text-gray-300' }}" aria-hidden="true"></i>
                                    <span>{{ $option->text }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div x-show="edit" x-cloak>
                        @include('partials.studio-question-form', ['action' => route('studio.questions.update', $question), 'method' => 'PUT', 'question' => $question, 'cancel' => 'edit = false'])
                    </div>
                </div>
            @empty
                <div class="rounded-2xl bg-white ring-1 ring-gray-200 p-8 text-center text-gray-500">
                    <i class="bi bi-patch-question text-4xl text-gray-300" aria-hidden="true"></i>
                    <p class="mt-2 text-sm">ยังไม่มีคำถาม เพิ่มคำถามแรกด้านล่าง</p>
                </div>
            @endforelse
        </div>

        {{-- Add question --}}
        <div class="mt-6">
            <h2 class="font-semibold mb-2">เพิ่มคำถามใหม่</h2>
            @include('partials.studio-question-form', ['action' => route('studio.questions.store', $lesson), 'method' => 'POST', 'question' => null])
        </div>
    </div>
</x-site-layout>
