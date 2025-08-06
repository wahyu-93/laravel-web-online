<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/pricing', [FrontController::class, 'pricing'])->name('front.pricing');

Route::match(['get','post'], '/booking/payment/midtrans/notification', [FrontController::class, 'PaymentMidtransNotifications'])->name('front.payment_midtrans_notifications');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::middleware('role:student')->group(function(){
        Route::get('/dashboard/subscriptions/', [DashboardController::class, 'subcriptions'])->name('dashboard.subscriptions');
        Route::get('/dashboard/subscriptions/{transaction}', [DashboardController::class, 'subcriptions_detail'])->name('dashboard.subscription.details');

        Route::get('/dashboard/courses/', [CourseController::class, 'index'])->name('dashboard.courses');
        Route::get('/dashboard/courses/{course:slug}', [CourseController::class, 'details'])->name('dashboard.course.details');
        Route::get('/dashboard/search/courses/', [CourseController::class, 'searchCourses'])->name('dashboard.search.courses');

        Route::middleware(['check.subscription'])->group(function(){
            Route::get('/dashboard/join/{course:slug}', [CourseController::class, 'join'])->name('dashboard.course.join');
            Route::get('/dashboard/learning/{course:slug}/{courseSection}/{sectionContent}', [CourseController::class, 'learning'])->name('dashboard.course.learning');
            Route::get('/dashboard/learning/{course:slug}/finished', [CourseController::class, 'learningFinished'])->name('dashboard.course.learning.finished');
        });

        Route::get('/checkout/success',[FrontController::class, 'checkoutSuccess'])->name('front.checkout.success');
        Route::get('/checkout/{pricing}',[FrontController::class, 'checkout'])->name('front.checkout');

        Route::post('/booking/payment/midtrans', [FrontController::class, 'paymentStoreMidtrans'])->name('front.payment_store_midtrans');
    });
});

require __DIR__.'/auth.php';
