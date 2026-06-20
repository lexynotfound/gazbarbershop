@extends('layouts.admin', ['heading' => 'Detail Review'])

@section('content')
@php
    $booking = $review->booking;
    $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
@endphp

<div class="grid gap-6">
    <x-breadcrumbs :items="[
        ['label' => 'Review', 'url' => route('admin.reviews.index')],
        ['label' => $booking->booking_code],
    ]" />

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <span class="inline-flex rounded-full border border-gaz-gold/30 bg-gaz-gold/10 px-3 py-1 text-sm font-bold text-gaz-gold">{{ $review->rating }}/5</span>
                <h1 class="mt-4 text-3xl font-black">Detail Review</h1>
                <p class="mt-2 text-sm text-gaz-muted">{{ $review->created_at?->translatedFormat('d F Y H:i') ?: '-' }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button href="{{ route('admin.reviews.index') }}">Kembali</x-secondary-button>
                <x-primary-button href="{{ route('admin.bookings.show', $booking) }}">Detail Booking</x-primary-button>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl bg-black/25 p-4">
                <p class="text-sm text-gaz-muted">Pelanggan</p>
                <p class="mt-1 font-black">{{ $review->user->name }}</p>
                <p class="mt-1 text-sm text-gaz-muted">{{ $review->user->email }}</p>
                <p class="mt-1 text-sm text-gaz-muted">{{ $review->user->phone ?: '-' }}</p>
            </div>
            <div class="rounded-xl bg-black/25 p-4">
                <p class="text-sm text-gaz-muted">Booking</p>
                <p class="mt-1 font-black">{{ $booking->booking_code }}</p>
                <p class="mt-1 text-sm text-gaz-muted">{{ $booking->booking_start->translatedFormat('d F Y H:i') }}</p>
                <p class="mt-1 text-sm text-gaz-muted">Rp{{ number_format($booking->grand_total, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl bg-black/25 p-4">
                <p class="text-sm text-gaz-muted">Capster</p>
                <p class="mt-1 font-black">{{ $review->capster->name }}</p>
                <p class="mt-1 text-sm text-gaz-muted">{{ $services ?: '-' }}</p>
                <div class="mt-2"><x-booking-status-badge :status="$booking->status" /></div>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <h2 class="text-xl font-black">Komentar</h2>
        <p class="mt-4 rounded-2xl border border-gaz-border bg-black/25 p-5 leading-7 text-gaz-muted">{{ $review->comment ?: '-' }}</p>
    </section>
</div>
@endsection
