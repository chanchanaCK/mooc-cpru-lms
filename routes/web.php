<?php

use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrganizationController as AdminOrganizationController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\QualificationController as AdminQualificationController;
use App\Http\Controllers\Admin\TransferController as AdminTransferController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CreditBankController;
use App\Http\Controllers\CreditTransferController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LearnController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Org\AssignmentController as OrgAssignmentController;
use App\Http\Controllers\Org\MemberController as OrgMemberController;
use App\Http\Controllers\Org\OrganizationController as OrgController;
use App\Http\Controllers\Org\ProgramAssignmentController as OrgProgramAssignmentController;
use App\Http\Controllers\Org\ReportController as OrgReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProgramEnrollmentController;
use App\Http\Controllers\Registrar\DashboardController as RegistrarDashboardController;
use App\Http\Controllers\Registrar\ProgramController as RegistrarProgramController;
use App\Http\Controllers\Registrar\TransferController as RegistrarTransferController;
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

// Programs (หลักสูตรสะสมหน่วยกิต) — public catalog
Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
Route::get('/programs/{program}', [ProgramController::class, 'show'])->name('programs.show');

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

    // Credit Bank (คลังหน่วยกิต)
    Route::get('/credit-bank', [CreditBankController::class, 'index'])->name('credit-bank.index');
    Route::get('/credit-bank/transcript', [CreditBankController::class, 'transcript'])->name('credit-bank.transcript');
    Route::get('/credit-bank/transfers', [CreditTransferController::class, 'index'])->name('credit-bank.transfers.index');
    Route::post('/credit-bank/transfers', [CreditTransferController::class, 'store'])->name('credit-bank.transfers.store');

    // Programs — enrol & qualification
    Route::post('/programs/{program}/enroll', [ProgramEnrollmentController::class, 'store'])->name('programs.enroll');
    Route::get('/programs/{program}/qualification', [ProgramEnrollmentController::class, 'qualification'])->name('programs.qualification');

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
| Organizations (B2B)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('org')->name('org.')->group(function () {
    Route::get('/', [OrgController::class, 'index'])->name('index');
    Route::get('/create', [OrgController::class, 'create'])->name('create');
    Route::post('/', [OrgController::class, 'store'])->name('store');
    Route::get('/{organization}', [OrgController::class, 'show'])->name('show');
    Route::get('/{organization}/report', [OrgReportController::class, 'show'])->name('report');

    Route::post('/{organization}/members', [OrgMemberController::class, 'store'])->name('members.store');
    Route::delete('/{organization}/members/{user}', [OrgMemberController::class, 'destroy'])->name('members.destroy');

    Route::post('/{organization}/assignments', [OrgAssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('/{organization}/assignments/{course}', [OrgAssignmentController::class, 'destroy'])->name('assignments.destroy');

    Route::post('/{organization}/program-assignments', [OrgProgramAssignmentController::class, 'store'])->name('program-assignments.store');
    Route::delete('/{organization}/program-assignments/{program}', [OrgProgramAssignmentController::class, 'destroy'])->name('program-assignments.destroy');
});

/*
|--------------------------------------------------------------------------
| Registrar (นายทะเบียน) — programs & credit transfers
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'registrar'])->prefix('registrar')->name('registrar.')->group(function () {
    Route::get('/', [RegistrarDashboardController::class, 'index'])->name('dashboard');

    Route::get('/programs', [RegistrarProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [RegistrarProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [RegistrarProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}/edit', [RegistrarProgramController::class, 'edit'])->name('programs.edit');
    Route::put('/programs/{program}', [RegistrarProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [RegistrarProgramController::class, 'destroy'])->name('programs.destroy');
    Route::post('/programs/{program}/publish', [RegistrarProgramController::class, 'togglePublish'])->name('programs.publish');
    Route::post('/programs/{program}/courses', [RegistrarProgramController::class, 'attachCourse'])->name('programs.courses.attach');
    Route::delete('/programs/{program}/courses/{course}', [RegistrarProgramController::class, 'detachCourse'])->name('programs.courses.detach');

    // Credit transfer / RPL review
    Route::get('/transfers', [RegistrarTransferController::class, 'index'])->name('transfers.index');
    Route::post('/transfers/{transfer}/approve', [RegistrarTransferController::class, 'approve'])->name('transfers.approve');
    Route::post('/transfers/{transfer}/reject', [RegistrarTransferController::class, 'reject'])->name('transfers.reject');
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
    Route::get('/users/{user}/profile', [AdminUserController::class, 'editProfile'])->name('users.profile.edit');
    Route::put('/users/{user}/profile', [AdminUserController::class, 'updateProfile'])->name('users.profile.update');

    Route::get('/courses', [AdminCourseController::class, 'index'])->name('courses.index');
    Route::post('/courses/{course}/publish', [AdminCourseController::class, 'togglePublish'])->name('courses.publish');
    Route::delete('/courses/{course}', [AdminCourseController::class, 'destroy'])->name('courses.destroy');

    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::post('/coupons/{coupon}/toggle', [AdminCouponController::class, 'toggle'])->name('coupons.toggle');
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');

    // Credit bank — programs
    Route::get('/programs', [AdminProgramController::class, 'index'])->name('programs.index');
    Route::get('/programs/create', [AdminProgramController::class, 'create'])->name('programs.create');
    Route::post('/programs', [AdminProgramController::class, 'store'])->name('programs.store');
    Route::get('/programs/{program}/edit', [AdminProgramController::class, 'edit'])->name('programs.edit');
    Route::put('/programs/{program}', [AdminProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [AdminProgramController::class, 'destroy'])->name('programs.destroy');
    Route::post('/programs/{program}/publish', [AdminProgramController::class, 'togglePublish'])->name('programs.publish');
    Route::post('/programs/{program}/courses', [AdminProgramController::class, 'attachCourse'])->name('programs.courses.attach');
    Route::delete('/programs/{program}/courses/{course}', [AdminProgramController::class, 'detachCourse'])->name('programs.courses.detach');

    // Credit bank — transfers (RPL)
    Route::get('/transfers', [AdminTransferController::class, 'index'])->name('transfers.index');
    Route::post('/transfers/{transfer}/approve', [AdminTransferController::class, 'approve'])->name('transfers.approve');
    Route::post('/transfers/{transfer}/reject', [AdminTransferController::class, 'reject'])->name('transfers.reject');

    // Organizations oversight
    Route::get('/organizations', [AdminOrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/{organization}', [AdminOrganizationController::class, 'show'])->name('organizations.show');

    // Qualification types & levels (ประเภท/ระดับคุณวุฒิ)
    Route::get('/qualifications', [AdminQualificationController::class, 'index'])->name('qualifications.index');
    Route::post('/qualifications/types', [AdminQualificationController::class, 'storeType'])->name('qualifications.types.store');
    Route::put('/qualifications/types/{type}', [AdminQualificationController::class, 'updateType'])->name('qualifications.types.update');
    Route::delete('/qualifications/types/{type}', [AdminQualificationController::class, 'destroyType'])->name('qualifications.types.destroy');
    Route::post('/qualifications/levels', [AdminQualificationController::class, 'storeLevel'])->name('qualifications.levels.store');
    Route::put('/qualifications/levels/{level}', [AdminQualificationController::class, 'updateLevel'])->name('qualifications.levels.update');
    Route::delete('/qualifications/levels/{level}', [AdminQualificationController::class, 'destroyLevel'])->name('qualifications.levels.destroy');
});

require __DIR__.'/auth.php';
