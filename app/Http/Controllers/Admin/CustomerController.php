<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\CustomerSegmentation;
use App\Services\PhoneNumberFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(CustomerSegmentation $segmentation): View
    {
        $customers = User::query()
            ->where('role', 'user')
            ->withCount([
                'bookings as completed_bookings_count' => fn ($query) => $query->whereIn('status', Booking::FINISHED_STATUSES),
            ])
            ->orderByDesc('completed_bookings_count')
            ->orderBy('name')
            ->get();

        $customers->each(fn (User $customer) => $customer->setAttribute(
            'crm_segment',
            $segmentation->segment((int) $customer->completed_bookings_count),
        ));

        return view('admin.customers.index', [
            'customers' => $customers,
            'loyalCustomerThreshold' => CustomerSegmentation::LOYAL_CUSTOMER_THRESHOLD,
        ]);
    }

    public function promoWhatsapp(User $user): RedirectResponse
    {
        $completedBookingsCount = $user->bookings()
            ->whereIn('status', Booking::FINISHED_STATUSES)
            ->count();

        if ($user->role !== 'user' || $completedBookingsCount < CustomerSegmentation::LOYAL_CUSTOMER_THRESHOLD) {
            return redirect()
                ->route('admin.customers.index')
                ->with('status', 'Promo personal hanya tersedia untuk pelanggan loyal.');
        }

        if (! $user->phone) {
            return redirect()
                ->route('admin.customers.index')
                ->with('status', "Nomor WhatsApp {$user->name} belum tersedia.");
        }

        $phone = PhoneNumberFormatter::toIndonesianMobile($user->phone);

        if (! PhoneNumberFormatter::isIndonesianMobile($phone)) {
            return redirect()
                ->route('admin.customers.index')
                ->with('status', "Nomor WhatsApp {$user->name} belum valid.");
        }

        $completedBookingIds = Booking::query()
            ->select('id')
            ->whereBelongsTo($user)
            ->whereIn('status', Booking::FINISHED_STATUSES);
        $favoriteService = BookingItem::query()
            ->select('service_id')
            ->selectRaw('COUNT(*) as usage_count')
            ->whereIn('booking_id', clone $completedBookingIds)
            ->with('service:id,name')
            ->groupBy('service_id')
            ->orderByDesc('usage_count')
            ->first();
        $favoriteCapster = Booking::query()
            ->select('capster_id')
            ->selectRaw('COUNT(*) as booking_count')
            ->whereBelongsTo($user)
            ->whereIn('status', Booking::FINISHED_STATUSES)
            ->with('capster:id,name')
            ->groupBy('capster_id')
            ->orderByDesc('booking_count')
            ->first();
        $totalPaid = Payment::query()
            ->where('status', 'paid')
            ->whereIn('booking_id', clone $completedBookingIds)
            ->sum('amount');

        $message = collect([
            "Halo {$user->name}, terima kasih sudah sering booking di GAZ Barbershop.",
            '',
            "Kamu sudah menyelesaikan {$completedBookingsCount} kunjungan dan masuk segmen pelanggan loyal.",
            $favoriteService?->service ? "Layanan favoritmu: {$favoriteService->service->name}." : null,
            $favoriteCapster?->capster ? "Capster favoritmu: {$favoriteCapster->capster->name}." : null,
            $totalPaid > 0 ? 'Total transaksi lunas: Rp'.number_format((int) $totalPaid, 0, ',', '.').'.' : null,
            '',
            'Kami punya promo personal untuk kunjungan berikutnya.',
            'Silakan booking kembali dan tunjukkan pesan ini saat datang.',
            '',
            'Terima kasih.',
            'GAZ Barbershop',
        ])->filter(fn (?string $line): bool => $line !== null)->implode("\n");

        return redirect()->away('https://wa.me/'.$phone.'?text='.urlencode($message));
    }
}
