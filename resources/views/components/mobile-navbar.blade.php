<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-gaz-border bg-gaz-black/95 px-4 py-2 backdrop-blur-xl lg:hidden">
    <div class="mx-auto grid max-w-md grid-cols-4 gap-1 text-center text-[0.72rem] font-bold">
        @foreach ([['⌂', 'Beranda', 'home'], ['✂', 'Layanan', 'services'], ['▣', 'Booking', 'booking.create'], [auth()->check() ? '♙' : '↪', auth()->check() ? 'Akun' : 'Login', auth()->check() ? 'dashboard' : 'login']] as [$icon, $label, $route])
            <a href="{{ route($route) }}" class="rounded-xl px-2 py-2 text-gaz-muted hover:bg-white/5 hover:text-gaz-gold">
                <span class="block text-lg">{{ $icon }}</span>{{ $label }}
            </a>
        @endforeach
    </div>
</nav>
