@extends('layouts.user')

@section('user-content')
<div class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">Booking Saya</h1>
    <p class="mt-2 text-sm text-gaz-muted">Booking aktif dan jadwal yang masih berjalan.</p>

    <div class="mt-6 grid gap-4">
        @forelse ($upcomingBookings as $booking)
            <x-user-booking-card :booking="$booking" />
        @empty
            <div class="rounded-2xl border border-dashed border-gaz-border p-8 text-center text-gaz-muted">Belum ada booking akan datang.</div>
        @endforelse
    </div>
</div>
@endsection
