<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::orderBy('sort_order')->withCount('publishedCourses')->get();

        $featured = Course::published()
            ->with(['instructor', 'category'])
            ->orderByDesc('students_count')
            ->take(8)
            ->get();

        $newest = Course::published()
            ->with(['instructor', 'category'])
            ->latest('published_at')
            ->take(4)
            ->get();

        return view('home', compact('categories', 'featured', 'newest'));
    }
}
