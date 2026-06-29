<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\CapsterSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class BookingAvailability
{
    public const SLOT_INTERVAL_MINUTES = 60;

    /**
     * @var array<int, string>
     */
    public const BLOCKING_STATUSES = [
        'PENDING',
        'WAITING_CUSTOMER_CONFIRMATION',
        'WAITING_PAYMENT',
        'CONFIRMED',
        'CHECKED_IN',
        'IN_PROGRESS',
        'PAID',
    ];

    /**
     * @return array<int, array{time: string, label: string, available: bool, status: string, booking_code: ?string, customer_name: ?string}>
     */
    public function slotsForSchedule(CapsterSchedule $schedule, int $durationMinutes = self::SLOT_INTERVAL_MINUTES): array
    {
        return $this->buildSlots(
            $schedule,
            $durationMinutes,
            $this->blockingBookingsForSchedule($schedule),
            $schedule->is_available,
        );
    }

    /**
     * @return array<int, array{time: string, label: string, available: bool, status: string, booking_code: ?string, customer_name: ?string}>
     */
    public function slotsForCapsterDate(int $capsterId, string $date, int $durationMinutes): array
    {
        $schedules = CapsterSchedule::query()
            ->where('capster_id', $capsterId)
            ->whereDate('work_date', $date)
            ->orderBy('start_time')
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $dayStart = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date.' 00:00:00');
        $dayEnd = $dayStart->addDay();
        $bookings = $this->blockingBookings($capsterId, $dayStart, $dayEnd);

        return $schedules
            ->flatMap(fn (CapsterSchedule $schedule): array => $this->buildSlots(
                $schedule,
                $durationMinutes,
                $bookings,
                $schedule->is_available,
            ))
            ->unique('time')
            ->values()
            ->all();
    }

    public function isAvailable(int $capsterId, string $date, string $time, int $durationMinutes): bool
    {
        return collect($this->slotsForCapsterDate($capsterId, $date, $durationMinutes))
            ->contains(fn (array $slot): bool => $slot['time'] === $time && $slot['available']);
    }

    /**
     * @return Collection<int, Booking>
     */
    public function blockingBookingsForSchedule(CapsterSchedule $schedule): Collection
    {
        $date = $schedule->work_date->toDateString();
        $start = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date.' '.$schedule->start_time);
        $end = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date.' '.$schedule->end_time);

        return $this->blockingBookings($schedule->capster_id, $start, $end);
    }

    /**
     * @return Collection<int, Booking>
     */
    private function blockingBookings(int $capsterId, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        return Booking::query()
            ->with('user')
            ->where('capster_id', $capsterId)
            ->whereIn('status', self::BLOCKING_STATUSES)
            ->where('booking_start', '<', $end)
            ->where('booking_end', '>', $start)
            ->orderBy('booking_start')
            ->get();
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     * @return array<int, array{time: string, label: string, available: bool, status: string, booking_code: ?string, customer_name: ?string}>
     */
    private function buildSlots(CapsterSchedule $schedule, int $durationMinutes, Collection $bookings, bool $scheduleAvailable): array
    {
        $date = $schedule->work_date->toDateString();
        $cursor = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date.' '.$schedule->start_time);
        $scheduleEnd = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $date.' '.$schedule->end_time);
        $slots = [];

        while ($cursor->addMinutes($durationMinutes)->lessThanOrEqualTo($scheduleEnd)) {
            $slotEnd = $cursor->addMinutes($durationMinutes);
            $booking = $bookings->first(fn (Booking $booking): bool => $booking->booking_start->lessThan($slotEnd) && $booking->booking_end->greaterThan($cursor));

            $slots[] = [
                'time' => $cursor->format('H:i'),
                'label' => $cursor->format('H:i').'-'.$slotEnd->format('H:i'),
                'available' => $scheduleAvailable && $booking === null,
                'status' => ! $scheduleAvailable ? 'Tidak tersedia' : ($booking ? 'Terbooking' : 'Tersedia'),
                'booking_code' => $booking?->booking_code,
                'customer_name' => $booking?->user?->name,
            ];

            $cursor = $cursor->addMinutes(self::SLOT_INTERVAL_MINUTES);
        }

        return $slots;
    }
}
