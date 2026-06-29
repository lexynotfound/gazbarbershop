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

    <section class="grid gap-6 rounded-2xl border border-gaz-border bg-gaz-card p-4 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-gaz-gold">Laporan Pelanggan</p>
                <h2 class="mt-2 text-2xl font-black">Ringkasan Laporan CRM</h2>
                <p class="mt-1 text-sm text-gaz-muted">Analisis pelanggan, layanan, capster, dan kualitas layanan pada {{ $crmReport['periodLabel'] }}.</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                <div>
                    <label for="month" class="mb-2 block text-xs font-bold uppercase text-gaz-muted">Periode laporan</label>
                    <input id="month" name="month" type="month" value="{{ $crmReport['month'] }}" class="min-h-11 rounded-xl border border-gaz-border bg-black/40 px-4 text-sm font-bold text-white outline-none focus:border-gaz-gold">
                </div>
                <x-primary-button type="submit" class="justify-center">Tampilkan</x-primary-button>
            </form>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat-card
                label="Pelanggan Aktif"
                :value="$crmReport['activeCustomersCount']"
                icon="PA"
                :description="'Booking selesai pada '.$crmReport['periodLabel']"
            />
            <x-stat-card
                label="Repeat Order"
                :value="$crmReport['repeatCustomersCount']"
                icon="RO"
                description="Minimal 3 booking selesai"
            />
            <x-stat-card
                label="Layanan Terlaris"
                :value="$crmReport['favoriteService']['name'] ?? '-'"
                icon="LT"
                :description="$crmReport['favoriteService'] ? $crmReport['favoriteService']['transactionCount'].' transaksi' : 'Belum ada transaksi selesai'"
            />
            <x-stat-card
                label="Capster Favorit"
                :value="$crmReport['favoriteCapster']['name'] ?? '-'"
                icon="CF"
                :description="$crmReport['favoriteCapster']
                    ? $crmReport['favoriteCapster']['bookingCount'].' booking · '.($crmReport['favoriteCapster']['averageRating'] !== null ? $crmReport['favoriteCapster']['averageRating'].'/5' : 'Belum ada rating')
                    : 'Belum ada booking selesai'"
            />
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.45fr_1fr_1fr]">
            <section class="min-w-0 rounded-2xl border border-gaz-border bg-black/25 p-4 sm:p-5">
                <h3 class="text-lg font-black">Pelanggan Aktif & Repeat Order</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-160 text-left text-sm">
                        <thead class="text-xs uppercase text-gaz-muted">
                            <tr class="border-b border-gaz-border">
                                <th class="px-3 py-3">Nama Pelanggan</th>
                                <th class="px-3 py-3 text-center">Total Booking</th>
                                <th class="px-3 py-3">Status</th>
                                <th class="px-3 py-3">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($crmReport['customers'] as $customer)
                                <tr class="border-b border-gaz-border/70 last:border-0">
                                    <td class="px-3 py-3 font-bold">{{ $customer['name'] }}</td>
                                    <td class="px-3 py-3 text-center font-black text-gaz-gold">{{ $customer['completedBookingsCount'] }}</td>
                                    <td class="px-3 py-3">
                                        <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $customer['status'] === 'Repeat' ? 'border-gaz-gold/30 bg-gaz-gold/10 text-gaz-gold' : 'border-white/15 bg-white/5 text-white' }}">
                                            {{ $customer['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 text-gaz-muted">{{ $customer['description'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-3 py-8 text-center text-gaz-muted">Belum ada pelanggan aktif pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-2xl border border-gaz-border bg-black/25 p-4 sm:p-5">
                <h3 class="text-lg font-black">Layanan Terlaris</h3>
                <div class="mt-5 grid gap-4">
                    @forelse ($crmReport['services'] as $service)
                        <div>
                            <div class="flex items-center justify-between gap-3 text-sm">
                                <span class="min-w-0 truncate font-bold">{{ $service['name'] }}</span>
                                <span class="shrink-0 text-gaz-gold">{{ $service['transactionCount'] }}</span>
                            </div>
                            <progress class="crm-progress mt-2 h-2 w-full overflow-hidden rounded-full" value="{{ $service['transactionCount'] }}" max="{{ max($crmReport['serviceMaxTransactions'], 1) }}">
                                {{ $service['transactionCount'] }}
                            </progress>
                        </div>
                    @empty
                        <p class="text-sm text-gaz-muted">Belum ada layanan selesai pada periode ini.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-gaz-border bg-black/25 p-4 sm:p-5">
                <h3 class="text-lg font-black">Capster Favorit & Rating</h3>
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-72 text-left text-sm">
                        <thead class="text-xs uppercase text-gaz-muted">
                            <tr class="border-b border-gaz-border">
                                <th class="py-3 pr-3">Nama</th>
                                <th class="px-3 py-3 text-center">Booking</th>
                                <th class="py-3 pl-3 text-right">Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($crmReport['capsters'] as $capster)
                                <tr class="border-b border-gaz-border/70 last:border-0">
                                    <td class="py-3 pr-3 font-bold">{{ $capster['name'] }}</td>
                                    <td class="px-3 py-3 text-center text-gaz-gold">{{ $capster['bookingCount'] }}</td>
                                    <td class="py-3 pl-3 text-right">{{ $capster['averageRating'] !== null ? $capster['averageRating'].' / 5' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gaz-muted">Belum ada data capster.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-gaz-gold/25 bg-gaz-gold/5 p-4 sm:p-5">
            <h3 class="text-lg font-black">Catatan Informasi Manajerial</h3>
            <ol class="mt-4 grid gap-3 text-sm text-gaz-muted md:grid-cols-2">
                @foreach ($crmReport['notes'] as $note)
                    <li class="flex gap-3">
                        <span class="grid size-6 shrink-0 place-items-center rounded-full bg-gaz-gold font-black text-black">{{ $loop->iteration }}</span>
                        <span>{{ $note }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    </section>
</div>
@endsection
