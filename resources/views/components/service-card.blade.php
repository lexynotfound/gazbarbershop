@props(['service', 'href' => null])

@php
    $classes = 'group block rounded-2xl border border-gaz-border bg-gaz-card p-5 transition hover:-translate-y-1 hover:border-gaz-gold/50 hover:bg-white/[0.06]';
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <div class="flex items-center gap-4">
            <div class="grid size-14 place-items-center rounded-2xl bg-gaz-gold/10 text-2xl text-gaz-gold">✂</div>
            <div class="min-w-0">
                <h3 class="truncate text-lg font-black text-white">{{ $service['name'] }}</h3>
                <p class="text-sm text-gaz-muted">{{ $service['duration'] ?? $service['duration_minutes'] }} menit</p>
            </div>
        </div>
        <div class="mt-5 flex items-center justify-between gap-4">
            <p class="font-bold text-gaz-gold">Mulai dari Rp{{ number_format($service['price'], 0, ',', '.') }}</p>
            <span class="text-2xl text-white transition group-hover:translate-x-1">›</span>
        </div>
    </a>
@else
    <article {{ $attributes->merge(['class' => $classes]) }}>
        <div class="flex items-center gap-4">
            <div class="grid size-14 place-items-center rounded-2xl bg-gaz-gold/10 text-2xl text-gaz-gold">✂</div>
            <div class="min-w-0">
                <h3 class="truncate text-lg font-black text-white">{{ $service['name'] }}</h3>
                <p class="text-sm text-gaz-muted">{{ $service['duration'] ?? $service['duration_minutes'] }} menit</p>
            </div>
        </div>
        <div class="mt-5 flex items-center justify-between gap-4">
            <p class="font-bold text-gaz-gold">Mulai dari Rp{{ number_format($service['price'], 0, ',', '.') }}</p>
            <span class="text-2xl text-white transition group-hover:translate-x-1">›</span>
        </div>
    </article>
@endif
