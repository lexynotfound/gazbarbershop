@props(['name' => 'modal'])

<div x-cloak x-show="{{ $name }}" class="fixed inset-0 z-50 grid place-items-center bg-black/70 p-4 backdrop-blur-sm" x-transition.opacity>
    <div @click.outside="{{ $name }} = false" {{ $attributes->merge(['class' => 'w-full max-w-xl rounded-2xl border border-gaz-border bg-gaz-card p-6 shadow-2xl']) }}>
        {{ $slot }}
    </div>
</div>
