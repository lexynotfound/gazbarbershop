<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CapsterSchedule;
use App\Notifications\BookingRescheduledNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BookingReschedule
{
    public function reschedule(Booking $booking, string $date, string $time): Booking
    {
        return DB::transaction(function () use ($booking, $date, $time): Booking {
            $booking->loadMissing('items');

            $previousBookingStart = $booking->booking_start;
            $durationMinutes = (int) $booking->items->sum('duration_minutes');

            $bookingStart = CarbonImmutable::createFromFormat('Y-m-d H:i', "{$date} {$time}");
            $bookingEnd = $bookingStart->addMinutes($durationMinutes);

            CapsterSchedule::query()
                ->where('capster_id', $booking->capster_id)
                ->whereDate('work_date', $date)
                ->lockForUpdate()
                ->get();

            $booking->update([
                'booking_start' => $bookingStart,
                'booking_end' => $bookingEnd,
                'status' => 'PENDING',
                'admin_confirmed_at' => null,
                'customer_response_deadline' => null,
                'checked_in_at' => null,
                'completed_at' => null,
            ]);

            $booking->user->notify(new BookingRescheduledNotification($booking->fresh(['capster']), $previousBookingStart));

            return $booking;
        });
    }
}
