<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** "My Learning" — the student's enrolled courses and progress. */
    public function index(Request $request): View
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with(['course.instructor', 'course.category'])
            ->latest()
            ->get();

        $inProgress = $enrollments->where('progress_percent', '<', 100);
        $completed = $enrollments->where('progress_percent', '>=', 100);

        return view('dashboard', compact('enrollments', 'inProgress', 'completed'));
    }
}
