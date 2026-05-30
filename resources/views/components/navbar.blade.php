<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-white/10 bg-gaz-black/85 backdrop-blur-xl">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <x-brand />
        <nav class="hidden items-center gap-7 lg:flex">
            @foreach ([['Beranda', 'home'], ['Layanan', 'services'], ['Capster', 'capsters'], ['Tentang Kami', 'home']] as [$label, $route])
                <a href="{{ route($route) }}" class="text-sm font-semibold text-gaz-muted transition hover:text-white">{{ $label }}</a>
            @endforeach
            @auth
                <a href="{{ route('bookings.index') }}" class="text-sm font-semibold text-gaz-muted transition hover:text-white">Booking Saya</a>
            @endauth
        </nav>
        <div class="hidden items-center gap-3 lg:flex">
            @auth
                <x-secondary-button href="{{ route('dashboard') }}">Dashboard</x-secondary-button>
            @else
                <x-secondary-button href="{{ route('login') }}">Login</x-secondary-button>
            @endauth
            <x-primary-button href="{{ route('booking.create') }}">Booking Sekarang</x-primary-button>
        </div>
        <button type="button" @click="open = ! open" class="grid size-11 place-items-center rounded-xl border border-gaz-border text-2xl text-white lg:hidden">☰</button>
    </div>
    <div x-cloak x-show="open" x-transition class="border-t border-gaz-border bg-gaz-black px-4 pb-4 lg:hidden">
        <div class="grid gap-2">
            @foreach ([['Beranda', 'home'], ['Layanan', 'services'], ['Capster', 'capsters'], ['Booking Sekarang', 'booking.create']] as [$label, $route])
                <a href="{{ route($route) }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">{{ $label }}</a>
            @endforeach
            @auth
                <a href="{{ route('bookings.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Booking Saya</a>
            @else
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Login</a>
            @endauth
        </div>
    </div>
</header>
