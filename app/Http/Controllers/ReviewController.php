<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isEnrolledIn($course), 403, 'ต้องลงเรียนก่อนจึงรีวิวได้');

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        Review::updateOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['rating' => $data['rating'], 'comment' => $data['comment'] ?? null],
        );

        $this->refreshCourseRating($course);

        return back()->with('success', 'ขอบคุณสำหรับรีวิว!');
    }

    /** Recompute the cached rating aggregates on the course. */
    private function refreshCourseRating(Course $course): void
    {
        $course->update([
            'rating_avg' => round((float) $course->reviews()->avg('rating'), 2),
            'rating_count' => $course->reviews()->count(),
        ]);
    }
}
