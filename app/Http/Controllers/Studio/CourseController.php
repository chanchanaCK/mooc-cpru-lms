<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CourseController extends Controller
{
    /** The instructor's own courses (admins see everything). */
    public function index(Request $request): View
    {
        $user = $request->user();

        $courses = ($user->isAdmin() ? Course::query() : $user->courses())
            ->with('category')
            ->withCount('lessons')
            ->latest()
            ->get();

        return view('studio.index', compact('courses'));
    }

    public function create(): View
    {
        return view('studio.courses.create', ['categories' => $this->categories()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCourse($request);

        $course = Course::create([
            'instructor_id' => $request->user()->id,
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => Course::generateUniqueSlug($data['title']),
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'level' => $data['level'],
            'language' => 'th',
            'status' => 'draft',
            'thumbnail' => $this->storeThumbnail($request),
            ...$this->creditAttributes($request, $data),
        ]);

        return redirect()
            ->route('studio.courses.curriculum', $course)
            ->with('success', 'สร้างคอร์สแล้ว — เพิ่มบทเรียนได้เลย');
    }

    public function edit(Request $request, Course $course): View
    {
        $this->authorize('manage', $course);

        return view('studio.courses.edit', [
            'course' => $course,
            'categories' => $this->categories(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('manage', $course);

        $data = $this->validateCourse($request);

        $course->fill([
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'level' => $data['level'],
            ...$this->creditAttributes($request, $data),
        ]);

        if ($file = $this->storeThumbnail($request)) {
            $this->deleteThumbnail($course);
            $course->thumbnail = $file;
        }

        $course->save();

        return back()->with('success', 'บันทึกข้อมูลคอร์สแล้ว');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $course->delete(); // soft delete

        return redirect()->route('studio.index')->with('success', 'ลบคอร์สแล้ว');
    }

    /** Toggle published / draft. Publishing requires at least one lesson. */
    public function togglePublish(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('manage', $course);

        if ($course->status === 'published') {
            $course->update(['status' => 'draft']);

            return back()->with('success', 'ย้ายคอร์สกลับเป็นฉบับร่างแล้ว');
        }

        if ($course->lessons()->count() === 0) {
            return back()->with('error', 'ต้องมีอย่างน้อย 1 บทเรียนก่อนเผยแพร่');
        }

        $course->update([
            'status' => 'published',
            'published_at' => $course->published_at ?? now(),
        ]);

        return back()->with('success', 'เผยแพร่คอร์สแล้ว');
    }

    /* ---------------------------------------------------------------- */

    private function categories()
    {
        return Category::orderBy('sort_order')->get();
    }

    /** Build the credit-bank attributes from validated input. */
    private function creditAttributes(Request $request, array $data): array
    {
        $bearing = $request->boolean('credit_bearing');

        return [
            'course_code' => $data['course_code'] ?? null,
            'credit_bearing' => $bearing,
            'credits' => $bearing ? ($data['credits'] ?? 0) : 0,
            'learning_hours' => $data['learning_hours'] ?? 0,
            'grading_method' => $data['grading_method'] ?? 'pass_fail',
            'pass_threshold' => $data['pass_threshold'] ?? 70,
        ];
    }

    private function validateCourse(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'price' => ['required', 'numeric', 'min:0', 'max:100000'],
            'level' => ['required', 'in:beginner,intermediate,advanced'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            // Credit bank (คลังหน่วยกิต)
            'course_code' => ['nullable', 'string', 'max:32'],
            'credit_bearing' => ['nullable', 'boolean'],
            'credits' => ['nullable', 'numeric', 'min:0', 'max:99'],
            'learning_hours' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'grading_method' => ['nullable', 'in:pass_fail,graded'],
            'pass_threshold' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);
    }

    private function storeThumbnail(Request $request): ?string
    {
        if (! $request->hasFile('thumbnail')) {
            return null;
        }

        return $request->file('thumbnail')->store('thumbnails', 'public');
    }

    private function deleteThumbnail(Course $course): void
    {
        if ($course->thumbnail && ! str_starts_with($course->thumbnail, 'http')) {
            Storage::disk('public')->delete($course->thumbnail);
        }
    }
}
