@extends('layouts.admin', ['heading' => 'Review'])

@section('content')
<div class="grid gap-6">
    <div class="grid gap-4 sm:grid-cols-2">
        <x-stat-card label="Total Review" :value="$reviews->count()" icon="R" />
        <x-stat-card label="Rata-rata Rating" :value="$averageRating ? number_format($averageRating, 1) : '0.0'" icon="★" />
    </div>

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-black">Review</h1>
                <p class="mt-2 text-gaz-muted">Pantau ulasan pelanggan untuk booking dan capster.</p>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-sm">
                <thead class="text-gaz-muted">
                    <tr>
                        @foreach (['Pelanggan', 'Booking', 'Capster', 'Rating', 'Komentar', 'Tanggal', 'Aksi'] as $head)
                            <th class="border-b border-gaz-border px-4 py-3">{{ $head }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gaz-border">
                    @forelse ($reviews as $review)
                        <tr class="transition hover:bg-white/[0.04]">
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="flex items-center gap-3 rounded-xl outline-none transition hover:text-gaz-gold focus:text-gaz-gold">
                                    <div class="grid size-11 shrink-0 place-items-center rounded-xl bg-gaz-gold font-black text-black">{{ str($review->user->name)->substr(0, 1) }}</div>
                                    <div>
                                        <p class="font-black">{{ $review->user->name }}</p>
                                        <p class="text-xs text-gaz-muted">{{ $review->user->email }}</p>
                                    </div>
                                </a>
                            </td>
                            <td class="px-4 py-4 font-bold">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="transition hover:text-gaz-gold">{{ $review->booking->booking_code }}</a>
                            </td>
                            <td class="px-4 py-4"><a href="{{ route('admin.reviews.show', $review) }}" class="transition hover:text-gaz-gold">{{ $review->capster->name }}</a></td>
                            <td class="px-4 py-4">
                                <a href="{{ route('admin.reviews.show', $review) }}" class="inline-flex rounded-full border border-gaz-gold/30 bg-gaz-gold/10 px-3 py-1 text-xs font-bold text-gaz-gold transition hover:border-gaz-gold">{{ $review->rating }}/5</a>
                            </td>
                            <td class="max-w-sm px-4 py-4 text-gaz-muted"><a href="{{ route('admin.reviews.show', $review) }}" class="transition hover:text-white">{{ $review->comment ?: '-' }}</a></td>
                            <td class="px-4 py-4"><a href="{{ route('admin.reviews.show', $review) }}" class="transition hover:text-gaz-gold">{{ $review->created_at?->translatedFormat('d F Y H:i') ?: '-' }}</a></td>
                            <td class="px-4 py-4">
                                <x-secondary-button href="{{ route('admin.reviews.show', $review) }}">Detail</x-secondary-button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gaz-muted">Belum ada review.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
