<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function __construct(private readonly CertificateService $certificates)
    {
    }

    /** The printable certificate page (save-as-PDF from the browser). */
    public function show(Request $request, Course $course)
    {
        $user = $request->user();

        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();

        abort_if($enrollment === null, 403, 'คุณยังไม่ได้ลงเรียนคอร์สนี้');

        if ($enrollment->progress_percent < 100) {
            return redirect()
                ->route('learn.show', $course)
                ->with('error', 'เรียนให้ครบ 100% ก่อนรับใบประกาศนียบัตร');
        }

        // Issue on the fly if somehow missing (idempotent).
        $certificate = $course->certificateFor($user) ?? $this->certificates->issueFor($user, $course);

        return view('certificates.show', compact('course', 'certificate', 'user'));
    }
}
