<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cancel-late-bookings')]
#[Description('Cancel active bookings that are more than 15 minutes late and have not checked in')]
class CancelLateBookings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $autoCancelled = Booking::query()
            ->where('status', 'WAITING_CUSTOMER_CONFIRMATION')
            ->whereNotNull('customer_response_deadline')
            ->where('customer_response_deadline', '<=', now())
            ->update([
                'status' => 'AUTO_CANCELLED',
            ]);

        $lateCancelled = Booking::query()
            ->whereIn('status', Booking::LATE_CANCELLABLE_STATUSES)
            ->whereNull('checked_in_at')
            ->where('booking_start', '<=', now()->subMinutes(15))
            ->update([
                'status' => 'LATE_CANCELLED',
            ]);

        $this->info("Auto-cancelled {$autoCancelled} unconfirmed booking(s).");
        $this->info("Late-cancelled {$lateCancelled} booking(s).");

        return self::SUCCESS;
    }
}
