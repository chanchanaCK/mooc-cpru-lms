<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function edit(Request $request, Course $course): View
    {
        $this->authorize('manage', $course);

        $course->load(['sections.lessons' => fn ($q) => $q->withCount('questions')]);

        return view('studio.courses.curriculum', compact('course'));
    }

    /* ---- Sections ---------------------------------------------------- */

    public function storeSection(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('manage', $course);

        $data = $request->validate(['title' => ['required', 'string', 'max:180']]);

        $course->sections()->create([
            'title' => $data['title'],
            'sort_order' => (int) $course->sections()->max('sort_order') + 1,
        ]);

        return back()->with('success', 'เพิ่มบทแล้ว');
    }

    public function updateSection(Request $request, Section $section): RedirectResponse
    {
        $this->authorize('manage', $section->course);

        $data = $request->validate(['title' => ['required', 'string', 'max:180']]);
        $section->update($data);

        return back()->with('success', 'แก้ไขบทแล้ว');
    }

    public function destroySection(Request $request, Section $section): RedirectResponse
    {
        $course = $section->course;
        $this->authorize('manage', $course);

        $section->delete(); // cascades lessons
        $course->syncContentCounts();

        return back()->with('success', 'ลบบทแล้ว');
    }

    /* ---- Lessons ----------------------------------------------------- */

    public function storeLesson(Request $request, Section $section): RedirectResponse
    {
        $course = $section->course;
        $this->authorize('manage', $course);

        $data = $this->validateLesson($request);

        $section->lessons()->create($this->lessonPayload($data, $course, [
            'sort_order' => (int) $course->lessons()->max('sort_order') + 1,
        ]));

        $course->syncContentCounts();

        return back()->with('success', 'เพิ่มบทเรียนแล้ว');
    }

    public function updateLesson(Request $request, Lesson $lesson): RedirectResponse
    {
        $course = $lesson->course;
        $this->authorize('manage', $course);

        $data = $this->validateLesson($request);
        $lesson->update($this->lessonPayload($data, $course));
        $course->syncContentCounts();

        return back()->with('success', 'แก้ไขบทเรียนแล้ว');
    }

    public function destroyLesson(Request $request, Lesson $lesson): RedirectResponse
    {
        $course = $lesson->course;
        $this->authorize('manage', $course);

        $lesson->delete();
        $course->syncContentCounts();

        return back()->with('success', 'ลบบทเรียนแล้ว');
    }

    /* ---------------------------------------------------------------- */

    private function validateLesson(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', 'in:video,article,quiz'],
            'video_provider' => ['nullable', 'in:youtube,vimeo'],
            'video' => ['nullable', 'string', 'max:500'],
            'duration_minutes' => ['nullable', 'integer', 'min:0', 'max:600'],
            'content' => ['nullable', 'string', 'max:20000'],
            'is_preview' => ['nullable', 'boolean'],
        ]);
    }

    private function lessonPayload(array $data, Course $course, array $extra = []): array
    {
        $isVideo = $data['type'] === 'video';

        return array_merge([
            'course_id' => $course->id,
            'title' => $data['title'],
            'type' => $data['type'],
            'video_provider' => $isVideo ? ($data['video_provider'] ?? 'youtube') : null,
            'video_id' => $isVideo ? Lesson::extractVideoId($data['video_provider'] ?? 'youtube', $data['video'] ?? null) : null,
            'duration_seconds' => $isVideo ? (int) ($data['duration_minutes'] ?? 0) * 60 : 0,
            'content' => $isVideo ? null : ($data['content'] ?? null),
            'is_preview' => (bool) ($data['is_preview'] ?? false),
        ], $extra);
    }
}
