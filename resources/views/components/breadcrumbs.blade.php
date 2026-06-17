@props(['items' => []])

<nav {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2 text-sm']) }} aria-label="Breadcrumb">
    <a href="{{ route('admin.dashboard') }}" class="font-bold text-gaz-muted transition hover:text-white">Dashboard</a>

    @foreach ($items as $item)
        <span class="text-gaz-muted">/</span>

        @if (($item['url'] ?? null) && ! ($loop->last))
            <a href="{{ $item['url'] }}" class="font-bold text-gaz-muted transition hover:text-white">{{ $item['label'] }}</a>
        @else
            <span class="font-bold text-gaz-gold">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
