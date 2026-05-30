@php
    $items = [
        ['Dashboard', 'admin.dashboard', '▦'],
        ['Booking', 'admin.bookings.index', '▣'],
        ['Capster', 'admin.capsters.index', '♙'],
        ['Layanan', 'admin.services.index', '✂'],
        ['Jadwal Capster', 'admin.schedules.index', '◷'],
        ['Pelanggan', 'admin.bookings.index', '☷'],
        ['Review', 'admin.dashboard', '★'],
        ['Pengaturan', 'admin.dashboard', '⚙'],
        ['Logout', 'home', '↩'],
    ];
@endphp

<aside {{ $attributes->merge(['class' => 'h-full w-72 border-r border-gaz-border bg-black/45 p-5']) }}>
    <x-brand />
    <nav class="mt-8 grid gap-2">
        @foreach ($items as [$label, $route, $icon])
            <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-gaz-muted transition hover:bg-white/5 hover:text-white {{ request()->routeIs($route) ? 'bg-gaz-gold/10 text-gaz-gold' : '' }}">
                <span class="w-5 text-center">{{ $icon }}</span>{{ $label }}
            </a>
        @endforeach
    </nav>
</aside>
