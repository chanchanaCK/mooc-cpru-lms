<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CreditRecord;
use App\Models\CreditTransferRequest;
use App\Models\Program;
use App\Models\ProgramEnrollment;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Credit Bank (คลังหน่วยกิต) — the single owner of credit accounting: banks
 * credits when a credit-bearing course is completed, computes the grade per the
 * course's grading policy, advances any programs, and builds transcript data.
 */
class CreditBankService
{
    /** University-style letter-grade bands, high → low (score is a percentage). */
    private const GRADE_BANDS = [
        [80, 'A'], [75, 'B+'], [70, 'B'], [65, 'C+'],
        [60, 'C'], [55, 'D+'], [50, 'D'],
    ];

    /**
     * Deposit credits for a completed course (idempotent). Returns the credit
     * record, or null when the course is not credit-bearing.
     */
    public function depositForCourse(User $user, Course $course): ?CreditRecord
    {
        if (! $course->isCreditBearing()) {
            return null;
        }

        return DB::transaction(function () use ($user, $course) {
            $existing = CreditRecord::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('source', 'course')
                ->first();

            if ($existing) {
                return $existing;
            }

            $score = $this->bestQuizScore($user, $course);
            $result = $this->computeGrade($course, $score);

            $record = CreditRecord::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'source' => 'course',
                'title' => $course->title,
                'code' => $course->course_code,
                'credits' => $result['credits'],
                'grade' => $result['grade'],
                'score_percent' => $result['score'],
                'status' => 'earned',
                'earned_at' => now(),
                'expires_at' => now()->addYears(8),
                'certificate_id' => optional($course->certificateFor($user))->id,
            ]);

            $this->advancePrograms($user);

            return $record;
        });
    }

    /**
     * Grade + earned credits for a course given the learner's best quiz score.
     *
     * @return array{grade:string, score:?int, passed:bool, credits:float}
     */
    public function computeGrade(Course $course, ?int $scorePercent): array
    {
        $threshold = (int) $course->pass_threshold;
        $full = (float) $course->credits;

        if ($course->grading_method === 'graded' && $scorePercent !== null) {
            $passed = $scorePercent >= $threshold;

            return [
                'grade' => $this->letterGrade($scorePercent),
                'score' => $scorePercent,
                'passed' => $passed,
                'credits' => $passed ? $full : 0.0,
            ];
        }

        // pass_fail, or a graded course with no quiz score: completion = pass.
        $passed = $scorePercent === null ? true : $scorePercent >= $threshold;

        return [
            'grade' => $passed ? 'S' : 'U',
            'score' => $scorePercent,
            'passed' => $passed,
            'credits' => $passed ? $full : 0.0,
        ];
    }

    /** The learner's best quiz score (%) across the course, or null if no quiz. */
    public function bestQuizScore(User $user, Course $course): ?int
    {
        $best = QuizAttempt::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->max('score');

        return $best === null ? null : (int) $best;
    }

    /**
     * Enrol a learner in a program (idempotent) and enrol them in its required
     * courses so they can start immediately. Recomputes progress afterwards.
     */
    public function enrollProgram(User $user, Program $program): ProgramEnrollment
    {
        return DB::transaction(function () use ($user, $program) {
            $enrollment = ProgramEnrollment::firstOrCreate(
                ['user_id' => $user->id, 'program_id' => $program->id],
                ['status' => 'in_progress', 'credits_earned' => 0],
            );

            // Resolve lazily to avoid a circular dependency with LearningService.
            $learning = app(LearningService::class);

            $requiredIds = DB::table('program_courses')
                ->where('program_id', $program->id)
                ->where('requirement', 'required')
                ->pluck('course_id');

            foreach (Course::whereIn('id', $requiredIds)->get() as $course) {
                $learning->enroll($user, $course);
            }

            $this->recalcProgramEnrollment($enrollment);

            return $enrollment;
        });
    }

    /** Recompute every in-progress program for the learner (banked credits changed). */
    public function advancePrograms(User $user): void
    {
        ProgramEnrollment::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->get()
            ->each(fn (ProgramEnrollment $pe) => $this->recalcProgramEnrollment($pe));
    }

    /**
     * Recompute one program enrolment's accumulated credits and, when the
     * requirement is met, mark it completed and issue a qualification number.
     * Uses primary-key lookups / the query builder (no relationship eager
     * loading) so it stays reliable inside bulk seeding transactions.
     */
    public function recalcProgramEnrollment(ProgramEnrollment $enrollment): void
    {
        $program = Program::find($enrollment->program_id);

        if (! $program) {
            return;
        }

        $pivot = DB::table('program_courses')->where('program_id', $program->id)->get();
        $courseIds = $pivot->pluck('course_id');
        $requiredIds = $pivot->where('requirement', 'required')->pluck('course_id');

        $earned = CreditRecord::where('user_id', $enrollment->user_id)
            ->where('status', 'earned')
            ->whereIn('course_id', $courseIds)
            ->get(['course_id', 'credits']);

        $creditsEarned = (float) $earned->sum('credits');
        $requiredDone = $requiredIds->diff($earned->pluck('course_id'))->isEmpty();

        $enrollment->credits_earned = $creditsEarned;

        if ($enrollment->status === 'in_progress'
            && $creditsEarned >= (float) $program->required_credits
            && $requiredDone
        ) {
            $enrollment->status = 'completed';
            $enrollment->completed_at = now();
            $enrollment->certificate_number = $enrollment->certificate_number ?: $this->generateProgramNumber();
        }

        $enrollment->save();
    }

    private function generateProgramNumber(): string
    {
        do {
            $number = 'CERT-' . now()->format('Y') . '-' . strtoupper(Str::random(6));
        } while (ProgramEnrollment::where('certificate_number', $number)->exists());

        return $number;
    }

    /**
     * Approve a credit-transfer/RPL request: bank the awarded credits as a
     * `transfer` credit record, link it back, and advance programs.
     */
    public function approveTransfer(CreditTransferRequest $request, User $reviewer, float $credits, string $grade = 'S', ?string $note = null): CreditRecord
    {
        return DB::transaction(function () use ($request, $reviewer, $credits, $grade, $note) {
            $record = CreditRecord::create([
                'user_id' => $request->user_id,
                'course_id' => null,
                'source' => 'transfer',
                'title' => $request->course_name . ' — ' . $request->source_name,
                'code' => null,
                'credits' => $credits,
                'grade' => $grade,
                'score_percent' => null,
                'status' => 'earned',
                'earned_at' => now(),
                'expires_at' => now()->addYears(8),
                'note' => 'เทียบโอนจาก ' . $request->source_name,
            ]);

            $request->update([
                'status' => 'approved',
                'credits_awarded' => $credits,
                'grade' => $grade,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_note' => $note,
                'credit_record_id' => $record->id,
            ]);

            if ($user = User::find($request->user_id)) {
                $this->advancePrograms($user);
            }

            return $record;
        });
    }

    /** Reject a credit-transfer request. */
    public function rejectTransfer(CreditTransferRequest $request, User $reviewer, ?string $note = null): void
    {
        $request->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);
    }

    /** Total earned credits currently in the learner's bank. */
    public function totalCredits(User $user): float
    {
        return (float) $user->creditRecords()->earned()->sum('credits');
    }

    /** Data for the credit-bank overview & the printable transcript. */
    public function transcriptData(User $user): array
    {
        $records = $user->creditRecords()
            ->with('course')
            ->orderByDesc('earned_at')
            ->get();

        $earned = $records->where('status', 'earned');

        return [
            'records' => $records,
            'total_credits' => (float) $earned->sum('credits'),
            'course_credits' => (float) $earned->where('source', 'course')->sum('credits'),
            'transfer_credits' => (float) $earned->where('source', 'transfer')->sum('credits'),
            'count' => $records->count(),
            'programs' => $user->programEnrollments()->with('program')->latest()->get(),
        ];
    }

    private function letterGrade(int $score): string
    {
        foreach (self::GRADE_BANDS as [$min, $grade]) {
            if ($score >= $min) {
                return $grade;
            }
        }

        return 'F';
    }
}
