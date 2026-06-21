@extends('layouts.admin', ['heading' => 'Transaksi'])

@section('content')
@php
    $gradient = $segments->isEmpty()
        ? '#1f2937 0 100%'
        : $segments->map(fn ($segment) => "{$segment['color']} {$segment['start']}% {$segment['end']}%")->join(', ');
@endphp

<div class="grid gap-6">
    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-2xl font-black">Laporan Transaksi</h2>
                <p class="mt-1 text-sm text-gaz-muted">Pantau pendapatan booking berdasarkan pembayaran yang masuk.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button href="{{ route('admin.transactions.export.csv', $filters) }}">Export CSV</x-secondary-button>
                <x-primary-button href="{{ route('admin.transactions.export.pdf', $filters) }}">Export PDF</x-primary-button>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.transactions.index') }}" class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <label class="grid gap-2 text-sm font-bold text-gaz-muted">
                Dari Tanggal
                <x-text-input type="date" name="start_date" value="{{ $filters['start_date'] }}" />
            </label>
            <label class="grid gap-2 text-sm font-bold text-gaz-muted">
                Sampai Tanggal
                <x-text-input type="date" name="end_date" value="{{ $filters['end_date'] }}" />
            </label>
            <label class="grid gap-2 text-sm font-bold text-gaz-muted">
                Status
                <x-select-input name="status">
                    @foreach ([['all', 'Semua Status'], ['paid', 'Lunas'], ['unpaid', 'Belum Dibayar']] as [$value, $label])
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </x-select-input>
            </label>
            <label class="grid gap-2 text-sm font-bold text-gaz-muted">
                Metode
                <x-select-input name="method">
                    <option value="all" @selected($filters['method'] === 'all')>Semua Metode</option>
                    @foreach ($methods as $method)
                        <option value="{{ $method }}" @selected($filters['method'] === $method)>{{ $report->methodLabel($method) }}</option>
                    @endforeach
                </x-select-input>
            </label>
            <div class="flex items-end gap-2">
                <x-primary-button type="submit" class="w-full">Terapkan</x-primary-button>
                <x-secondary-button href="{{ route('admin.transactions.index') }}">Reset</x-secondary-button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Total Transaksi', number_format($summary['total_transactions'], 0, ',', '.')],
            ['Pendapatan Lunas', 'Rp'.number_format($summary['paid_revenue'], 0, ',', '.')],
            ['Belum Dibayar', 'Rp'.number_format($summary['unpaid_amount'], 0, ',', '.')],
            ['Rata-rata Transaksi', 'Rp'.number_format($summary['average_paid_transaction'], 0, ',', '.')],
        ] as [$label, $value])
            <div class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
                <p class="text-sm font-bold text-gaz-muted">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black text-white">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[420px_1fr]">
        <div class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
            <div>
                <h3 class="text-xl font-black">Pendapatan per Metode</h3>
                <p class="mt-1 text-sm text-gaz-muted">Dihitung dari transaksi berstatus lunas.</p>
            </div>

            <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row xl:flex-col">
                <div class="grid size-52 shrink-0 place-items-center rounded-full" style="background: conic-gradient({{ $gradient }});">
                    <div class="grid size-28 place-items-center rounded-full border border-gaz-border bg-gaz-card text-center">
                        <span class="text-xs font-bold text-gaz-muted">Total</span>
                        <span class="text-sm font-black text-gaz-gold">Rp{{ number_format($summary['paid_revenue'], 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="grid w-full gap-3">
                    @forelse ($segments as $segment)
                        <div class="flex items-center justify-between gap-4 rounded-xl border border-gaz-border bg-white/[0.03] px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="size-3 rounded-full" style="background-color: {{ $segment['color'] }}"></span>
                                <span class="font-bold">{{ $segment['label'] }}</span>
                            </div>
                            <div class="text-right text-sm">
                                <p class="font-black">Rp{{ number_format($segment['total'], 0, ',', '.') }}</p>
                                <p class="text-gaz-muted">{{ number_format($segment['percentage'], 1, ',', '.') }}%</p>
                            </div>
                        </div>
                    @empty
                        <p class="rounded-xl border border-gaz-border bg-white/[0.03] px-4 py-6 text-center text-sm text-gaz-muted">Belum ada pendapatan lunas pada periode ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gaz-border bg-gaz-card">
            <div class="border-b border-gaz-border p-5">
                <h3 class="text-xl font-black">Daftar Transaksi</h3>
                <p class="mt-1 text-sm text-gaz-muted">{{ $payments->count() }} transaksi sesuai filter.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1020px] text-left text-sm">
                    <thead class="bg-white/[0.04] text-gaz-muted">
                        <tr>
                            @foreach (['Tanggal Bayar', 'Kode', 'Customer', 'Layanan', 'Capster', 'Metode', 'Status', 'Nominal'] as $head)
                                <th class="px-4 py-4 font-bold">{{ $head }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gaz-border">
                        @forelse ($payments as $payment)
                            <tr class="hover:bg-white/[0.03]">
                                <td class="px-4 py-4">{{ $payment->paid_at?->translatedFormat('d F Y H:i') ?? '-' }}</td>
                                <td class="px-4 py-4 font-black">{{ $payment->booking?->booking_code ?? '-' }}</td>
                                <td class="px-4 py-4">{{ $payment->booking?->user?->name ?? '-' }}</td>
                                <td class="px-4 py-4">{{ $report->servicesLabel($payment) }}</td>
                                <td class="px-4 py-4">{{ $payment->booking?->capster?->name ?? '-' }}</td>
                                <td class="px-4 py-4">{{ $report->methodLabel($payment->method) }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $payment->status === 'paid' ? 'border-emerald-400/30 bg-emerald-400/10 text-emerald-200' : 'border-amber-400/30 bg-amber-400/10 text-amber-200' }}">
                                        {{ $report->statusLabel($payment->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 font-black">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-gaz-muted">Belum ada transaksi sesuai filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
