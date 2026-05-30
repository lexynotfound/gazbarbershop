@props(['type' => 'button'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex min-h-10 items-center justify-center rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white transition hover:bg-red-400']) }}>{{ $slot }}</button>
