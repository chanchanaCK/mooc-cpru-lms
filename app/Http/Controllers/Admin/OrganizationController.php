<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\View\View;

/** Admin oversight of B2B organizations (read-oriented). */
class OrganizationController extends Controller
{
    public function index(): View
    {
        $organizations = Organization::with('owner')
            ->withCount(['members', 'assignments', 'programAssignments'])
            ->orderBy('name')
            ->get();

        return view('admin.organizations.index', compact('organizations'));
    }

    public function show(Organization $organization): View
    {
        $organization->load(['owner', 'members.user', 'assignedCourses.instructor', 'assignedPrograms']);

        return view('admin.organizations.show', compact('organization'));
    }
}
