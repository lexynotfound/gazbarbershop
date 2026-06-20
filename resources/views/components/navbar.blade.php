<header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-white/10 bg-gaz-black/85 backdrop-blur-xl">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <x-brand />
        <nav class="hidden items-center gap-7 lg:flex">
            @foreach ([['Beranda', 'home'], ['Layanan', 'services'], ['Capster', 'capsters'], ['Tentang Kami', 'home']] as [$label, $route])
                <a href="{{ route($route) }}" class="text-sm font-semibold text-gaz-muted transition hover:text-white">{{ $label }}</a>
            @endforeach
            @auth
                @if (auth()->user()->role === 'user')
                    <a href="{{ route('bookings.index') }}" class="text-sm font-semibold text-gaz-muted transition hover:text-white">Booking Saya</a>
                @endif
            @endauth
        </nav>
        <div class="hidden items-center gap-3 lg:flex">
            @auth
                @php
                    $user = auth()->user();
                    $dashboardRoute = $user->role === 'admin' ? route('admin.dashboard') : route('dashboard');
                    $dashboardLabel = $user->role === 'admin' ? 'Dashboard Admin' : 'Dashboard User';
                    $firstName = str($user->name)->before(' ');
                    $avatar = $user->getAttribute('photo') ?? $user->getAttribute('avatar');
                @endphp

                @if ($user->role !== 'admin')
                    <x-primary-button href="{{ route('booking.create') }}">Booking Sekarang</x-primary-button>
                @endif

                <button type="button" aria-label="Notifikasi" class="grid size-10 place-items-center rounded-full border border-transparent bg-black/30 text-base text-gaz-muted transition hover:border-gaz-gold/40 hover:bg-white/5 hover:text-gaz-gold focus:outline-none focus:ring-2 focus:ring-gaz-gold/30">
                    <span aria-hidden="true">&#128276;</span>
                </button>

                <div class="group relative">
                    <button type="button" class="flex max-w-52 items-center gap-2 rounded-full border border-transparent bg-black/30 px-2.5 py-1.5 text-left transition hover:border-gaz-gold/40 hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-gaz-gold/30">
                        @if ($avatar)
                            <img src="{{ asset('storage/'.$avatar) }}" alt="Foto {{ $user->name }}" class="size-8 rounded-full object-cover ring-2 ring-gaz-gold/40">
                        @else
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-gaz-gold text-sm font-black text-black ring-2 ring-gaz-gold/40">{{ str($user->name)->substr(0, 1) }}</span>
                        @endif
                        <span class="truncate text-sm font-black text-white">Halo, {{ $firstName }}</span>
                        <span class="text-xs text-gaz-muted transition group-hover:text-gaz-gold" aria-hidden="true">&#8964;</span>
                    </button>

                    <div class="invisible absolute right-0 top-full z-50 mt-3 w-64 rounded-2xl border border-gaz-border bg-gaz-card p-3 opacity-0 shadow-2xl transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                        <div class="rounded-xl bg-black/25 p-3">
                            <p class="truncate text-sm font-black text-white">{{ $user->name }}</p>
                            <p class="mt-1 text-xs font-bold text-gaz-muted">{{ $user->email }}</p>
                        </div>
                        <a href="{{ $dashboardRoute }}" class="mt-2 block rounded-xl px-3 py-2 text-sm font-bold text-gaz-muted transition hover:bg-white/5 hover:text-white">{{ $dashboardLabel }}</a>
                    </div>
                </div>
            @else
                <x-secondary-button href="{{ route('login') }}">Login</x-secondary-button>
                <x-primary-button href="{{ route('booking.create') }}">Booking Sekarang</x-primary-button>
            @endauth
        </div>
        <button type="button" @click="open = ! open" class="grid size-11 place-items-center rounded-xl border border-gaz-border text-2xl text-white lg:hidden">&#9776;</button>
    </div>
    <div x-cloak x-show="open" x-transition class="border-t border-gaz-border bg-gaz-black px-4 pb-4 lg:hidden">
        <div class="grid gap-2">
            @foreach ([['Beranda', 'home'], ['Layanan', 'services'], ['Capster', 'capsters']] as [$label, $route])
                <a href="{{ route($route) }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">{{ $label }}</a>
            @endforeach
            @auth
                @if (auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Dashboard Admin</a>
                @else
                    <a href="{{ route('booking.create') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Booking Sekarang</a>
                    <a href="{{ route('bookings.index') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Booking Saya</a>
                    <a href="{{ route('dashboard') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Dashboard</a>
                @endif
            @else
                <a href="{{ route('booking.create') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Booking Sekarang</a>
                <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 text-sm font-semibold text-gaz-muted hover:bg-white/5 hover:text-white">Login</a>
            @endauth
        </div>
    </div>
</header>
