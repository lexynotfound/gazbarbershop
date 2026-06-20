@extends('layouts.admin', ['heading' => 'Chart Booking'])

@section('content')
<div class="grid gap-6">
    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-black">Booking Terbaru</h1>
                <p class="mt-2 text-gaz-muted">Detail jumlah booking harian dalam 30 hari terakhir.</p>
            </div>
            <x-secondary-button href="{{ route('admin.dashboard') }}">Kembali ke Dashboard</x-secondary-button>
        </div>

        <div class="mt-8 h-[28rem] rounded-2xl border border-gaz-border bg-black/25 p-5">
            <div class="flex h-full items-end gap-1.5 sm:gap-2">
                @foreach ($recentBookingChart as $point)
                    <div class="group relative flex h-full flex-1 flex-col justify-end gap-2">
                        <div class="sr-only">{{ $point['label'] }}: {{ $point['total'] }} booking</div>
                        <div class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-3 min-w-32 -translate-x-1/2 rounded-xl border border-gaz-border bg-gaz-card px-3 py-2 text-center text-xs font-bold text-white opacity-0 shadow-xl transition group-hover:opacity-100 group-focus-within:opacity-100">
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
        <h2 class="text-xl font-black">Detail Data</h2>
        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead class="text-gaz-muted">
                    <tr>
                        <th class="border-b border-gaz-border px-4 py-3">Tanggal</th>
                        <th class="border-b border-gaz-border px-4 py-3">Total Booking</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gaz-border">
                    @foreach ($recentBookingChart as $point)
                        <tr>
                            <td class="px-4 py-3 font-bold">{{ $point['label'] }}</td>
                            <td class="px-4 py-3">{{ $point['total'] }} booking</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
