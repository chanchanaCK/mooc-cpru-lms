<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    /** Public catalog of published programs. */
    public function index(Request $request): View
    {
        $programs = Program::published()
            ->withCount('courses')
            ->orderBy('title')
            ->get();

        return view('programs.index', compact('programs'));
    }

    /** Program detail with the learner's own progress. */
    public function show(Request $request, Program $program): View
    {
        $user = $request->user();

        abort_unless(
            $program->status === 'published' || ($user && $user->isRegistrar()),
            404,
        );

        $program->load(['courses' => fn ($q) => $q->withCount('lessons')]);
        $enrollment = $program->enrollmentFor($user);

        // Course ids the learner has already banked credits for.
        $bankedCourseIds = $user
            ? $user->creditRecords()->where('status', 'earned')->pluck('course_id')->filter()->all()
            : [];

        return view('programs.show', compact('program', 'enrollment', 'bankedCourseIds'));
    }
}
