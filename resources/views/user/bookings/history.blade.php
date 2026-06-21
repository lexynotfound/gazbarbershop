@extends('layouts.user')

@section('user-content')
<div x-data="{ tab: 'Selesai' }" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">Riwayat Booking</h1>
    <p class="mt-2 text-sm text-gaz-muted">Booking yang sudah selesai, direview, atau dibatalkan.</p>

    <div class="mt-6 flex flex-wrap gap-2">
        @foreach (['Selesai', 'Dibatalkan'] as $tab)
            <button @click="tab = '{{ $tab }}'" class="rounded-full border px-4 py-2 text-sm font-bold" :class="tab === '{{ $tab }}' ? 'border-gaz-gold bg-gaz-gold text-black' : 'border-gaz-border text-gaz-muted'">{{ $tab }}</button>
        @endforeach
    </div>

    @foreach ([
        'Selesai' => $finishedBookings,
        'Dibatalkan' => $cancelledBookings,
    ] as $group => $bookings)
        <div x-show="tab === '{{ $group }}'" class="mt-6 grid gap-4">
            @forelse ($bookings as $booking)
                <x-user-booking-card :booking="$booking" />
            @empty
                <div class="rounded-2xl border border-dashed border-gaz-border p-8 text-center text-gaz-muted">Belum ada booking {{ str($group)->lower() }}.</div>
            @endforelse
        </div>
    @endforeach
</div>
@endsection
