<?php

use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Studio\CourseController as StudioCourseController;
use App\Http\Controllers\Studio\CurriculumController;
use App\Http\Controllers\Studio\QuizController as StudioQuizController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course}', [CourseController::class, 'show'])->name('courses.show');

/*
|--------------------------------------------------------------------------
| Authenticated learner
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])->name('enroll');
    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/learn/{course}', [LearnController::class, 'show'])->name('learn.show');
    Route::get('/learn/{course}/lessons/{lesson}', [LearnController::class, 'lesson'])->name('learn.lesson');
    Route::post('/learn/{course}/lessons/{lesson}/complete', [LearnController::class, 'complete'])->name('learn.complete');
    Route::post('/learn/{course}/lessons/{lesson}/quiz', [QuizController::class, 'submit'])->name('learn.quiz.submit');
    Route::get('/learn/{course}/certificate', [CertificateController::class, 'show'])->name('certificate.show');

    // Commerce — cart / checkout / orders
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
    Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
    Route::post('/cart/{course}', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{course}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Instructor Studio (role-gated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'instructor'])->prefix('studio')->name('studio.')->group(function () {
    Route::get('/', [StudioCourseController::class, 'index'])->name('index');

    Route::get('/courses/create', [StudioCourseController::class, 'create'])->name('courses.create');
    Route::post('/courses', [StudioCourseController::class, 'store'])->name('courses.store');
    Route::get('/courses/{course}/edit', [StudioCourseController::class, 'edit'])->name('courses.edit');
    Route::put('/courses/{course}', [StudioCourseController::class, 'update'])->name('courses.update');
    Route::delete('/courses/{course}', [StudioCourseController::class, 'destroy'])->name('courses.destroy');
    Route::post('/courses/{course}/publish', [StudioCourseController::class, 'togglePublish'])->name('courses.publish');

    // Curriculum
    Route::get('/courses/{course}/curriculum', [CurriculumController::class, 'edit'])->name('courses.curriculum');
    Route::post('/courses/{course}/sections', [CurriculumController::class, 'storeSection'])->name('sections.store');
    Route::put('/sections/{section}', [CurriculumController::class, 'updateSection'])->name('sections.update');
    Route::delete('/sections/{section}', [CurriculumController::class, 'destroySection'])->name('sections.destroy');
    Route::post('/sections/{section}/lessons', [CurriculumController::class, 'storeLesson'])->name('lessons.store');
    Route::put('/lessons/{lesson}', [CurriculumController::class, 'updateLesson'])->name('lessons.update');
    Route::delete('/lessons/{lesson}', [CurriculumController::class, 'destroyLesson'])->name('lessons.destroy');

    // Quiz authoring
    Route::get('/lessons/{lesson}/quiz', [StudioQuizController::class, 'edit'])->name('lessons.quiz.edit');
    Route::post('/lessons/{lesson}/questions', [StudioQuizController::class, 'storeQuestion'])->name('questions.store');
    Route::put('/questions/{question}', [StudioQuizController::class, 'updateQuestion'])->name('questions.update');
    Route::delete('/questions/{question}', [StudioQuizController::class, 'destroyQuestion'])->name('questions.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin (role-gated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');

    Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
    Route::post('/courses/{course}/publish', [AdminCourseController::class, 'togglePublish'])->name('courses.publish');
    Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])->name('courses.destroy');

    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::post('/coupons/{coupon}/toggle', [AdminCouponController::class, 'toggle'])->name('coupons.toggle');
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');
});

require __DIR__.'/auth.php';
