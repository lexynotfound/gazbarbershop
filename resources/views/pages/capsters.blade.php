@extends('layouts.app')

@section('content')
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <h1 class="text-4xl font-black">Capster Profesional</h1>
    <p class="mt-3 max-w-2xl text-gaz-muted">Pilih capster berdasarkan rating, gaya kerja, dan harga jasa.</p>
    <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($capsters as $capster)
            <x-capster-card :capster="$capster" />
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-gaz-border bg-gaz-card p-6 text-center text-gaz-muted">Belum ada capster aktif.</div>
        @endforelse
    </div>
</section>
@endsection
