@extends('layouts.user')

@section('user-content')
<div class="grid gap-6">
    <div class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <p class="text-sm font-bold text-gaz-gold">Member Area</p>
        <h1 class="mt-2 text-3xl font-black">Dashboard User</h1>
        <p class="mt-3 text-gaz-muted">Pantau booking aktif, riwayat, dan review dari satu tempat.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Booking Aktif" :value="$activeBookingsCount" icon="B" />
        <x-stat-card label="Selesai" :value="$finishedBookingsCount" icon="S" />
        <x-stat-card label="Review" :value="$reviewsCount" icon="R" />
    </div>
</div>
@endsection
