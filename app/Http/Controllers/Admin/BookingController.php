<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PhoneNumberFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::query()
            ->with(['user', 'capster', 'items.service', 'payment'])
            ->latest('booking_start')
            ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['user', 'capster', 'items.service', 'payment']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function whatsappConfirmation(Booking $booking): View
    {
        $booking->load(['user', 'capster', 'items.service']);

        $services = $booking->items->map(fn ($item) => $item->service->name)->join(', ');

        $message = implode("\n", [
            "Halo {$booking->user->name}, booking Anda di GAZ Barbershop sudah kami terima.",
            '',
            'Detail Booking:',
            "- Kode Booking: {$booking->booking_code}",
            "- Layanan: {$services}",
            "- Capster: {$booking->capster->name}",
            '- Jadwal: '.$booking->booking_start->translatedFormat('d F Y, H:i'),
            '- Total Harga: Rp'.number_format($booking->grand_total, 0, ',', '.'),
            '',
            'Mohon konfirmasi apakah Anda jadi datang.',
            'Balas: Jadi / Tidak Jadi',
            '',
            'Terima kasih.',
        ]);

        $whatsappPhone = PhoneNumberFormatter::toIndonesianMobile($booking->user->phone);
        $whatsappUrl = PhoneNumberFormatter::isIndonesianMobile($whatsappPhone)
            ? 'https://wa.me/'.$whatsappPhone.'?text='.urlencode($message)
            : null;

        return view('admin.bookings.whatsapp-confirmation', compact('booking', 'message', 'whatsappPhone', 'whatsappUrl'));
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        $booking->update([
            'status' => 'WAITING_CUSTOMER_CONFIRMATION',
            'admin_confirmed_at' => now(),
            'customer_response_deadline' => now()->addMinutes(15),
        ]);

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', "Booking {$booking->booking_code} ditandai WA terkirim.");
    }

    public function accept(Booking $booking): RedirectResponse
    {
        if (! in_array($booking->status, Booking::ACCEPT_STATUSES, true)) {
            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('status', "Booking {$booking->booking_code} tidak bisa ditandai jadi datang.");
        }

        $booking->update([
            'status' => 'ACCEPTED',
            'customer_response_deadline' => null,
        ]);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', "Booking {$booking->booking_code} ditandai user jadi datang.");
    }

    public function checkIn(Booking $booking): RedirectResponse
    {
        if (! in_array($booking->status, Booking::CHECK_IN_STATUSES, true)) {
            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('status', "Booking {$booking->booking_code} tidak bisa di-check-in.");
        }

        $booking->update([
            'status' => 'CHECKED_IN',
            'checked_in_at' => now(),
        ]);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', "Booking {$booking->booking_code} berhasil di-check-in.");
    }

    public function complete(Booking $booking): RedirectResponse
    {
        if (! in_array($booking->status, Booking::COMPLETE_STATUSES, true)) {
            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('status', "Booking {$booking->booking_code} tidak bisa diselesaikan.");
        }

        $booking->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', "Booking {$booking->booking_code} selesai dan sudah bisa direview user.");
    }

    public function markPaid(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'method' => ['required', Rule::in(['cash', 'qris', 'transfer'])],
        ]);

        $payment = $booking->payment()->firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'amount' => $booking->grand_total,
                'method' => $validated['method'],
                'status' => 'unpaid',
            ],
        );

        if ($payment->status === 'paid') {
            return redirect()
                ->route('admin.bookings.show', $booking)
                ->with('status', "Transaksi {$booking->booking_code} sudah lunas.");
        }

        $payment->update([
            'amount' => $booking->grand_total,
            'method' => $validated['method'],
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', "Transaksi {$booking->booking_code} ditandai lunas.");
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'REJECTED']);

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', "Booking {$booking->booking_code} ditolak.");
    }
}
