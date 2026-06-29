@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
    <a href="{{ route('capsters') }}" class="inline-flex items-center gap-1 text-sm text-gaz-muted transition hover:text-white">
        ← Semua Capster
    </a>

    <div class="mt-6 rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <div class="flex items-center gap-5">
            @if ($capster->photo)
                <img src="{{ asset('storage/'.$capster->photo) }}" alt="Foto {{ $capster->name }}" class="size-20 rounded-2xl object-cover">
            @else
                <div class="grid size-20 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-gaz-gold/80 to-neutral-900 text-2xl font-black text-black">
                    {{ str($capster->name)->substr(0, 1) }}
                </div>
            @endif
            <div class="min-w-0">
                <h1 class="text-2xl font-black text-white">{{ $capster->name }}</h1>
                <p class="mt-1 text-sm text-gaz-muted">⭐ {{ $capster->rating }} · Rp{{ number_format($capster->service_fee, 0, ',', '.') }}</p>
                @if ($capster->description)
                    <p class="mt-2 text-sm text-gaz-muted">{{ $capster->description }}</p>
                @endif
            </div>
        </div>
        <x-primary-button href="{{ route('booking.create') }}" class="mt-6 w-full justify-center">Pilih Capster</x-primary-button>
    </div>

    <div class="mt-8">
        <h2 class="text-xl font-black">
            Review Pelanggan
            <span class="ml-1 text-base font-normal text-gaz-muted">({{ $reviews->count() }})</span>
        </h2>

        @if ($reviews->isEmpty())
            <div class="mt-4 rounded-2xl border border-dashed border-gaz-border bg-gaz-card p-6 text-center text-gaz-muted">
                Belum ada review untuk capster ini.
            </div>
        @else
            <div class="mt-4 space-y-4">
                @foreach ($reviews as $review)
                    <div class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-bold text-white">{{ $review->user->name }}</p>
                                <p class="mt-0.5 text-sm text-gaz-gold">{{ str_repeat('⭐', $review->rating) }} {{ $review->rating }}/5</p>
                            </div>
                            <p class="shrink-0 text-xs text-gaz-muted">{{ $review->created_at->translatedFormat('d M Y') }}</p>
                        </div>
                        @if ($review->comment)
                            <p class="mt-3 text-sm leading-relaxed text-gaz-muted">{{ $review->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
