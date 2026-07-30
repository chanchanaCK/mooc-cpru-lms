<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->withCount(['courses', 'enrollments'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', 'in:student,instructor,registrar,admin']]);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'ไม่สามารถเปลี่ยนบทบาทของตัวเองได้');
        }

        $user->update(['role' => $data['role']]);

        return back()->with('success', "เปลี่ยนบทบาทของ {$user->name} เป็น {$data['role']} แล้ว");
    }

    /** Edit a user's profile / ประวัติ (esp. instructors: headline + bio + avatar). */
    public function editProfile(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateProfile(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'headline' => ['nullable', 'string', 'max:180'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'avatar' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', "อัปเดตประวัติของ {$user->name} แล้ว");
    }
}
