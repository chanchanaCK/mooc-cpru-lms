<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Encapsulates enrolment and progress-tracking rules so controllers stay thin
 * and the counters on `courses` / `enrollments` stay consistent.
 */
class LearningService
{
    public function __construct(
        private readonly CertificateService $certificates,
        private readonly CreditBankService $creditBank,
    ) {
    }

    /** Enrol a user into a course (idempotent). */
    public function enroll(User $user, Course $course): Enrollment
    {
        return DB::transaction(function () use ($user, $course) {
            $enrollment = Enrollment::firstOrNew([
                'user_id' => $user->id,
                'course_id' => $course->id,
            ]);

            if (! $enrollment->exists) {
                $enrollment->progress_percent = 0;
                $enrollment->save();

                $course->increment('students_count');
            }

            return $enrollment;
        });
    }

    /** Mark a lesson complete for a user, then recompute course progress. */
    public function markLessonComplete(User $user, Lesson $lesson): int
    {
        return DB::transaction(function () use ($user, $lesson) {
            LessonCompletion::firstOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['course_id' => $lesson->course_id, 'completed_at' => now()],
            );

            return $this->recalculateProgress($user, $lesson->course);
        });
    }

    /** Remove a completion (toggle off), then recompute progress. */
    public function markLessonIncomplete(User $user, Lesson $lesson): int
    {
        return DB::transaction(function () use ($user, $lesson) {
            LessonCompletion::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->delete();

            return $this->recalculateProgress($user, $lesson->course);
        });
    }

    /** Recompute and persist the enrolment progress percentage. */
    public function recalculateProgress(User $user, Course $course): int
    {
        $total = $course->lessons()->count();

        $done = LessonCompletion::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->count();

        $percent = $total > 0 ? (int) round($done / $total * 100) : 0;

        Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->update([
                'progress_percent' => $percent,
                'completed_at' => $percent >= 100 ? now() : null,
            ]);

        // Award a certificate and bank credits the moment the course is completed.
        if ($percent >= 100 && $total > 0) {
            $this->certificates->issueFor($user, $course);
            $this->creditBank->depositForCourse($user, $course);
        }

        return $percent;
    }
}
