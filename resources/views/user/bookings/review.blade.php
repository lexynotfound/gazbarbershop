@extends('layouts.user')

@section('user-content')
<div class="mx-auto grid max-w-3xl gap-5">
    @if (session('status'))
        <div class="rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm font-bold text-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    @if ($selectedBooking)
        @php
            $services = $selectedBooking->items->map(fn ($item) => $item->service->name)->join(' + ');
            $initial = str($selectedBooking->capster->name)->substr(0, 1);
        @endphp

        <form method="POST" action="{{ route('booking.review.store') }}" x-data="{ rating: {{ (int) old('rating', 0) }} }" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
            @csrf
            <input type="hidden" name="booking_id" value="{{ $selectedBooking->id }}">
            <input type="hidden" name="rating" x-bind:value="rating">

            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-gaz-gold">{{ $selectedBooking->booking_code }}</p>
                    <h1 class="mt-1 text-3xl font-black">Berikan Penilaian untuk {{ $selectedBooking->capster->name }}</h1>
                </div>
                <a href="{{ route('booking.review') }}" class="text-sm font-bold text-gaz-muted transition hover:text-white">Pilih booking lain</a>
            </div>

            <div class="mt-6 flex items-center gap-4 rounded-2xl bg-black/25 p-4">
                <div class="grid size-16 shrink-0 place-items-center rounded-2xl bg-gaz-gold text-xl font-black text-black">{{ $initial }}</div>
                <div>
                    <p class="font-black">{{ $selectedBooking->capster->name }}</p>
                    <p class="text-sm text-gaz-muted">Capster - {{ $services }}</p>
                    <p class="mt-1 text-xs font-bold text-gaz-muted">{{ $selectedBooking->booking_start->translatedFormat('d F Y, H:i') }}</p>
                </div>
            </div>

            <div class="mt-6">
                <x-input-label>Rating</x-input-label>
                <div class="mt-2 flex gap-2">
                    <template x-for="star in [1,2,3,4,5]" :key="star">
                        <button type="button" x-on:click="rating = star" class="text-4xl transition" x-bind:class="rating >= star ? 'text-gaz-gold' : 'text-gaz-border'" x-bind:aria-label="`Beri rating ${star}`">&#9733;</button>
                    </template>
                </div>
            </div>

            <div class="mt-6">
                <x-input-label for="comment">Komentar</x-input-label>
                <x-textarea-input id="comment" name="comment" placeholder="Pelayanan bagus dan hasil memuaskan...">{{ old('comment') }}</x-textarea-input>
            </div>

            <x-primary-button type="submit" class="mt-6 w-full" x-bind:disabled="rating === 0">Kirim Ulasan</x-primary-button>
        </form>
    @else
        <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
            <h1 class="text-3xl font-black">Pilih Booking untuk Direview</h1>
            <p class="mt-2 text-sm text-gaz-muted">Review hanya tersedia untuk booking yang sudah selesai cukur dan belum pernah direview.</p>

            <div class="mt-6 grid gap-4">
                @forelse ($reviewableBookings as $booking)
                    @php
                        $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
                    @endphp

                    <article class="rounded-2xl border border-gaz-border bg-black/25 p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gaz-gold">{{ $booking->booking_code }}</p>
                                <p class="font-black">{{ $booking->capster->name }}</p>
                                <p class="mt-1 text-sm text-gaz-muted">{{ $services }} - {{ $booking->booking_start->translatedFormat('d F Y, H:i') }}</p>
                            </div>
                            <x-primary-button href="{{ route('booking.review', ['booking' => $booking->id]) }}">Beri Review</x-primary-button>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-gaz-border p-8 text-center text-gaz-muted">Belum ada booking selesai yang bisa direview.</div>
                @endforelse
            </div>
        </section>
    @endif

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <h2 class="text-2xl font-black">Review yang Sudah Dikirim</h2>
        <p class="mt-2 text-sm text-gaz-muted">Daftar ulasan yang sudah kamu kirim untuk capster.</p>

        <div class="mt-6 grid gap-4">
            @forelse ($submittedReviews as $review)
                @php
                    $services = $review->booking?->items->map(fn ($item) => $item->service?->name)->filter()->join(' + ');
                @endphp

                <article class="rounded-2xl border border-gaz-border bg-black/25 p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-gaz-gold">{{ $review->booking?->booking_code ?? '-' }}</p>
                            <p class="mt-1 font-black">{{ $review->capster?->name ?? '-' }}</p>
                            <p class="mt-1 text-sm text-gaz-muted">{{ $services ?: '-' }}</p>
                            <p class="mt-3 leading-7 text-gaz-muted">{{ $review->comment ?: '-' }}</p>
                        </div>
                        <div class="shrink-0 text-left sm:text-right">
                            <span class="inline-flex rounded-full border border-gaz-gold/30 bg-gaz-gold/10 px-3 py-1 text-xs font-bold text-gaz-gold">{{ $review->rating }}/5</span>
                            <p class="mt-2 text-xs font-bold text-gaz-muted">{{ $review->created_at?->translatedFormat('d F Y H:i') ?? '-' }}</p>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gaz-border p-8 text-center text-gaz-muted">Belum ada review yang sudah dikirim.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
