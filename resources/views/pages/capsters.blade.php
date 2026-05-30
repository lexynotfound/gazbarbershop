@extends('layouts.app')

@section('content')
@php
    $capsters = [
        ['name' => 'Rudi', 'rating' => 4.9, 'service_fee' => 50000],
        ['name' => 'Dika', 'rating' => 4.8, 'service_fee' => 45000],
        ['name' => 'Fahmi', 'rating' => 4.7, 'service_fee' => 40000],
        ['name' => 'Bayu', 'rating' => 4.9, 'service_fee' => 60000],
    ];
@endphp
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-4xl font-black">Capster Profesional</h1>
    <p class="mt-3 max-w-2xl text-gaz-muted">Pilih capster berdasarkan rating, gaya kerja, dan harga jasa.</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($capsters as $capster)
            <x-capster-card :capster="$capster" />
        @endforeach
    </div>
</section>
@endsection
