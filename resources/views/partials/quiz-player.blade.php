@php $answers = $quizAttempt?->answers ?? []; @endphp

<div class="bg-white">
    <div class="max-w-3xl mx-auto p-5 sm:p-8" x-data="{ retake: {{ $quizAttempt ? 'false' : 'true' }} }">
        <div class="flex items-center gap-2 text-indigo-600">
            <i class="bi bi-patch-question text-xl" aria-hidden="true"></i>
            <h2 class="font-bold">แบบทดสอบ</h2>
        </div>
        <p class="text-sm text-gray-500 mt-1">ต้องได้ {{ \App\Models\Lesson::QUIZ_PASS_PERCENT }}% ขึ้นไปจึงจะผ่าน · {{ $quizQuestions->count() }} ข้อ</p>

        @if (! $enrolled)
            <div class="mt-5 rounded-xl bg-indigo-50 text-indigo-800 px-4 py-3 text-sm">
                ลงทะเบียนเรียนก่อนเพื่อทำแบบทดสอบ
            </div>
        @elseif ($quizQuestions->isEmpty())
            <div class="mt-5 rounded-xl bg-gray-50 text-gray-500 px-4 py-3 text-sm">ยังไม่มีคำถามในแบบทดสอบนี้</div>
        @else
            {{-- Result banner (after an attempt) --}}
            @if ($quizAttempt)
                <div class="mt-5 flex items-center gap-3 rounded-xl p-4 {{ $quizAttempt->passed ? 'bg-green-50 ring-1 ring-green-200' : 'bg-red-50 ring-1 ring-red-200' }}">
                    <i class="bi {{ $quizAttempt->passed ? 'bi-check-circle-fill text-green-600' : 'bi-x-circle-fill text-red-600' }} text-3xl" aria-hidden="true"></i>
                    <div class="flex-1">
                        <p class="font-bold {{ $quizAttempt->passed ? 'text-green-800' : 'text-red-800' }}">
                            {{ $quizAttempt->passed ? 'ผ่านแบบทดสอบแล้ว' : 'ยังไม่ผ่าน' }} · คะแนน {{ $quizAttempt->score }}%
                        </p>
                        <p class="text-sm {{ $quizAttempt->passed ? 'text-green-700' : 'text-red-700' }}">
                            {{ $quizAttempt->passed ? 'บทเรียนนี้ถูกทำเครื่องหมายว่าเรียนจบแล้ว' : 'ทบทวนคำตอบด้านล่างแล้วลองอีกครั้ง' }}
                        </p>
                    </div>
                    <button type="button" @click="retake = true" x-show="!retake"
                            class="rounded-full ring-1 ring-gray-300 bg-white px-4 py-2 text-sm font-semibold hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                        ทำใหม่
                    </button>
                </div>
            @endif

            {{-- Review (read-only) after an attempt --}}
            <div x-show="!retake" x-cloak class="mt-6 space-y-5">
                @foreach ($quizQuestions as $i => $question)
                    @php $chosen = (int) ($answers[$question->id] ?? 0); @endphp
                    <div>
                        <p class="font-medium text-sm">{{ $i + 1 }}. {{ $question->text }}</p>
                        <ul class="mt-2 space-y-1.5">
                            @foreach ($question->options as $option)
                                @php $isCorrect = $option->is_correct; $isChosen = $option->id === $chosen; @endphp
                                <li class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm ring-1 {{ $isCorrect ? 'bg-green-50 ring-green-200 text-green-800' : ($isChosen ? 'bg-red-50 ring-red-200 text-red-800' : 'ring-gray-200 text-gray-600') }}">
                                    @if ($isCorrect)<i class="bi bi-check-circle-fill text-green-600" aria-hidden="true"></i>
                                    @elseif ($isChosen)<i class="bi bi-x-circle-fill text-red-500" aria-hidden="true"></i>
                                    @else<i class="bi bi-circle text-gray-300" aria-hidden="true"></i>@endif
                                    <span>{{ $option->text }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            {{-- Quiz form --}}
            <form method="POST" action="{{ route('learn.quiz.submit', [$course, $lesson]) }}" x-show="retake" x-cloak class="mt-6 space-y-6">
                @csrf
                @foreach ($quizQuestions as $i => $question)
                    <fieldset>
                        <legend class="font-medium text-sm">{{ $i + 1 }}. {{ $question->text }}</legend>
                        <div class="mt-2 space-y-1.5">
                            @foreach ($question->options as $option)
                                <label class="flex items-center gap-3 rounded-lg ring-1 ring-gray-200 px-3 py-2 text-sm cursor-pointer hover:bg-gray-50 has-[:checked]:ring-indigo-500 has-[:checked]:bg-indigo-50">
                                    <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                           class="text-indigo-600 focus:ring-indigo-500" required>
                                    <span>{{ $option->text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                @endforeach

                <button class="rounded-full bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    ส่งคำตอบ
                </button>
            </form>
        @endif
    </div>
</div>
