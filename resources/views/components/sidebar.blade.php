@php
    $items = [
        ['Dashboard', 'admin.dashboard', 'D'],
        ['Booking', 'admin.bookings.index', 'B'],
        ['Capster', 'admin.capsters.index', 'C'],
        ['Layanan', 'admin.services.index', 'L'],
        ['Jadwal Capster', 'admin.schedules.index', 'J'],
        ['Pelanggan', 'admin.customers.index', 'P'],
        ['Transaksi', 'admin.transactions.index', 'Rp'],
        ['Review', 'admin.reviews.index', 'R'],
        ['Pengaturan', 'admin.settings.edit', 'S'],
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
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-gaz-muted transition hover:bg-white/5 hover:text-white">
                <span class="w-5 text-center">Out</span>Logout
            </button>
        </form>
    </nav>
</aside>
