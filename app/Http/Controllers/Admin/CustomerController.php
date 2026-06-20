<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    private const RepeatOrderThreshold = 3;

    public function index(): View
    {
        $customers = User::query()
            ->where('role', 'user')
            ->withCount([
                'bookings as completed_bookings_count' => fn ($query) => $query->where('status', 'COMPLETED'),
            ])
            ->orderByDesc('completed_bookings_count')
            ->orderBy('name')
            ->get();

        return view('admin.customers.index', [
            'customers' => $customers,
            'repeatOrderThreshold' => self::RepeatOrderThreshold,
        ]);
    }

    public function promoWhatsapp(User $user): RedirectResponse
    {
        $completedBookingsCount = $user->bookings()
            ->where('status', 'COMPLETED')
            ->count();

        if ($user->role !== 'user' || $completedBookingsCount < self::RepeatOrderThreshold) {
            return redirect()
                ->route('admin.customers.index')
                ->with('status', 'Promo hanya tersedia untuk pelanggan repeat order.');
        }

        if (! $user->phone) {
            return redirect()
                ->route('admin.customers.index')
                ->with('status', "Nomor WhatsApp {$user->name} belum tersedia.");
        }

        $phone = preg_replace('/\D+/', '', $user->phone);
        $message = implode("\n", [
            "Halo {$user->name}, terima kasih sudah sering booking di GAZ Barbershop.",
            '',
            'Sebagai pelanggan loyal, kami punya promo spesial untuk kunjungan berikutnya.',
            'Silakan booking kembali dan tunjukkan pesan ini saat datang.',
            '',
            'Terima kasih.',
            'GAZ Barbershop',
        ]);

        return redirect()->away('https://wa.me/'.$phone.'?text='.urlencode($message));
    }
}
