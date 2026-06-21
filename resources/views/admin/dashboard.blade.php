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
            <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_auto] md:items-center">
                <h2 class="text-xl font-black leading-tight">Booking Terbaru</h2>
                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-2 sm:flex sm:justify-end">
                    <x-select-input class="min-w-0 sm:w-44"><option>30 Hari Terakhir</option></x-select-input>
                    <x-secondary-button href="{{ route('admin.booking-chart') }}" class="shrink-0 whitespace-nowrap px-4">Lihat Detail</x-secondary-button>
                </div>
            </div>
            <div class="mt-8 h-72 rounded-2xl border border-gaz-border bg-black/25 p-5">
                <div class="flex h-full items-end gap-1.5 sm:gap-2">
                    @foreach ($recentBookingChart as $point)
                        <div class="group relative flex h-full flex-1 flex-col justify-end gap-2">
                            <div class="sr-only">{{ $point['label'] }}: {{ $point['total'] }} booking</div>
                            <div class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 min-w-28 -translate-x-1/2 rounded-xl border border-gaz-border bg-gaz-card px-3 py-2 text-center text-xs font-bold text-white opacity-0 shadow-xl transition group-hover:opacity-100 group-focus-within:opacity-100">
                                <span class="block text-gaz-gold">{{ $point['label'] }}</span>
                                <span>{{ $point['total'] }} booking</span>
                            </div>
                            <div
                                class="min-h-1 rounded-t-lg bg-gradient-to-t from-gaz-gold/30 to-gaz-gold transition group-hover:from-gaz-gold/60 group-hover:to-white"
                                style="height: {{ $point['height'] }}%"
                                title="{{ $point['label'] }}: {{ $point['total'] }} booking"
                                aria-label="{{ $point['label'] }}: {{ $point['total'] }} booking"
                            ></div>
                        </div>
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
                                <x-secondary-button type="submit">Tandai WA Terkirim</x-secondary-button>
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
