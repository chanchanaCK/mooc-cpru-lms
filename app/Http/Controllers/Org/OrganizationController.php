<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Organization;
use App\Models\Program;
use App\Services\OrgService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function __construct(private readonly OrgService $orgs)
    {
    }

    public function index(Request $request): View
    {
        $organizations = $request->user()
            ->organizations()
            ->withCount(['members', 'assignments'])
            ->get();

        return view('org.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('org.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'seats' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $org = $this->orgs->createOrganization($request->user(), $data['name'], $data['seats']);

        return redirect()->route('org.show', $org)->with('success', 'สร้างองค์กรแล้ว');
    }

    public function show(Request $request, Organization $organization): View
    {
        $this->authorize('view', $organization);

        $organization->load(['members.user', 'assignedCourses.instructor', 'assignedPrograms']);

        $canManage = $organization->canManage($request->user());

        // Courses available to assign (published, not already assigned).
        $assignableCourses = $canManage
            ? Course::published()
                ->whereNotIn('id', $organization->assignedCourses->pluck('id'))
                ->orderBy('title')
                ->get()
            : collect();

        // Programs available to assign (published, not already assigned).
        $assignablePrograms = $canManage
            ? Program::published()
                ->whereNotIn('id', $organization->assignedPrograms->pluck('id'))
                ->orderBy('title')
                ->get()
            : collect();

        return view('org.show', compact('organization', 'canManage', 'assignableCourses', 'assignablePrograms'));
    }
}
