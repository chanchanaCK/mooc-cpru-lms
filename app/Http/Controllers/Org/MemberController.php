<?php

namespace App\Http\Controllers\Org;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Services\OrgService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(private readonly OrgService $orgs)
    {
    }

    public function store(Request $request, Organization $organization): RedirectResponse
    {
        $this->authorize('manage', $organization);

        $data = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', 'in:member,manager'],
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [mb_strtolower($data['email'])])->first();

        if (! $user) {
            return back()->with('error', 'ไม่พบผู้ใช้อีเมลนี้ในระบบ (ต้องสมัครสมาชิกก่อน)');
        }

        if ($organization->hasMember($user)) {
            return back()->with('error', 'ผู้ใช้นี้อยู่ในองค์กรแล้ว');
        }

        if ($organization->members()->count() >= $organization->seats) {
            return back()->with('error', 'จำนวนที่นั่งเต็มแล้ว (' . $organization->seats . ' ที่นั่ง)');
        }

        $this->orgs->addMember($organization, $user, $data['role']);

        return back()->with('success', "เพิ่ม {$user->name} เข้าองค์กรแล้ว");
    }

    public function destroy(Request $request, Organization $organization, User $user): RedirectResponse
    {
        $this->authorize('manage', $organization);

        if ($organization->owner_id === $user->id) {
            return back()->with('error', 'ไม่สามารถนำเจ้าขององค์กรออกได้');
        }

        $this->orgs->removeMember($organization, $user);

        return back()->with('success', 'นำสมาชิกออกแล้ว');
    }
}
