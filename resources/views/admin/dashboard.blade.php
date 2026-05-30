@extends('layouts.admin', ['heading' => 'Dashboard'])

@section('content')
@php
    $waiting = [
        ['name' => 'Rizky Pratama', 'service' => 'Cukur favorit + Cuci', 'time' => '31 Mei 2026 · 10:00', 'capster' => 'Rudi'],
        ['name' => 'Ariel Saputra', 'service' => 'Cukur trendcut', 'time' => '31 Mei 2026 · 11:00', 'capster' => 'Dika'],
        ['name' => 'Dedi Santoso', 'service' => 'Warnai Rambut', 'time' => '31 Mei 2026 · 13:00', 'capster' => 'Bayu'],
    ];
@endphp
<div class="grid gap-6">
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-stat-card label="Total Booking" value="128" icon="▣" />
        <x-stat-card label="Booking Hari Ini" value="23" icon="◷" />
        <x-stat-card label="Capster" value="8" icon="♙" />
        <x-stat-card label="Pelanggan" value="256" icon="☷" />
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
                @foreach ($waiting as $item)
                    <article class="flex flex-col gap-4 rounded-2xl border border-gaz-border bg-black/25 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3"><div class="grid size-12 place-items-center rounded-xl bg-gaz-gold text-black font-black">{{ str($item['name'])->substr(0, 1) }}</div><div><p class="font-black">{{ $item['name'] }}</p><p class="text-xs text-gaz-muted">{{ $item['service'] }} · {{ $item['time'] }} · {{ $item['capster'] }}</p></div></div>
                        <div class="flex gap-2"><x-secondary-button>Konfirmasi</x-secondary-button><x-primary-button href="{{ route('admin.bookings.whatsapp') }}">WhatsApp</x-primary-button></div>
                    </article>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
