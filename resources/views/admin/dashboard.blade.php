@extends('layouts.admin', ['heading' => 'Dashboard'])

@section('content')
<div class="grid gap-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Total Booking" :value="$totalBookings" icon="B" />
        <x-stat-card label="Booking Hari Ini" :value="$todayBookings" icon="H" />
        <x-stat-card label="Capster" :value="$totalCapsters" icon="C" />
        <x-stat-card label="Pelanggan" :value="$totalCustomers" icon="P" />
    </div>
    <div class="grid gap-6 xl:grid-cols-[1.3fr_1fr]">
        <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
            <div class="flex items-center justify-between gap-4"><h2 class="text-xl font-black">Booking Terbaru</h2><x-select-input class="max-w-40"><option>30 Hari Terakhir</option></x-select-input></div>
            <div class="mt-8 h-72 rounded-2xl border border-gaz-border bg-black/25 p-5">
                <div class="flex h-full items-end gap-3">
                    @foreach ([25, 65, 42, 88, 48, 38, 57, 73, 39, 30, 68, 76] as $height)
                        <div class="flex-1 rounded-t-lg bg-gradient-to-t from-gaz-gold/30 to-gaz-gold" style="height: {{ $height }}%"></div>
                    @endforeach
                </div>
            </div>
        </section>
        <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
            <h2 class="text-xl font-black">Booking Menunggu Konfirmasi</h2>
            <div class="mt-5 grid gap-4">
                @forelse ($waitingBookings as $booking)
                    @php
                        $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
                    @endphp
                    <article class="flex flex-col gap-4 rounded-2xl border border-gaz-border bg-black/25 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="grid size-12 shrink-0 place-items-center rounded-xl bg-gaz-gold font-black text-black">{{ str($booking->user->name)->substr(0, 1) }}</div>
                            <div class="min-w-0">
                                <p class="font-black">{{ $booking->user->name }}</p>
                                <p class="text-xs text-gaz-muted">{{ $services }} - {{ $booking->booking_start->translatedFormat('d F Y') }} - {{ $booking->booking_start->format('H:i') }} - {{ $booking->capster->name }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:justify-end">
                            <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                                @csrf
                                @method('PATCH')
                                <x-secondary-button type="submit">Konfirmasi</x-secondary-button>
                            </form>
                            <x-primary-button href="{{ route('admin.bookings.whatsapp', $booking) }}">WhatsApp</x-primary-button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gaz-border bg-black/20 p-5 text-sm text-gaz-muted">Belum ada booking menunggu konfirmasi.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
