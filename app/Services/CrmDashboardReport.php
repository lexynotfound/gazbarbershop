<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Capster;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class CrmDashboardReport
{
    public function __construct(private CustomerSegmentation $segmentation) {}

    /**
     * @return array{
     *     month: string,
     *     periodLabel: string,
     *     activeCustomersCount: int,
     *     repeatCustomersCount: int,
     *     loyalCustomersCount: int,
     *     customers: array<int, array{name: string, completedBookingsCount: int, periodBookingsCount: int, status: string, description: string}>,
     *     services: array<int, array{name: string, transactionCount: int}>,
     *     serviceMaxTransactions: int,
     *     favoriteService: ?array{name: string, transactionCount: int},
     *     capsters: array<int, array{name: string, bookingCount: int, averageRating: ?float}>,
     *     favoriteCapster: ?array{name: string, bookingCount: int, averageRating: ?float},
     *     notes: array<int, string>
     * }
     */
    public function forMonth(?string $month): array
    {
        $periodStart = $this->resolveMonth($month);
        $periodEnd = $periodStart->endOfMonth();
        $periodLabel = $periodStart->translatedFormat('F Y');

        $activeCustomersCount = $this->activeCustomerIds($periodStart, $periodEnd)->count('user_id');
        $repeatCustomersCount = $this->segmentedCustomersCount(
            $periodStart,
            $periodEnd,
            CustomerSegmentation::REPEAT_ORDER_THRESHOLD,
            CustomerSegmentation::LOYAL_CUSTOMER_THRESHOLD,
        );
        $loyalCustomersCount = $this->segmentedCustomersCount(
            $periodStart,
            $periodEnd,
            CustomerSegmentation::LOYAL_CUSTOMER_THRESHOLD,
        );
        $customers = $this->customers($periodStart, $periodEnd);
        $services = $this->services($periodStart, $periodEnd);
        $capsters = $this->capsters($periodStart, $periodEnd);
        $favoriteService = $services[0] ?? null;
        $favoriteCapster = $capsters[0] ?? null;

        return [
            'month' => $periodStart->format('Y-m'),
            'periodLabel' => $periodLabel,
            'activeCustomersCount' => $activeCustomersCount,
            'repeatCustomersCount' => $repeatCustomersCount,
            'loyalCustomersCount' => $loyalCustomersCount,
            'customers' => $customers,
            'services' => $services,
            'serviceMaxTransactions' => collect($services)->max('transactionCount') ?? 0,
            'favoriteService' => $favoriteService,
            'capsters' => $capsters,
            'favoriteCapster' => $favoriteCapster,
            'notes' => $this->notes(
                $periodLabel,
                $activeCustomersCount,
                $repeatCustomersCount,
                $loyalCustomersCount,
                $favoriteService,
                $favoriteCapster,
            ),
        ];
    }

    private function resolveMonth(?string $month): CarbonImmutable
    {
        if (is_string($month) && preg_match('/\A(\d{4})-(0[1-9]|1[0-2])\z/', $month, $matches) === 1) {
            return CarbonImmutable::create((int) $matches[1], (int) $matches[2], 1)->startOfDay();
        }

        return CarbonImmutable::today()->startOfMonth();
    }

    /**
     * @return Builder<Booking>
     */
    private function activeCustomerIds(CarbonImmutable $periodStart, CarbonImmutable $periodEnd): Builder
    {
        return Booking::query()
            ->select('user_id')
            ->whereIn('status', Booking::FINISHED_STATUSES)
            ->whereBetween('booking_start', [$periodStart, $periodEnd])
            ->whereIn('user_id', User::query()->select('id')->where('role', 'user'))
            ->distinct();
    }

    private function segmentedCustomersCount(
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
        int $minimumBookings,
        ?int $maximumBookings = null,
    ): int {
        return Booking::query()
            ->select('user_id')
            ->whereIn('status', Booking::FINISHED_STATUSES)
            ->where('booking_start', '<=', $periodEnd)
            ->whereIn('user_id', $this->activeCustomerIds($periodStart, $periodEnd))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= ?', [$minimumBookings])
            ->when($maximumBookings !== null, fn (Builder $query): Builder => $query->havingRaw('COUNT(*) < ?', [$maximumBookings]))
            ->get()
            ->count();
    }

    /**
     * @return array<int, array{name: string, completedBookingsCount: int, periodBookingsCount: int, status: string, description: string}>
     */
    private function customers(CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        return User::query()
            ->select(['id', 'name'])
            ->where('role', 'user')
            ->whereIn('id', $this->activeCustomerIds($periodStart, $periodEnd))
            ->withCount([
                'bookings as completed_bookings_count' => fn (Builder $query): Builder => $query
                    ->whereIn('status', Booking::FINISHED_STATUSES)
                    ->where('booking_start', '<=', $periodEnd),
                'bookings as period_bookings_count' => fn (Builder $query): Builder => $query
                    ->whereIn('status', Booking::FINISHED_STATUSES)
                    ->whereBetween('booking_start', [$periodStart, $periodEnd]),
            ])
            ->orderByDesc('completed_bookings_count')
            ->orderBy('name')
            ->limit(5)
            ->get()
            ->map(function (User $customer): array {
                $completedBookingsCount = (int) $customer->completed_bookings_count;
                $periodBookingsCount = (int) $customer->period_bookings_count;
                $segment = $this->segmentation->segment($completedBookingsCount);

                return [
                    'name' => $customer->name,
                    'completedBookingsCount' => $completedBookingsCount,
                    'periodBookingsCount' => $periodBookingsCount,
                    'status' => $segment,
                    'description' => match ($segment) {
                        'Loyal' => 'Pelanggan loyal',
                        'Repeat' => 'Datang kembali',
                        default => 'Pelanggan baru',
                    },
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array{name: string, transactionCount: int}>
     */
    private function services(CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $servicesTable = (new Service)->getTable();
        $bookingItemsTable = (new BookingItem)->getTable();
        $bookingsTable = (new Booking)->getTable();

        return Service::query()
            ->select([$servicesTable.'.id', $servicesTable.'.name'])
            ->join($bookingItemsTable, $bookingItemsTable.'.service_id', '=', $servicesTable.'.id')
            ->join($bookingsTable, $bookingsTable.'.id', '=', $bookingItemsTable.'.booking_id')
            ->whereIn($bookingsTable.'.status', Booking::FINISHED_STATUSES)
            ->whereBetween($bookingsTable.'.booking_start', [$periodStart, $periodEnd])
            ->selectRaw("COUNT({$bookingItemsTable}.id) as transaction_count")
            ->groupBy($servicesTable.'.id', $servicesTable.'.name')
            ->orderByDesc('transaction_count')
            ->orderBy($servicesTable.'.name')
            ->limit(5)
            ->get()
            ->map(fn (Service $service): array => [
                'name' => $service->name,
                'transactionCount' => (int) $service->transaction_count,
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, bookingCount: int, averageRating: ?float}>
     */
    private function capsters(CarbonImmutable $periodStart, CarbonImmutable $periodEnd): array
    {
        $capstersTable = (new Capster)->getTable();
        $bookingsTable = (new Booking)->getTable();
        $reviewsTable = (new Review)->getTable();

        $averageRating = Review::query()
            ->selectRaw("AVG({$reviewsTable}.rating)")
            ->whereColumn($reviewsTable.'.capster_id', $capstersTable.'.id')
            ->whereBetween($reviewsTable.'.created_at', [$periodStart, $periodEnd]);

        return Capster::query()
            ->select([$capstersTable.'.id', $capstersTable.'.name'])
            ->join($bookingsTable, $bookingsTable.'.capster_id', '=', $capstersTable.'.id')
            ->whereIn($bookingsTable.'.status', Booking::FINISHED_STATUSES)
            ->whereBetween($bookingsTable.'.booking_start', [$periodStart, $periodEnd])
            ->selectRaw("COUNT({$bookingsTable}.id) as booking_count")
            ->selectSub($averageRating, 'average_rating')
            ->groupBy($capstersTable.'.id', $capstersTable.'.name')
            ->orderByDesc('booking_count')
            ->orderByDesc('average_rating')
            ->orderBy($capstersTable.'.name')
            ->limit(5)
            ->get()
            ->map(fn (Capster $capster): array => [
                'name' => $capster->name,
                'bookingCount' => (int) $capster->booking_count,
                'averageRating' => $capster->average_rating !== null
                    ? round((float) $capster->average_rating, 1)
                    : null,
            ])
            ->all();
    }

    /**
     * @param  ?array{name: string, transactionCount: int}  $favoriteService
     * @param  ?array{name: string, bookingCount: int, averageRating: ?float}  $favoriteCapster
     * @return array<int, string>
     */
    private function notes(
        string $periodLabel,
        int $activeCustomersCount,
        int $repeatCustomersCount,
        int $loyalCustomersCount,
        ?array $favoriteService,
        ?array $favoriteCapster,
    ): array {
        $serviceNote = $favoriteService
            ? "Layanan {$favoriteService['name']} menjadi layanan terlaris dengan {$favoriteService['transactionCount']} transaksi."
            : 'Belum ada layanan selesai pada periode ini.';

        if ($favoriteCapster) {
            $rating = $favoriteCapster['averageRating'] !== null
                ? "rating {$favoriteCapster['averageRating']}/5"
                : 'belum memiliki ulasan pada periode ini';
            $capsterNote = "Capster {$favoriteCapster['name']} menjadi favorit dengan {$favoriteCapster['bookingCount']} booking selesai dan {$rating}.";
        } else {
            $capsterNote = 'Belum ada capster favorit pada periode ini.';
        }

        return [
            "{$activeCustomersCount} pelanggan aktif melakukan layanan pada {$periodLabel}.",
            "{$repeatCustomersCount} pelanggan repeat order dengan 2 booking selesai aktif kembali.",
            "{$loyalCustomersCount} pelanggan loyal dengan minimal 3 booking selesai aktif kembali.",
            $serviceNote,
            $capsterNote,
        ];
    }
}
