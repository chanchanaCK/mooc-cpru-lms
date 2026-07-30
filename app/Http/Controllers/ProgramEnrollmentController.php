<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Services\CreditBankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramEnrollmentController extends Controller
{
    public function __construct(private readonly CreditBankService $creditBank)
    {
    }

    /** Enrol the learner into a program (banks any already-earned credits). */
    public function store(Request $request, Program $program): RedirectResponse
    {
        abort_unless($program->status === 'published', 404);

        $this->creditBank->enrollProgram($request->user(), $program);

        return redirect()
            ->route('programs.show', $program)
            ->with('success', 'ลงทะเบียนหลักสูตรแล้ว — สะสมหน่วยกิตให้ครบเพื่อรับคุณวุฒิ');
    }

    /** Printable qualification (คุณวุฒิ/สัมฤทธิบัตร) once the program is completed. */
    public function qualification(Request $request, Program $program): View
    {
        $user = $request->user();
        $enrollment = $program->enrollmentFor($user);

        abort_if($enrollment === null || ! $enrollment->isCompleted(), 403, 'ยังเรียนหลักสูตรนี้ไม่สำเร็จ');

        return view('programs.qualification', compact('program', 'enrollment', 'user'));
    }
}
