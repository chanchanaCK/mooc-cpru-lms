<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\LearningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(private readonly LearningService $learning)
    {
    }

    /**
     * Enrol the current user. For the MVP only free courses can be enrolled
     * directly; paid courses will route through checkout in a later phase.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        abort_unless($course->status === 'published', 404);

        if (! $course->isFree()) {
            return redirect()
                ->route('courses.show', $course)
                ->with('error', 'คอร์สนี้ต้องชำระเงินก่อน (ระบบชำระเงินอยู่ใน Phase ถัดไป)');
        }

        $this->learning->enroll($request->user(), $course);

        return redirect()
            ->route('learn.show', $course)
            ->with('success', 'ลงทะเบียนเรียนสำเร็จ! เริ่มเรียนได้เลย');
    }
}
