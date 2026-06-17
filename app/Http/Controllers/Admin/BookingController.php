<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::query()
            ->with(['user', 'capster', 'items.service'])
            ->latest('booking_start')
            ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['user', 'capster', 'items.service']);

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

        $whatsappUrl = 'https://wa.me/'.$booking->user->phone.'?text='.urlencode($message);

        return view('admin.bookings.whatsapp-confirmation', compact('booking', 'message', 'whatsappUrl'));
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
            ->with('status', "Booking {$booking->booking_code} ditandai sudah dikonfirmasi.");
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $booking->update(['status' => 'REJECTED']);

        return redirect()
            ->route('admin.bookings.index')
            ->with('status', "Booking {$booking->booking_code} ditolak.");
    }
}
