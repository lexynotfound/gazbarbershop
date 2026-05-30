@props(['capster'])

<article {{ $attributes->merge(['class' => 'rounded-2xl border border-gaz-border bg-gaz-card p-5 transition hover:-translate-y-1 hover:border-gaz-gold/50 hover:bg-white/[0.06]']) }}>
    <div class="flex items-center gap-4">
        <div class="grid size-16 place-items-center rounded-2xl bg-gradient-to-br from-gaz-gold/80 to-neutral-900 text-xl font-black text-black">{{ str($capster['name'])->substr(0, 1) }}</div>
        <div class="min-w-0">
            <h3 class="truncate text-lg font-black text-white">{{ $capster['name'] }}</h3>
            <p class="text-sm text-gaz-muted">⭐ {{ $capster['rating'] }} · Rp{{ number_format($capster['service_fee'], 0, ',', '.') }}</p>
        </div>
    </div>
    <x-secondary-button href="{{ route('booking.create') }}" class="mt-5 w-full">Pilih Capster</x-secondary-button>
</article>
