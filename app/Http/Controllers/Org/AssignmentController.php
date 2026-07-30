<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Organization;
use App\Services\OrgService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function __construct(private readonly OrgService $orgs)
    {
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'due_at' => ['nullable', 'date'],
        ]);

        $course = Course::published()->findOrFail($data['course_id']);

        $this->orgs->assignCourse($organization, $course, $request->user(), $data['due_at'] ?? null);

        return back()->with('success', "มอบหมาย “{$course->title}” ให้สมาชิกทุกคนแล้ว");
    }

    public function destroy(Request $request, Organization $organization, Course $course): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $this->orgs->unassignCourse($organization, $course);

        return back()->with('success', 'ยกเลิกการมอบหมายแล้ว (สมาชิกยังเข้าเรียนต่อได้)');
    }
}
