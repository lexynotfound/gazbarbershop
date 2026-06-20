<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Collection;

class RecentBookingChart
{
    /**
     * @return Collection<int, array{date: string, label: string, total: int, height: int}>
     */
    public function dailyTotals(int $days = 30): Collection
    {
        $start = today()->subDays($days - 1)->startOfDay();
        $end = today()->endOfDay();

        $totals = Booking::query()
            ->whereBetween('booking_start', [$start, $end])
            ->selectRaw('DATE(booking_start) as booking_date, COUNT(*) as total')
            ->groupByRaw('DATE(booking_start)')
            ->pluck('total', 'booking_date');

        $highestTotal = max((int) $totals->max(), 1);

        return collect(range(0, $days - 1))
            ->map(function (int $dayOffset) use ($start, $totals, $highestTotal): array {
                $date = $start->copy()->addDays($dayOffset);
                $total = (int) ($totals[$date->toDateString()] ?? 0);

                return [
                    'date' => $date->toDateString(),
                    'label' => $date->translatedFormat('d M'),
                    'total' => $total,
                    'height' => $total > 0 ? max(8, (int) round(($total / $highestTotal) * 100)) : 0,
                ];
            });
    }
}
