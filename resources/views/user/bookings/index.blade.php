@extends('layouts.user')

@section('user-content')
<div x-data="{ tab: 'Akan Datang' }" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">Booking Saya</h1>
    <div class="mt-6 flex flex-wrap gap-2">
        @foreach (['Akan Datang', 'Selesai', 'Dibatalkan'] as $tab)
            <button @click="tab = '{{ $tab }}'" class="rounded-full border px-4 py-2 text-sm font-bold" :class="tab === '{{ $tab }}' ? 'border-gaz-gold bg-gaz-gold text-black' : 'border-gaz-border text-gaz-muted'">{{ $tab }}</button>
        @endforeach
    </div>

    @foreach ([
        'Akan Datang' => $upcomingBookings,
        'Selesai' => $finishedBookings,
        'Dibatalkan' => $cancelledBookings,
    ] as $group => $bookings)
        <div x-show="tab === '{{ $group }}'" class="mt-6 grid gap-4">
            @forelse ($bookings as $booking)
                @php
                    $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
                @endphp
                <article class="rounded-2xl border border-gaz-border bg-black/25 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gaz-gold">{{ $booking->booking_code }}</p>
                            <p class="font-black">{{ $services }}</p>
                            <p class="mt-1 text-sm text-gaz-muted">{{ $booking->booking_start->translatedFormat('d F Y, H:i') }} - {{ $booking->capster->name }} - Rp{{ number_format($booking->grand_total, 0, ',', '.') }}</p>
                        </div>
                        <x-booking-status-badge :status="$booking->status" />
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <x-secondary-button href="{{ route('booking.show', $booking) }}">Detail</x-secondary-button>
                        @if ($booking->status === 'COMPLETED' && ! $booking->review)
                            <x-primary-button href="{{ route('booking.review', ['booking' => $booking->id]) }}">Review</x-primary-button>
                        @elseif ($booking->status === 'REVIEWED' || $booking->review)
                            <span class="inline-flex min-h-11 items-center justify-center rounded-xl border border-gaz-border px-5 py-3 text-sm font-bold text-gaz-muted">Sudah direview</span>
                        @endif
                        @if ($booking->status === 'PAID')
                            <x-primary-button>Selesai</x-primary-button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gaz-border p-8 text-center text-gaz-muted">Belum ada booking {{ str($group)->lower() }}.</div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
