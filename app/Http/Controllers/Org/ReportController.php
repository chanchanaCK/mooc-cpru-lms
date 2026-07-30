<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\CreditRecord;
use App\Models\Enrollment;
use App\Models\Organization;
use App\Models\ProgramEnrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function show(Request $request, Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load(['members.user', 'assignedCourses']);

        $members = $organization->users->sortBy('name')->values();
        $courses = $organization->assignedCourses;
        $memberIds = $members->pluck('id');

        // progress map keyed "userId-courseId" => percent
        $progress = Enrollment::whereIn('user_id', $memberIds)
            ->whereIn('course_id', $courses->pluck('id'))
            ->get()
            ->mapWithKeys(fn ($e) => ["{$e->user_id}-{$e->course_id}" => (int) $e->progress_percent]);

        // Credit bank: total earned credits per member
        $creditTotals = CreditRecord::whereIn('user_id', $memberIds)
            ->where('status', 'earned')
            ->get(['user_id', 'credits'])
            ->groupBy('user_id')
            ->map(fn ($rows) => (float) $rows->sum('credits'));

        // Completed programs per member
        $programsDone = ProgramEnrollment::whereIn('user_id', $memberIds)
            ->where('status', 'completed')
            ->get(['user_id'])
            ->groupBy('user_id')
            ->map->count();

        return view('org.report', compact('organization', 'members', 'courses', 'progress', 'creditTotals', 'programsDone'));
    }
}
