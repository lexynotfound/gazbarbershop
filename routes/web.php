<?php

use App\Http\Controllers\Admin\BookingChartController as AdminBookingChartController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\CapsterController as AdminCapsterController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\User\BookingController as UserBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/layanan', [PageController::class, 'services'])->name('services');
Route::get('/capster', [PageController::class, 'capsters'])->name('capsters');
Route::view('/login', 'pages.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::view('/register', 'pages.register')->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/booking', [BookingController::class, 'create'])->name('booking.create');
Route::get('/booking/available-times', [BookingController::class, 'availableTimes'])->name('booking.available-times');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

Route::middleware(['auth', 'user'])->group(function (): void {
    Route::view('/dashboard', 'user.dashboard')->name('dashboard');
    Route::view('/profil', 'user.profile')->name('profile');
    Route::get('/booking-saya', [UserBookingController::class, 'index'])->name('bookings.index');
    Route::view('/booking/review', 'user.bookings.review')->name('booking.review');
    Route::get('/booking/{booking}', [UserBookingController::class, 'show'])->name('booking.show');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/booking-chart', AdminBookingChartController::class)->name('booking-chart');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/whatsapp-confirmation', [AdminBookingController::class, 'whatsappConfirmation'])->name('bookings.whatsapp');
    Route::patch('/bookings/{booking}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
    Route::get('/capsters', [AdminCapsterController::class, 'index'])->name('capsters.index');
    Route::get('/capsters/create', [AdminCapsterController::class, 'create'])->name('capsters.create');
    Route::post('/capsters', [AdminCapsterController::class, 'store'])->name('capsters.store');
    Route::get('/capsters/edit', [AdminCapsterController::class, 'editFirst']);
    Route::get('/capsters/{capster}/edit', [AdminCapsterController::class, 'edit'])->name('capsters.edit');
    Route::patch('/capsters/{capster}', [AdminCapsterController::class, 'update'])->name('capsters.update');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{user}/promo-whatsapp', [AdminCustomerController::class, 'promoWhatsapp'])->name('customers.promo-whatsapp');
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [AdminReviewController::class, 'show'])->name('reviews.show');
    Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create', [AdminServiceController::class, 'create'])->name('services.create');
    Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{service}/edit', [AdminServiceController::class, 'edit'])->name('services.edit');
    Route::patch('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
    Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');
    Route::get('/schedules/create', [AdminScheduleController::class, 'create'])->name('schedules.create');
    Route::get('/schedules/edit', [AdminScheduleController::class, 'editFirst']);
    Route::get('/schedules/capster/{capster}', [AdminScheduleController::class, 'byCapster'])->name('schedules.by-capster');
    Route::get('/schedules/{schedule}/edit', [AdminScheduleController::class, 'edit'])->name('schedules.edit');
    Route::get('/schedules/{schedule}', [AdminScheduleController::class, 'show'])->name('schedules.show');
});
