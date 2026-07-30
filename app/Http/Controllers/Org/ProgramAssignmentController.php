<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Program;
use App\Services\OrgService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProgramAssignmentController extends Controller
{
    public function __construct(private readonly OrgService $orgs)
    {
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
        ]);

        $program = Program::published()->findOrFail($data['program_id']);

        $this->orgs->assignProgram($organization, $program, $request->user());

        return back()->with('success', "มอบหมายหลักสูตร “{$program->title}” ให้สมาชิกทุกคนแล้ว");
    }

    public function destroy(Request $request, Organization $organization, Program $program): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $this->orgs->unassignProgram($organization, $program);

        return back()->with('success', 'ยกเลิกการมอบหมายหลักสูตรแล้ว (สมาชิกยังเรียนต่อได้)');
    }
}
