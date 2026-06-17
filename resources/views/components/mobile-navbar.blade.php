@php
    $accountRoute = auth()->user()?->role === 'admin' ? 'admin.dashboard' : (auth()->check() ? 'dashboard' : 'login');
    $bookingRoute = auth()->user()?->role === 'admin' ? 'admin.bookings.index' : 'booking.create';
@endphp

<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gaz-border bg-gaz-black/95 px-4 py-2 backdrop-blur-xl lg:hidden">
    <div class="mx-auto grid max-w-md grid-cols-4 gap-1 text-center text-[0.72rem] font-bold">
        @foreach ([['&#8962;', 'Beranda', 'home'], ['&#9986;', 'Layanan', 'services'], ['&#9635;', 'Booking', $bookingRoute], [auth()->check() ? '&#9817;' : '&#8618;', auth()->check() ? 'Akun' : 'Login', $accountRoute]] as [$icon, $label, $route])
            <a href="{{ route($route) }}" class="rounded-xl px-2 py-2 text-gaz-muted hover:bg-white/5 hover:text-gaz-gold">
                <span class="block text-lg">{!! $icon !!}</span>{{ $label }}
            </a>
        @endforeach
    </div>
</nav>
