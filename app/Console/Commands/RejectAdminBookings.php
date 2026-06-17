<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Services\BookingAvailability;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:reject-admin-bookings')]
#[Description('Reject active bookings that were accidentally created by admin users')]
class RejectAdminBookings extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = Booking::query()
            ->whereHas('user', fn ($query) => $query->where('role', 'admin'))
            ->whereIn('status', BookingAvailability::BLOCKING_STATUSES)
            ->update(['status' => 'REJECTED']);

        $this->info("Rejected {$updated} admin-owned active booking(s).");

        return self::SUCCESS;
    }
}
