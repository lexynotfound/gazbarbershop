@props(['href' => null, 'type' => 'button'])

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center justify-center rounded-xl bg-gaz-gold px-5 py-3 text-sm font800 font-bold text-black shadow-lg shadow-gaz-gold/15 transition hover:bg-gaz-gold-hover focus:outline-none focus:ring-2 focus:ring-gaz-gold']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex min-h-11 items-center justify-center rounded-xl bg-gaz-gold px-5 py-3 text-sm font-bold text-black shadow-lg shadow-gaz-gold/15 transition hover:bg-gaz-gold-hover disabled:cursor-not-allowed disabled:opacity-45 focus:outline-none focus:ring-2 focus:ring-gaz-gold']) }}>{{ $slot }}</button>
@endif
