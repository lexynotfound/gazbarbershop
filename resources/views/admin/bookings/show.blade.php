@extends('layouts.admin', ['heading' => 'Detail Booking'])

@section('content')
@php
    $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
@endphp

<div class="grid gap-6">
    <x-breadcrumbs :items="[
        ['label' => 'Booking', 'url' => route('admin.bookings.index')],
        ['label' => $booking->booking_code],
    ]" />

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <x-booking-status-badge :status="$booking->status" />
                <h1 class="mt-4 text-3xl font-black">{{ $booking->booking_code }}</h1>
                <p class="mt-2 text-sm text-gaz-muted">{{ $booking->booking_start->translatedFormat('d F Y H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button href="{{ route('admin.bookings.index') }}">Kembali</x-secondary-button>
                <x-primary-button href="{{ route('admin.bookings.whatsapp', $booking) }}">WhatsApp</x-primary-button>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Pelanggan', $booking->user->name], ['Layanan', $services], ['Capster', $booking->capster->name], ['Total', 'Rp'.number_format($booking->grand_total, 0, ',', '.')]] as [$label, $value])
                <div class="rounded-xl bg-black/25 p-4"><p class="text-sm text-gaz-muted">{{ $label }}</p><p class="mt-1 font-black">{{ $value }}</p></div>
            @endforeach
        </div>
    </section>
</div>
@endsection
