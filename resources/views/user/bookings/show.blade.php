@extends('layouts.user')

@section('user-content')
<div class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><p class="text-sm text-gaz-gold">GAZ-260531-001</p><h1 class="text-3xl font-black">Detail Booking</h1></div>
        <x-booking-status-badge status="WAITING_CUSTOMER_CONFIRMATION" />
    </div>
    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
        @foreach ([['Layanan', 'Cukur Rambut + Cuci'], ['Capster', 'Rudi'], ['Jadwal', '31 Mei 2026, 10:00'], ['Total', 'Rp150.000']] as [$label, $value])
            <div class="rounded-xl bg-black/25 p-4"><dt class="text-sm text-gaz-muted">{{ $label }}</dt><dd class="mt-1 font-black">{{ $value }}</dd></div>
        @endforeach
    </dl>
</div>
@endsection
