<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * @var array<int, string>
     */
    private const UPCOMING_STATUSES = [
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

    /**
     * @var array<int, string>
     */
    private const CANCELLED_STATUSES = [
        'CANCELLED',
        'AUTO_CANCELLED',
        'LATE_CANCELLED',
        'REJECTED',
    ];

    public function show(Booking $booking): View
    {
        abort_if($booking->user_id !== Auth::id(), 403);

        $booking->load(['capster', 'items.service']);

        return view('user.bookings.show', compact('booking'));
    }

    public function index(): View
    {
        $bookings = Booking::query()
            ->with(['capster', 'items.service'])
            ->whereBelongsTo(Auth::user())
            ->latest('booking_start')
            ->get();

        return view('user.bookings.index', [
            'upcomingBookings' => $bookings->whereIn('status', self::UPCOMING_STATUSES)->values(),
            'finishedBookings' => $bookings->whereIn('status', self::FINISHED_STATUSES)->values(),
            'cancelledBookings' => $bookings->whereIn('status', self::CANCELLED_STATUSES)->values(),
        ]);
    }
}
