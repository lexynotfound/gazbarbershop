@extends('layouts.user')

@section('user-content')
@php
    $bookings = [
        ['date' => '31 Mei 2026, 10:00', 'service' => 'Cukur Rambut + Cuci', 'capster' => 'Rudi', 'total' => 150000, 'status' => 'WAITING_CUSTOMER_CONFIRMATION'],
        ['date' => '28 Mei 2026, 14:00', 'service' => 'Perawatan Jenggot', 'capster' => 'Fahmi', 'total' => 90000, 'status' => 'COMPLETED'],
        ['date' => '20 Mei 2026, 13:00', 'service' => 'Warnai Rambut', 'capster' => 'Bayu', 'total' => 210000, 'status' => 'REVIEWED'],
    ];
@endphp
<div x-data="{ tab: 'Akan Datang' }" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">Booking Saya</h1>
    <div class="mt-6 flex flex-wrap gap-2">
        @foreach (['Akan Datang', 'Selesai', 'Dibatalkan'] as $tab)
            <button @click="tab = '{{ $tab }}'" class="rounded-full border px-4 py-2 text-sm font-bold" :class="tab === '{{ $tab }}' ? 'border-gaz-gold bg-gaz-gold text-black' : 'border-gaz-border text-gaz-muted'">{{ $tab }}</button>
        @endforeach
    </div>
    <div class="mt-6 grid gap-4">
        @forelse ($bookings as $booking)
            <article class="rounded-2xl border border-gaz-border bg-black/25 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="font-black">{{ $booking['service'] }}</p>
                        <p class="mt-1 text-sm text-gaz-muted">{{ $booking['date'] }} · {{ $booking['capster'] }} · Rp{{ number_format($booking['total'], 0, ',', '.') }}</p>
                    </div>
                    <x-booking-status-badge :status="$booking['status']" />
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <x-secondary-button href="{{ route('booking.show') }}">Detail</x-secondary-button>
                    @if ($booking['status'] === 'COMPLETED')
                        <x-primary-button href="{{ route('booking.review') }}">Review</x-primary-button>
                    @endif
                    @if ($booking['status'] === 'PAID')
                        <x-primary-button>Selesai</x-primary-button>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-gaz-border p-8 text-center text-gaz-muted">Belum ada booking.</div>
        @endforelse
    </div>
</div>
@endsection
