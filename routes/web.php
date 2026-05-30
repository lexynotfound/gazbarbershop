<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.home')->name('home');
Route::view('/layanan', 'pages.services')->name('services');
Route::view('/capster', 'pages.capsters')->name('capsters');
Route::view('/login', 'pages.login')->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::view('/register', 'pages.register')->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::view('/dashboard', 'user.dashboard')->name('dashboard');
Route::view('/profil', 'user.profile')->name('profile');
Route::view('/booking', 'user.bookings.create')->name('booking.create');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::view('/booking-saya', 'user.bookings.index')->name('bookings.index');
Route::view('/booking/detail', 'user.bookings.show')->name('booking.show');
Route::view('/booking/review', 'user.bookings.review')->name('booking.review');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::view('/', 'admin.dashboard')->name('dashboard');
    Route::view('/bookings', 'admin.bookings.index')->name('bookings.index');
    Route::view('/bookings/show', 'admin.bookings.show')->name('bookings.show');
    Route::view('/bookings/whatsapp-confirmation', 'admin.bookings.whatsapp-confirmation')->name('bookings.whatsapp');
    Route::view('/capsters', 'admin.capsters.index')->name('capsters.index');
    Route::view('/capsters/create', 'admin.capsters.create')->name('capsters.create');
    Route::view('/capsters/edit', 'admin.capsters.edit')->name('capsters.edit');
    Route::view('/services', 'admin.services.index')->name('services.index');
    Route::view('/services/create', 'admin.services.create')->name('services.create');
    Route::view('/services/edit', 'admin.services.edit')->name('services.edit');
    Route::view('/schedules', 'admin.schedules.index')->name('schedules.index');
    Route::view('/schedules/create', 'admin.schedules.create')->name('schedules.create');
    Route::view('/schedules/edit', 'admin.schedules.edit')->name('schedules.edit');
});
