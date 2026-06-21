<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:backfill-booking-payments')]
#[Description('Create missing payment records for existing bookings')]
class BackfillBookingPayments extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $created = 0;
        $paid = 0;
        $unpaid = 0;

        Booking::query()
            ->doesntHave('payment')
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use (&$created, &$paid, &$unpaid): void {
                $bookings->each(function (Booking $booking) use (&$created, &$paid, &$unpaid): void {
                    $isPaid = in_array($booking->status, ['COMPLETED', 'REVIEWED'], true);

                    Payment::query()->create([
                        'booking_id' => $booking->id,
                        'amount' => $booking->grand_total,
                        'method' => 'cash',
                        'status' => $isPaid ? 'paid' : 'unpaid',
                        'paid_at' => $isPaid ? ($booking->completed_at ?? $booking->updated_at ?? now()) : null,
                    ]);

                    $created++;
                    $isPaid ? $paid++ : $unpaid++;
                });
            });

        $this->info("Created {$created} missing payment(s).");
        $this->info("Marked {$paid} payment(s) as paid.");
        $this->info("Marked {$unpaid} payment(s) as unpaid.");

        return self::SUCCESS;
    }
}
