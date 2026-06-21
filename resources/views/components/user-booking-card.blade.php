@props(['booking'])

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
