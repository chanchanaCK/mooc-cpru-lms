<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class QuizService
{
    public function __construct(private readonly LearningService $learning)
    {
    }

    /**
     * Grade a quiz submission, record the attempt, and (on pass) mark the
     * lesson complete so it counts toward course progress.
     *
     * @param  array<int, int>  $answers  question_id => chosen option_id
     */
    public function grade(User $user, Lesson $lesson, array $answers): QuizAttempt
    {
        $questions = $lesson->questions()->with('options')->get();
        $total = $questions->count();

        $correct = $questions->filter(function ($question) use ($answers) {
            $chosen = (int) ($answers[$question->id] ?? 0);
            $answer = $question->options->firstWhere('is_correct', true);

            return $answer && $answer->id === $chosen;
        })->count();

        $score = $total > 0 ? (int) round($correct / $total * 100) : 0;
        $passed = $score >= Lesson::QUIZ_PASS_PERCENT;

        return DB::transaction(function () use ($user, $lesson, $answers, $score, $passed) {
            $attempt = QuizAttempt::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'course_id' => $lesson->course_id,
                'score' => $score,
                'passed' => $passed,
                'answers' => $answers,
            ]);

            if ($passed && ! $lesson->isCompletedBy($user)) {
                $this->learning->markLessonComplete($user, $lesson);
            }

            return $attempt;
        });
    }
}
