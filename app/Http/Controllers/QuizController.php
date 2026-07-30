<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Services\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(private readonly QuizService $quiz)
    {
    }

    public function submit(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id && $lesson->isQuiz(), 404);
        abort_unless($request->user()->isEnrolledIn($course), 403, 'กรุณาลงทะเบียนเรียนก่อน');

        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['nullable', 'integer'],
        ]);

        $attempt = $this->quiz->grade($request->user(), $lesson, $data['answers']);

        $message = $attempt->passed
            ? "ผ่านแบบทดสอบ! คะแนน {$attempt->score}%"
            : "ยังไม่ผ่าน (ได้ {$attempt->score}%, ต้องได้ " . Lesson::QUIZ_PASS_PERCENT . '% ขึ้นไป) ลองใหม่ได้';

        return redirect()
            ->route('learn.lesson', [$course, $lesson])
            ->with($attempt->passed ? 'success' : 'error', $message);
    }
}
