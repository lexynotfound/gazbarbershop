@props(['label', 'value', 'icon' => '', 'description' => null])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gaz-border bg-white/[0.04] p-5 shadow-xl shadow-black/20']) }}>
    <div class="flex items-center justify-between gap-4">
        <p class="text-sm text-gaz-muted">{{ $label }}</p>
        <span class="text-xl text-gaz-gold">{{ $icon }}</span>
    </div>
    <p class="mt-3 text-3xl font-black text-white">{{ $value }}</p>
    @if ($description)
        <p class="mt-1 text-xs text-gaz-muted">{{ $description }}</p>
    @endif
</div>
