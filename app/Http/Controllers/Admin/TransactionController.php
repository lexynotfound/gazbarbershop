<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\TransactionReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request, TransactionReport $report): View
    {
        $filters = $report->filters($request->query());

        return view('admin.transactions.index', [
            'filters' => $filters,
            'payments' => $report->payments($filters),
            'summary' => $report->summary($filters),
            'segments' => $report->chartSegments($filters),
            'methods' => $report->methods(),
            'report' => $report,
        ]);
    }

    public function exportCsv(Request $request, TransactionReport $report): StreamedResponse
    {
        $filters = $report->filters($request->query());
        $payments = $report->payments($filters);

        return response()->streamDownload(function () use ($payments, $report): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Tanggal Bayar',
                'Kode Booking',
                'Customer',
                'Layanan',
                'Capster',
                'Metode',
                'Status',
                'Nominal',
            ]);

            $payments->each(function (Payment $payment) use ($handle, $report): void {
                fputcsv($handle, [
                    $payment->paid_at?->format('Y-m-d H:i') ?? '-',
                    $payment->booking?->booking_code ?? '-',
                    $payment->booking?->user?->name ?? '-',
                    $report->servicesLabel($payment),
                    $payment->booking?->capster?->name ?? '-',
                    $report->methodLabel($payment->method),
                    $report->statusLabel($payment->status),
                    $payment->amount,
                ]);
            });

            fclose($handle);
        }, $this->filename('laporan-transaksi', $filters, 'csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request, TransactionReport $report): Response
    {
        $filters = $report->filters($request->query());
        $payments = $report->payments($filters);

        return Pdf::loadView('admin.transactions.pdf', [
            'filters' => $filters,
            'payments' => $payments,
            'summary' => $report->summary($filters),
            'segments' => $report->chartSegments($filters),
            'report' => $report,
        ])->download($this->filename('laporan-transaksi', $filters, 'pdf'));
    }

    /**
     * @param  array{start_date: string, end_date: string, status: string, method: string}  $filters
     */
    private function filename(string $prefix, array $filters, string $extension): string
    {
        return "{$prefix}-{$filters['start_date']}-{$filters['end_date']}.{$extension}";
    }
}
