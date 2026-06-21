@props([
    'id' => 'password',
    'name' => 'password',
    'placeholder' => null,
])

<div x-data="{ visible: false }" class="relative">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="password"
        x-bind:type="visible ? 'text' : 'password'"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        {{ $attributes->merge(['class' => 'min-h-11 w-full rounded-xl border border-gaz-border bg-gaz-soft px-4 py-3 pr-12 text-sm text-white outline-none transition placeholder:text-gaz-muted focus:border-gaz-gold focus:ring-2 focus:ring-gaz-gold/20']) }}
    >

    <button
        type="button"
        class="absolute inset-y-0 right-0 grid w-12 place-items-center rounded-r-xl text-gaz-muted transition hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-gaz-gold"
        x-bind:aria-label="visible ? 'Sembunyikan password' : 'Tampilkan password'"
        x-bind:title="visible ? 'Sembunyikan password' : 'Tampilkan password'"
        x-on:click="visible = ! visible"
    >
        <svg x-cloak x-show="! visible" xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M2.1 12s3.4-6.5 9.9-6.5S21.9 12 21.9 12s-3.4 6.5-9.9 6.5S2.1 12 2.1 12Z" />
            <circle cx="12" cy="12" r="3" />
        </svg>
        <svg x-cloak x-show="visible" xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M10.7 5.6A10.5 10.5 0 0 1 12 5.5c6.5 0 9.9 6.5 9.9 6.5a17.9 17.9 0 0 1-2.1 3" />
            <path d="M6.6 6.8A17.6 17.6 0 0 0 2.1 12s3.4 6.5 9.9 6.5a9.8 9.8 0 0 0 4.9-1.3" />
            <path d="M14.1 14.1A3 3 0 0 1 9.9 9.9" />
            <path d="M3 3l18 18" />
        </svg>
    </button>
</div>
