<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Admin GAZ Barbershop' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body x-data="{ sidebar: false }" class="min-h-screen bg-gaz-black font-sans text-white antialiased">
    <div class="lg:grid lg:min-h-screen lg:grid-cols-[288px_1fr]">
        <x-sidebar class="hidden lg:block" />
        <div class="min-w-0">
            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-gaz-border bg-gaz-black/85 px-4 py-4 backdrop-blur-xl sm:px-6 lg:px-8">
                <button @click="sidebar = true" class="grid size-11 place-items-center rounded-xl border border-gaz-border text-2xl lg:hidden">☰</button>
                <div>
                    <p class="text-xs font-bold uppercase text-gaz-gold">Dashboard Admin</p>
                    <h1 class="text-xl font-black">{{ $heading ?? 'GAZ Barbershop' }}</h1>
                </div>
                <a href="{{ route('home') }}" class="rounded-xl border border-gaz-border px-4 py-2 text-sm font-bold text-gaz-muted hover:text-white">Lihat Site</a>
            </header>
            <main class="p-4 sm:p-6 lg:p-8">@yield('content')</main>
        </div>
    </div>
    <div x-cloak x-show="sidebar" class="fixed inset-0 z-50 bg-black/70 lg:hidden" x-transition.opacity>
        <div @click.outside="sidebar = false" class="h-full w-72">
            <x-sidebar />
        </div>
    </div>
</body>
</html>
