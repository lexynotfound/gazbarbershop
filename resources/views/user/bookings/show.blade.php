@extends('layouts.user')

@section('user-content')
<div class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gaz-gold">{{ $booking->booking_code }}</p>
            <h1 class="text-3xl font-black">Detail Booking</h1>
        </div>
        <x-booking-status-badge :status="$booking->status" />
    </div>
    @php
        $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
    @endphp
    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ([
            ['Layanan', $services],
            ['Capster', $booking->capster->name],
            ['Jadwal', $booking->booking_start->translatedFormat('d F Y, H:i')],
            ['Total', 'Rp' . number_format($booking->grand_total, 0, ',', '.')],
        ] as [$label, $value])
            <div class="rounded-xl bg-black/25 p-4">
                <dt class="text-sm text-gaz-muted">{{ $label }}</dt>
                <dd class="mt-1 font-black">{{ $value }}</dd>
            </div>
        @endforeach
    </dl>
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('bookings.index') }}" class="text-sm text-gaz-muted hover:text-white">← Kembali ke Booking Saya</a>
        @if (in_array($booking->status, \App\Models\Booking::RESCHEDULABLE_STATUSES, true))
            <x-secondary-button href="{{ route('booking.reschedule.form', $booking) }}">Reschedule</x-secondary-button>
        @endif
    </div>
</div>
@endsection
