<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const ACTIVE_STATUSES = [
        'PENDING',
        'WAITING_CUSTOMER_CONFIRMATION',
        'WAITING_PAYMENT',
        'ACCEPTED',
        'CHECKED_IN',
        'IN_PROGRESS',
        'PAID',
    ];

    /**
     * @var array<int, string>
     */
    private const FINISHED_STATUSES = [
        'COMPLETED',
        'REVIEWED',
    ];

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('user.dashboard', [
            'activeBookingsCount' => Booking::query()
                ->whereBelongsTo($user)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->count(),
            'finishedBookingsCount' => Booking::query()
                ->whereBelongsTo($user)
                ->whereIn('status', self::FINISHED_STATUSES)
                ->count(),
            'reviewsCount' => Review::query()
                ->whereBelongsTo($user)
                ->count(),
        ]);
    }
}
