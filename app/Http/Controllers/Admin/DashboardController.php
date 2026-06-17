<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Capster;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'totalBookings' => Booking::count(),
            'todayBookings' => Booking::whereDate('booking_start', today())->count(),
            'totalCapsters' => Capster::count(),
            'totalCustomers' => User::where('role', 'user')->count(),
            'waitingBookings' => Booking::query()
                ->with(['user', 'capster', 'items.service'])
                ->where('status', 'PENDING')
                ->orderBy('booking_start')
                ->limit(3)
                ->get(),
        ]);
    }
}
