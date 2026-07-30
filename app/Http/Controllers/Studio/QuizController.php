<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function edit(Request $request, Lesson $lesson): View
    {
        abort_unless($lesson->isQuiz(), 404);
        $this->authorize('manage', $lesson->course);

        $lesson->load('questions.options');

        return view('studio.quiz.edit', compact('lesson'));
    }

    public function storeQuestion(Request $request, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->isQuiz(), 404);
        $this->authorize('manage', $lesson->course);

        $data = $this->validateQuestion($request);

        $question = $lesson->questions()->create([
            'text' => $data['text'],
            'sort_order' => (int) $lesson->questions()->max('sort_order') + 1,
        ]);

        $this->syncOptions($question, $data['options'], (int) $data['correct']);

        return back()->with('success', 'เพิ่มคำถามแล้ว');
    }

    public function updateQuestion(Request $request, Question $question): RedirectResponse
    {
        $this->authorize('manage', $question->lesson->course);

        $data = $this->validateQuestion($request);

        $question->update(['text' => $data['text']]);
        $question->options()->delete();
        $this->syncOptions($question, $data['options'], (int) $data['correct']);

        return back()->with('success', 'แก้ไขคำถามแล้ว');
    }

    public function destroyQuestion(Request $request, Question $question): RedirectResponse
    {
        $this->authorize('manage', $question->lesson->course);

        $question->delete();

        return back()->with('success', 'ลบคำถามแล้ว');
    }

    /* ---------------------------------------------------------------- */

    private function validateQuestion(Request $request): array
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:500'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['nullable', 'string', 'max:255'],
            'correct' => ['required', 'integer', 'min:0'],
        ]);

        $filled = array_filter($validated['options'], fn ($o) => filled($o));

        if (count($filled) < 2) {
            throw ValidationException::withMessages(['options' => 'ต้องมีอย่างน้อย 2 ตัวเลือก']);
        }

        if (blank($validated['options'][$validated['correct']] ?? null)) {
            throw ValidationException::withMessages(['correct' => 'กรุณาเลือกคำตอบที่ถูกต้องจากตัวเลือกที่กรอกข้อความ']);
        }

        return $validated;
    }

    private function syncOptions(Question $question, array $options, int $correctIndex): void
    {
        $sort = 0;
        foreach ($options as $index => $text) {
            if (blank($text)) {
                continue;
            }

            $question->options()->create([
                'text' => $text,
                'is_correct' => $index === $correctIndex,
                'sort_order' => $sort++,
            ]);
        }
    }
}
