<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Notifications\BookingCancelledNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

#[Signature('app:cancel-late-bookings')]
#[Description('Cancel active bookings that are more than 15 minutes late and have not checked in')]
class CancelLateBookings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $autoCancelled = $this->cancelBookings(
            Booking::query()
                ->where('status', 'WAITING_CUSTOMER_CONFIRMATION')
                ->whereNotNull('customer_response_deadline')
                ->where('customer_response_deadline', '<=', now()),
            'AUTO_CANCELLED',
            'NO_CONFIRMATION',
        );

        $lateCancelled = $this->cancelBookings(
            Booking::query()
                ->whereIn('status', Booking::LATE_CANCELLABLE_STATUSES)
                ->whereNull('checked_in_at')
                ->where('booking_start', '<=', now()->subMinutes(15)),
            'LATE_CANCELLED',
            'LATE_ARRIVAL',
        );

        $this->info("Auto-cancelled {$autoCancelled} unconfirmed booking(s).");
        $this->info("Late-cancelled {$lateCancelled} booking(s).");

        return self::SUCCESS;
    }

    private function cancelBookings(Builder $query, string $status, string $reason): int
    {
        $cancelled = 0;

        $query->with(['user', 'capster'])->chunkById(100, function (Collection $bookings) use (&$cancelled, $status, $reason): void {
            foreach ($bookings as $booking) {
                $booking->update(['status' => $status]);
                $booking->user->notify(new BookingCancelledNotification($booking, $reason));
                $cancelled++;
            }
        });

        return $cancelled;
    }
}
