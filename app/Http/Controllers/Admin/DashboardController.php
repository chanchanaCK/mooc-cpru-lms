<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'users' => User::count(),
            'instructors' => User::where('role', 'instructor')->count(),
            'courses' => Course::count(),
            'published' => Course::where('status', 'published')->count(),
            'enrollments' => Enrollment::count(),
            'revenue' => (float) Order::where('status', 'paid')->sum('total'),
            'orders' => Order::where('status', 'paid')->count(),
            'certificates' => Certificate::count(),
        ];

        $topCourses = Course::query()
            ->with('instructor')
            ->orderByDesc('students_count')
            ->take(6)
            ->get();

        $maxStudents = max(1, (int) $topCourses->max('students_count'));

        $recentOrders = Order::with('user')
            ->where('status', 'paid')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'topCourses', 'maxStudents', 'recentOrders'));
    }
}
