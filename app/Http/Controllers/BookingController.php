<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Capster;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $bookingData = $request->validated();

        if (! Auth::check()) {
            $request->session()->put('pending_booking', $bookingData);

            return redirect()
                ->route('register')
                ->with('status', 'Daftar akun dulu, booking kamu akan otomatis dibuat setelah selesai daftar.');
        }

        $this->createBooking($bookingData, Auth::id());

        return redirect()
            ->route('bookings.index')
            ->with('status', 'Booking berhasil dibuat.');
    }

    public function completePendingBooking(Request $request): ?Booking
    {
        $bookingData = $request->session()->pull('pending_booking');

        if (! $bookingData || ! Auth::check()) {
            return null;
        }

        return $this->createBooking($bookingData, Auth::id());
    }

    /**
     * @param  array{service_ids: array<int, int>, capster_id: int, booking_date: string, booking_time: string}  $bookingData
     */
    private function createBooking(array $bookingData, int $userId): Booking
    {
        return DB::transaction(function () use ($bookingData, $userId): Booking {
            $services = Service::query()
                ->whereIn('id', $bookingData['service_ids'])
                ->where('is_active', true)
                ->get();

            $capster = Capster::query()
                ->where('is_active', true)
                ->findOrFail($bookingData['capster_id']);

            $bookingStart = CarbonImmutable::createFromFormat(
                'Y-m-d H:i',
                "{$bookingData['booking_date']} {$bookingData['booking_time']}",
            );
            $durationMinutes = (int) $services->sum('duration_minutes');
            $bookingEnd = $bookingStart->addMinutes($durationMinutes);
            $serviceTotal = (int) $services->sum('price');
            $capsterFee = (int) $capster->service_fee;

            $booking = Booking::query()->create([
                'booking_code' => $this->nextBookingCode(),
                'user_id' => $userId,
                'capster_id' => $capster->id,
                'booking_start' => $bookingStart,
                'booking_end' => $bookingEnd,
                'service_total' => $serviceTotal,
                'capster_fee' => $capsterFee,
                'grand_total' => $serviceTotal + $capsterFee,
                'status' => 'PENDING',
            ]);

            $services->each(function (Service $service) use ($booking): void {
                $booking->items()->create([
                    'service_id' => $service->id,
                    'price' => $service->price,
                    'duration_minutes' => $service->duration_minutes,
                ]);
            });

            return $booking;
        });
    }

    private function nextBookingCode(): string
    {
        return 'GAZ-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
    }
}
