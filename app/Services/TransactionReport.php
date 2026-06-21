<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TransactionReport
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{start_date: string, end_date: string, status: string, method: string}
     */
    public function filters(array $input): array
    {
        $startDate = filled($input['start_date'] ?? null)
            ? Carbon::parse($input['start_date'])->startOfDay()
            : now()->startOfMonth();

        $endDate = filled($input['end_date'] ?? null)
            ? Carbon::parse($input['end_date'])->endOfDay()
            : now()->endOfMonth();

        if ($endDate->lt($startDate)) {
            [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
        }

        return [
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => in_array($input['status'] ?? 'all', ['all', 'paid', 'unpaid'], true) ? (string) ($input['status'] ?? 'all') : 'all',
            'method' => filled($input['method'] ?? null) ? (string) $input['method'] : 'all',
        ];
    }

    /**
     * @param  array{start_date: string, end_date: string, status: string, method: string}  $filters
     * @return Collection<int, Payment>
     */
    public function payments(array $filters): Collection
    {
        return $this->query($filters)
            ->with(['booking.user', 'booking.capster', 'booking.items.service'])
            ->latest('paid_at')
            ->latest('created_at')
            ->get();
    }

    /**
     * @param  array{start_date: string, end_date: string, status: string, method: string}  $filters
     * @return array{total_transactions: int, paid_revenue: int, unpaid_amount: int, average_paid_transaction: float}
     */
    public function summary(array $filters): array
    {
        $payments = $this->query([...$filters, 'status' => 'all'])->get();
        $paidPayments = $payments->where('status', 'paid');

        return [
            'total_transactions' => $payments->count(),
            'paid_revenue' => (int) $paidPayments->sum('amount'),
            'unpaid_amount' => (int) $payments->where('status', 'unpaid')->sum('amount'),
            'average_paid_transaction' => (float) $paidPayments->avg('amount'),
        ];
    }

    /**
     * @param  array{start_date: string, end_date: string, status: string, method: string}  $filters
     * @return Collection<int, array{label: string, total: int, percentage: float, color: string, start: float, end: float}>
     */
    public function chartSegments(array $filters): Collection
    {
        $colors = ['#d4af37', '#22c55e', '#38bdf8', '#f97316', '#a855f7', '#ef4444'];

        $rows = $this->query([...$filters, 'status' => 'paid'])
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get();

        $grandTotal = (int) $rows->sum('total');
        $cursor = 0.0;

        return $rows->values()->map(function (Payment $payment, int $index) use ($colors, $grandTotal, &$cursor): array {
            $total = (int) $payment->total;
            $percentage = $grandTotal > 0 ? ($total / $grandTotal) * 100 : 0.0;
            $start = $cursor;
            $cursor += $percentage;

            return [
                'label' => $this->methodLabel($payment->method),
                'total' => $total,
                'percentage' => $percentage,
                'color' => $colors[$index % count($colors)],
                'start' => $start,
                'end' => $cursor,
            ];
        });
    }

    /**
     * @return Collection<int, string>
     */
    public function methods(): Collection
    {
        return Payment::query()
            ->whereNotNull('method')
            ->distinct()
            ->orderBy('method')
            ->pluck('method')
            ->values();
    }

    public function methodLabel(?string $method): string
    {
        return match ($method) {
            'cash' => 'Cash',
            'qris' => 'QRIS',
            'transfer' => 'Transfer',
            default => str($method ?: '-')->replace('_', ' ')->title()->toString(),
        };
    }

    public function statusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => 'Lunas',
            'unpaid' => 'Belum Dibayar',
            default => str($status ?: '-')->replace('_', ' ')->title()->toString(),
        };
    }

    public function servicesLabel(Payment $payment): string
    {
        $services = ($payment->booking?->items ?? collect())
            ->map(fn ($item): ?string => $item->service?->name)
            ->filter()
            ->values();

        return $services->isNotEmpty() ? $services->join(' + ') : '-';
    }

    /**
     * @param  array{start_date: string, end_date: string, status: string, method: string}  $filters
     * @return Builder<Payment>
     */
    private function query(array $filters): Builder
    {
        $startDate = Carbon::parse($filters['start_date'])->startOfDay();
        $endDate = Carbon::parse($filters['end_date'])->endOfDay();

        return Payment::query()
            ->where(function (Builder $query) use ($startDate, $endDate): void {
                $query->whereBetween('paid_at', [$startDate, $endDate])
                    ->orWhere(function (Builder $query) use ($startDate, $endDate): void {
                        $query->whereNull('paid_at')
                            ->whereBetween('created_at', [$startDate, $endDate]);
                    });
            })
            ->when($filters['status'] !== 'all', fn (Builder $query): Builder => $query->where('status', $filters['status']))
            ->when($filters['method'] !== 'all', fn (Builder $query): Builder => $query->where('method', $filters['method']));
    }
}
