<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Services\LearningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearnController extends Controller
{
    public function __construct(private readonly LearningService $learning)
    {
    }

    /** Entry point: send the learner to the first lesson of the course. */
    public function show(Request $request, Course $course): RedirectResponse
    {
        $this->ensureEnrolled($request, $course);

        $first = $course->firstLesson();

        abort_if($first === null, 404, 'คอร์สนี้ยังไม่มีบทเรียน');

        return redirect()->route('learn.lesson', [$course, $first]);
    }

    /** The classroom / video player for a single lesson. */
    public function lesson(Request $request, Course $course, Lesson $lesson): View
    {
        abort_unless($lesson->course_id === $course->id, 404);

        $user = $request->user();
        $enrolled = $user->isEnrolledIn($course);

        // Non-enrolled users may only watch preview lessons.
        abort_unless($enrolled || $lesson->is_preview, 403, 'กรุณาลงทะเบียนเรียนก่อน');

        $course->load('sections.lessons');

        $completedIds = $enrolled
            ? $user->lessonCompletions()->where('course_id', $course->id)->pluck('lesson_id')->all()
            : [];

        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();

        $quizQuestions = $lesson->isQuiz() ? $lesson->questions()->with('options')->get() : collect();
        $quizAttempt = $lesson->isQuiz() ? $lesson->latestAttemptFor($user) : null;

        return view('learn.lesson', compact(
            'course', 'lesson', 'completedIds', 'enrollment', 'enrolled', 'quizQuestions', 'quizAttempt'
        ));
    }

    /** Toggle a lesson's completion state, then bounce back to the player. */
    public function complete(Request $request, Course $course, Lesson $lesson): RedirectResponse
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $this->ensureEnrolled($request, $course);

        $user = $request->user();

        if ($lesson->isCompletedBy($user)) {
            $this->learning->markLessonIncomplete($user, $lesson);
        } else {
            $this->learning->markLessonComplete($user, $lesson);
        }

        // Advance to the next lesson when marking complete, else stay put.
        $next = $course->lessons()
            ->where('sort_order', '>', $lesson->sort_order)
            ->orderBy('sort_order')
            ->first();

        $target = $next ?: $lesson;

        return redirect()->route('learn.lesson', [$course, $target]);
    }

    private function ensureEnrolled(Request $request, Course $course): void
    {
        abort_unless($request->user()->isEnrolledIn($course), 403, 'กรุณาลงทะเบียนเรียนก่อน');
    }
}
