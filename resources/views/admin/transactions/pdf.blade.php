<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body { color: #111827; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 22px; margin: 0; }
        h2 { font-size: 15px; margin: 24px 0 10px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #d1d5db; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .muted { color: #6b7280; }
        .summary { margin-top: 18px; width: 100%; }
        .summary td { width: 25%; }
        .value { font-size: 16px; font-weight: bold; margin-top: 4px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Transaksi</h1>
    <p class="muted">Periode {{ \Illuminate\Support\Carbon::parse($filters['start_date'])->translatedFormat('d F Y') }} - {{ \Illuminate\Support\Carbon::parse($filters['end_date'])->translatedFormat('d F Y') }}</p>

    <table class="summary">
        <tr>
            <td><span class="muted">Total Transaksi</span><div class="value">{{ number_format($summary['total_transactions'], 0, ',', '.') }}</div></td>
            <td><span class="muted">Pendapatan Lunas</span><div class="value">Rp{{ number_format($summary['paid_revenue'], 0, ',', '.') }}</div></td>
            <td><span class="muted">Belum Dibayar</span><div class="value">Rp{{ number_format($summary['unpaid_amount'], 0, ',', '.') }}</div></td>
            <td><span class="muted">Rata-rata</span><div class="value">Rp{{ number_format($summary['average_paid_transaction'], 0, ',', '.') }}</div></td>
        </tr>
    </table>

    <h2>Ringkasan Metode Pembayaran</h2>
    <table>
        <thead>
            <tr>
                <th>Metode</th>
                <th class="right">Pendapatan</th>
                <th class="right">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($segments as $segment)
                <tr>
                    <td>{{ $segment['label'] }}</td>
                    <td class="right">Rp{{ number_format($segment['total'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($segment['percentage'], 1, ',', '.') }}%</td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada pendapatan lunas pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Daftar Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal Bayar</th>
                <th>Kode</th>
                <th>Customer</th>
                <th>Layanan</th>
                <th>Capster</th>
                <th>Metode</th>
                <th>Status</th>
                <th class="right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                <tr>
                    <td>{{ $payment->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $payment->booking?->booking_code ?? '-' }}</td>
                    <td>{{ $payment->booking?->user?->name ?? '-' }}</td>
                    <td>{{ $report->servicesLabel($payment) }}</td>
                    <td>{{ $payment->booking?->capster?->name ?? '-' }}</td>
                    <td>{{ $report->methodLabel($payment->method) }}</td>
                    <td>{{ $report->statusLabel($payment->status) }}</td>
                    <td class="right">Rp{{ number_format($payment->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Belum ada transaksi sesuai filter.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
