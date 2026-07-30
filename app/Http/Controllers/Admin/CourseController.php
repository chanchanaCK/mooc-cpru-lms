<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->with(['instructor', 'category'])
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.courses.index', compact('courses'));
    }

    public function togglePublish(Request $request, Course $course): RedirectResponse
    {
        if ($course->status === 'published') {
            $course->update(['status' => 'draft']);

            return back()->with('success', 'ย้ายคอร์สเป็นฉบับร่างแล้ว');
        }

        $course->update(['status' => 'published', 'published_at' => $course->published_at ?? now()]);

        return back()->with('success', 'เผยแพร่คอร์สแล้ว');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $course->delete();

        return back()->with('success', 'ลบคอร์สแล้ว');
    }
}
