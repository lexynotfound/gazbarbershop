<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RecentBookingChart;
use Illuminate\View\View;

class BookingChartController extends Controller
{
    public function __invoke(RecentBookingChart $chart): View
    {
        return view('admin.bookings.chart', [
            'recentBookingChart' => $chart->dailyTotals(),
        ]);
    }
}
