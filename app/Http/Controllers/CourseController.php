<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /** Course catalog with search, category / level filters and sorting. */
    public function index(Request $request): View
    {
        $categories = Category::orderBy('sort_order')->get();

        $sort = $request->string('sort')->toString();

        $courses = Course::published()
            ->with(['instructor', 'category'])
            ->search($request->string('q')->toString() ?: null)
            ->when($request->filled('category'), function ($query) use ($request) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->category));
            })
            ->when($request->filled('level'), fn ($query) => $query->where('level', $request->level))
            ->when($request->boolean('free'), fn ($query) => $query->where('price', 0))
            ->when($sort === 'newest', fn ($q) => $q->latest('published_at'))
            ->when($sort === 'rating', fn ($q) => $q->orderByDesc('rating_avg'))
            ->when($sort === 'price_low', fn ($q) => $q->orderBy('price'))
            ->when($sort === 'price_high', fn ($q) => $q->orderByDesc('price'))
            ->when(! in_array($sort, ['newest', 'rating', 'price_low', 'price_high'], true),
                fn ($q) => $q->orderByDesc('students_count'))
            ->paginate(12)
            ->withQueryString();

        return view('courses.index', compact('courses', 'categories'));
    }

    /** Public course detail / sales page. */
    public function show(Course $course): View
    {
        abort_unless($course->status === 'published', 404);

        $course->load([
            'instructor',
            'category',
            'sections.lessons',
            'reviews.user',
        ]);

        $user = request()->user();
        $isEnrolled = $user ? $user->isEnrolledIn($course) : false;
        $inCart = $user ? $user->hasInCart($course) : false;

        return view('courses.show', compact('course', 'isEnrolled', 'inCart'));
    }
}
