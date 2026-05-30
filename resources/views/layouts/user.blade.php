@extends('layouts.app')

@section('content')
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[260px_1fr] lg:px-8">
        <aside class="hidden rounded-2xl border border-gaz-border bg-gaz-card p-4 lg:block">
            <nav class="grid gap-2">
                @foreach ([['Booking Sekarang', 'booking.create']] as [$label, $route])
                    <a href="{{ route($route) }}" class="rounded-xl px-4 py-3 text-sm font-bold text-gaz-muted hover:bg-white/5 hover:text-white">{{ $label }}</a>
                @endforeach
                @auth
                    @foreach ([['Dashboard', 'dashboard'], ['Booking Saya', 'bookings.index'], ['Riwayat Booking', 'bookings.index'], ['Review Saya', 'booking.review'], ['Profil', 'profile']] as [$label, $route])
                        <a href="{{ route($route) }}" class="rounded-xl px-4 py-3 text-sm font-bold text-gaz-muted hover:bg-white/5 hover:text-white">{{ $label }}</a>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-xl px-4 py-3 text-left text-sm font-bold text-gaz-muted hover:bg-white/5 hover:text-white">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 text-sm font-bold text-gaz-muted hover:bg-white/5 hover:text-white">Login</a>
                @endauth
            </nav>
        </aside>
        <section>@yield('user-content')</section>
    </div>
@endsection
