@extends('layouts.app')

@section('content')
<section class="relative overflow-hidden border-b border-gaz-border bg-[radial-gradient(circle_at_80%_10%,rgba(214,168,79,.18),transparent_35%),#050505]">
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1fr_0.9fr] lg:px-8 lg:py-20">
        <div>
            <p class="text-sm font-black uppercase tracking-[0.28em] text-gaz-gold">GAZ Barbershop</p>
            <h1 class="mt-5 max-w-2xl text-4xl font-black leading-tight text-white sm:text-6xl">Gaya Terbaik, Dimulai Dari Sini</h1>
            <p class="mt-5 max-w-xl text-base leading-8 text-gaz-muted sm:text-lg">Pilih layanan terbaik, capster profesional, dan jadwalkan waktu favoritmu dalam pengalaman booking yang rapi dan premium.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <x-primary-button href="{{ route('booking.create') }}">Booking Sekarang</x-primary-button>
                <x-secondary-button href="{{ route('services') }}">Lihat Layanan</x-secondary-button>
            </div>
        </div>
        <div class="relative min-h-[360px] overflow-hidden rounded-[2rem] border border-gaz-border bg-gaz-card shadow-2xl lg:-mr-8 lg:min-h-[480px] lg:rounded-none lg:border-0">
            <img
                src="{{ asset('images/hero-barbershop.png') }}"
                alt="Capster GAZ Barbershop sedang menata rambut pelanggan"
                class="absolute inset-0 size-full object-cover object-[72%_center]"
                width="1829"
                height="860"
                fetchpriority="high"
            >
            <div class="absolute inset-0 bg-gradient-to-r from-gaz-black via-gaz-black/15 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-gaz-black/70 to-transparent"></div>
        </div>
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-4">
        <div><p class="text-sm font-bold text-gaz-gold">Layanan Kami</p><h2 class="mt-2 text-3xl font-black">Pilih grooming favoritmu</h2></div>
        <x-secondary-button href="{{ route('services') }}" class="hidden sm:inline-flex">Lihat Semua</x-secondary-button>
    </div>
    <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($services as $service)
            <x-service-card :service="$service" />
        @endforeach
    </div>
</section>

<section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between gap-4">
        <div><p class="text-sm font-bold text-gaz-gold">Capster Terbaik</p><h2 class="mt-2 text-3xl font-black">Profesional yang kamu pilih</h2></div>
        <x-secondary-button href="{{ route('capsters') }}" class="hidden sm:inline-flex">Lihat Semua</x-secondary-button>
    </div>
    <div class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($capsters as $capster)
            <x-capster-card :capster="$capster" />
        @endforeach
    </div>
</section>

<section class="border-y border-gaz-border bg-gaz-card/60">
    <div class="mx-auto grid max-w-7xl gap-4 px-4 py-10 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
        @foreach (['Booking Mudah', 'Konfirmasi Cepat', 'Notifikasi Otomatis', 'Review & Rating'] as $benefit)
            <div class="rounded-2xl border border-gaz-border bg-black/30 p-5"><p class="text-lg font-black text-white">{{ $benefit }}</p><p class="mt-2 text-sm text-gaz-muted">Flow jelas, status transparan, dan nyaman dipakai di mobile.</p></div>
        @endforeach
    </div>
</section>
@endsection
