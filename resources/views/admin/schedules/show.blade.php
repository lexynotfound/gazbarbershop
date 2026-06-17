@extends('layouts.admin', ['heading' => 'Detail Jadwal'])

@section('content')
<div class="grid gap-6">
    <x-breadcrumbs :items="[
        ['label' => 'Jadwal Capster', 'url' => route('admin.schedules.index')],
        ['label' => $schedule->capster->name.' - '.$schedule->work_date->translatedFormat('d F Y')],
    ]" />

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-sm font-bold text-gaz-gold">Detail Jadwal Capster</p>
                <h1 class="mt-2 text-3xl font-black">{{ $schedule->capster->name }}</h1>
                <p class="mt-2 text-gaz-muted">{{ $schedule->work_date->translatedFormat('d F Y') }} - {{ str($schedule->start_time)->substr(0, 5) }}-{{ str($schedule->end_time)->substr(0, 5) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button href="{{ route('admin.schedules.index') }}">Kembali</x-secondary-button>
                <x-primary-button href="{{ route('admin.schedules.edit', $schedule) }}">Edit Jadwal</x-primary-button>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-3">
            <div class="rounded-xl bg-black/25 p-4">
                <p class="text-sm text-gaz-muted">Status jadwal</p>
                <p class="mt-1 font-black {{ $schedule->is_available ? 'text-gaz-gold' : 'text-red-300' }}">{{ $schedule->is_available ? 'Tersedia' : 'Tidak Tersedia' }}</p>
            </div>
            <div class="rounded-xl bg-black/25 p-4">
                <p class="text-sm text-gaz-muted">Slot tersedia</p>
                <p class="mt-1 font-black">{{ collect($slots)->where('available', true)->count() }}</p>
            </div>
            <div class="rounded-xl bg-black/25 p-4">
                <p class="text-sm text-gaz-muted">Slot terbooking</p>
                <p class="mt-1 font-black">{{ collect($slots)->where('status', 'Terbooking')->count() }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <h2 class="text-2xl font-black">Slot Jam</h2>
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-6">
            @foreach ($slots as $slot)
                <div class="rounded-xl border p-4 {{ $slot['available'] ? 'border-gaz-gold/50 bg-gaz-gold/10' : ($slot['status'] === 'Terbooking' ? 'border-yellow-500/30 bg-yellow-500/10' : 'border-red-500/30 bg-red-500/10') }}">
                    <p class="font-black">{{ $slot['label'] }}</p>
                    <p class="mt-1 text-sm {{ $slot['available'] ? 'text-gaz-gold' : ($slot['status'] === 'Terbooking' ? 'text-yellow-200' : 'text-red-200') }}">{{ $slot['status'] }}</p>
                    @if ($slot['booking_code'])
                        <p class="mt-2 text-xs text-gaz-muted">{{ $slot['booking_code'] }} - {{ $slot['customer_name'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <h2 class="text-2xl font-black">Booking Aktif di Jadwal Ini</h2>
        <div class="mt-5 grid gap-3">
            @forelse ($bookings as $booking)
                <a href="{{ route('admin.bookings.show', $booking) }}" class="rounded-xl border border-gaz-border bg-black/25 p-4 transition hover:border-gaz-gold/60">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-black">{{ $booking->booking_code }}</p>
                            <p class="text-sm text-gaz-muted">{{ $booking->user->name }} - {{ $booking->booking_start->format('H:i') }}-{{ $booking->booking_end->format('H:i') }}</p>
                        </div>
                        <x-booking-status-badge :status="$booking->status" />
                    </div>
                </a>
            @empty
                <div class="rounded-xl border border-dashed border-gaz-border bg-black/20 p-5 text-sm text-gaz-muted">Belum ada booking aktif di jadwal ini.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
