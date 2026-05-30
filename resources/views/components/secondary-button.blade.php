@props(['href' => null, 'type' => 'button'])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center justify-center rounded-xl border border-gaz-gold/50 px-5 py-3 text-sm font-bold text-gaz-gold transition hover:border-gaz-gold hover:bg-gaz-gold/10']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center justify-center rounded-xl border border-gaz-gold/50 px-5 py-3 text-sm font-bold text-gaz-gold transition hover:border-gaz-gold hover:bg-gaz-gold/10']) }}>{{ $slot }}</button>
@endif
