@props(['capster'])

@php
    $photo = $capster['photo'] ?? null;
@endphp

<a href="{{ route('capster.show', $capster['id']) }}" class="block">
    <article {{ $attributes->merge(['class' => 'rounded-2xl border border-gaz-border bg-gaz-card p-5 transition hover:-translate-y-1 hover:border-gaz-gold/50 hover:bg-white/[0.06]']) }}>
        <div class="flex items-center gap-4">
            @if ($photo)
                <img src="{{ asset('storage/'.$photo) }}" alt="Foto {{ $capster['name'] }}" class="size-16 rounded-2xl object-cover">
            @else
                <div class="grid size-16 place-items-center rounded-2xl bg-gradient-to-br from-gaz-gold/80 to-neutral-900 text-xl font-black text-black">{{ str($capster['name'])->substr(0, 1) }}</div>
            @endif
            <div class="min-w-0">
                <h3 class="truncate text-lg font-black text-white">{{ $capster['name'] }}</h3>
                <p class="text-sm text-gaz-muted">⭐ {{ $capster['rating'] }} · Rp{{ number_format($capster['service_fee'], 0, ',', '.') }}</p>
            </div>
        </div>
        <p class="mt-4 text-xs font-medium text-gaz-gold">Lihat Review →</p>
    </article>
</a>
