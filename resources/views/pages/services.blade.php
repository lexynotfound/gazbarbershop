@extends('layouts.app')

@section('content')
@php
    $services = [
        ['id' => 1, 'name' => 'Cukur Rambut', 'price' => 40000, 'duration' => 30],
        ['id' => 2, 'name' => 'Cukur + Cuci', 'price' => 60000, 'duration' => 45],
        ['id' => 3, 'name' => 'Warnai Rambut', 'price' => 150000, 'duration' => 90],
        ['id' => 4, 'name' => 'Perawatan Jenggot', 'price' => 50000, 'duration' => 30],
    ];
@endphp
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-4xl font-black">Layanan GAZ Barbershop</h1>
    <p class="mt-3 max-w-2xl text-gaz-muted">Paket grooming premium dengan harga transparan dan durasi yang mudah dijadwalkan.</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($services as $service)
            <x-service-card :service="$service" href="{{ route('booking.create', ['service' => $service['id']]) }}" />
        @empty
            <div class="rounded-2xl border border-dashed border-gaz-border p-8 text-gaz-muted">Belum ada layanan aktif.</div>
        @endforelse
    </div>
</section>
@endsection
