<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request, BookingController $bookingController): RedirectResponse
    {
        $user = User::query()->create($request->validated() + ['role' => 'user']);

        Auth::login($user);
        $request->session()->regenerate();

        $bookingController->completePendingBooking($request);

        return redirect()
            ->route('bookings.index')
            ->with('status', 'Akun berhasil dibuat dan booking kamu sudah tersimpan.');
    }

    public function login(LoginUserRequest $request, BookingController $bookingController): RedirectResponse
    {
        if (! Auth::attempt($request->validated())) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak cocok.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        if (Auth::user()->role === 'user') {
            $bookingController->completePendingBooking($request);
        }

        $route = Auth::user()->role === 'admin' ? 'admin.dashboard' : 'bookings.index';

        return redirect()
            ->route($route)
            ->with('status', 'Berhasil masuk.');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }
}
