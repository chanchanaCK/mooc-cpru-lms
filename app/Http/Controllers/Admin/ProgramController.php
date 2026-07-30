<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin control over credit-bank programs — same capabilities as the registrar
 * area, surfaced inside the admin console (<x-admin-layout>).
 */
class ProgramController extends Controller
{
    public function index(): View
    {
        $programs = Program::withCount(['courses', 'enrollments'])
            ->orderBy('title')
            ->get();

        return view('admin.programs.index', compact('programs'));
    }

    public function create(): View
    {
        $this->authorize('create', Program::class);

        return view('admin.programs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Program::class);

        $data = $this->validateProgram($request);

        $program = Program::create([
            ...$data,
            'slug' => Program::generateUniqueSlug($data['title']),
            'status' => 'draft',
            'owner_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.programs.edit', $program)
            ->with('success', 'สร้างหลักสูตรแล้ว — เพิ่มรายวิชาเข้าหลักสูตรได้เลย');
    }

    public function edit(Program $program): View
    {
        $this->authorize('manage', $program);

        $program->load('courses');

        $availableCourses = Course::where('credit_bearing', true)
            ->whereNotIn('id', $program->courses->pluck('id'))
            ->orderBy('title')
            ->get();

        return view('admin.programs.edit', compact('program', 'availableCourses'));
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $this->authorize('manage', $program);

        $program->update($this->validateProgram($request));

        return back()->with('success', 'บันทึกหลักสูตรแล้ว');
    }

    public function destroy(Program $program): RedirectResponse
    {
        $this->authorize('delete', $program);

        $program->delete();

        return redirect()->route('admin.programs.index')->with('success', 'ลบหลักสูตรแล้ว');
    }

    public function togglePublish(Program $program): RedirectResponse
    {
        $this->authorize('manage', $program);

        if ($program->status === 'published') {
            $program->update(['status' => 'draft']);

            return back()->with('success', 'ย้ายหลักสูตรกลับเป็นฉบับร่างแล้ว');
        }

        if ($program->courses()->count() === 0) {
            return back()->with('error', 'ต้องมีรายวิชาอย่างน้อย 1 วิชาก่อนเผยแพร่');
        }

        $program->update(['status' => 'published']);

        return back()->with('success', 'เผยแพร่หลักสูตรแล้ว');
    }

    public function attachCourse(Request $request, Program $program): RedirectResponse
    {
        $this->authorize('manage', $program);

        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'requirement' => ['required', 'in:required,elective'],
        ]);

        $nextSort = (int) $program->courses()->max('program_courses.sort_order') + 1;

        $program->courses()->syncWithoutDetaching([
            $data['course_id'] => ['requirement' => $data['requirement'], 'sort_order' => $nextSort],
        ]);

        return back()->with('success', 'เพิ่มรายวิชาเข้าหลักสูตรแล้ว');
    }

    public function detachCourse(Program $program, Course $course): RedirectResponse
    {
        $this->authorize('manage', $program);

        $program->courses()->detach($course->id);

        return back()->with('success', 'นำรายวิชาออกจากหลักสูตรแล้ว');
    }

    private function validateProgram(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['required', 'exists:qualification_types,slug'],
            'nqf_level' => ['nullable', 'integer', 'exists:qualification_levels,level'],
            'required_credits' => ['required', 'numeric', 'min:0', 'max:999'],
            'duration_months' => ['nullable', 'integer', 'min:0', 'max:120'],
        ]);
    }
}
