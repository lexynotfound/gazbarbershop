<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\RescheduleBookingRequest;
use App\Models\Booking;
use App\Services\BookingReschedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private BookingReschedule $bookingReschedule) {}

    /**
     * @var array<int, string>
     */
    private const UPCOMING_STATUSES = [
        'PENDING',
        'WAITING_CUSTOMER_CONFIRMATION',
        'WAITING_PAYMENT',
        'CONFIRMED',
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
        return view('user.bookings.index', [
            'upcomingBookings' => $this->bookingsForStatuses(self::UPCOMING_STATUSES),
        ]);
    }

    public function rescheduleForm(Booking $booking): View|RedirectResponse
    {
        abort_if($booking->user_id !== Auth::id(), 403);

        if (! in_array($booking->status, Booking::RESCHEDULABLE_STATUSES, true)) {
            return redirect()
                ->route('booking.show', $booking)
                ->with('status', "Booking {$booking->booking_code} tidak bisa dijadwalkan ulang.");
        }

        $booking->load(['capster', 'items.service']);

        return view('user.bookings.reschedule', compact('booking'));
    }

    public function reschedule(RescheduleBookingRequest $request, Booking $booking): RedirectResponse
    {
        abort_if($booking->user_id !== Auth::id(), 403);

        $this->bookingReschedule->reschedule($booking, $request->validated('booking_date'), $request->validated('booking_time'));

        return redirect()
            ->route('booking.show', $booking)
            ->with('status', "Booking {$booking->booking_code} berhasil dijadwalkan ulang.");
    }

    public function history(): View
    {
        $bookings = $this->bookingsForStatuses([
            ...self::FINISHED_STATUSES,
            ...self::CANCELLED_STATUSES,
        ]);

        return view('user.bookings.history', [
            'finishedBookings' => $bookings->whereIn('status', self::FINISHED_STATUSES)->values(),
            'cancelledBookings' => $bookings->whereIn('status', self::CANCELLED_STATUSES)->values(),
        ]);
    }

    /**
     * @param  array<int, string>  $statuses
     * @return Collection<int, Booking>
     */
    private function bookingsForStatuses(array $statuses): Collection
    {
        return Booking::query()
            ->with(['capster', 'items.service', 'review'])
            ->whereBelongsTo(Auth::user())
            ->whereIn('status', $statuses)
            ->latest('booking_start')
            ->get();
    }
}
