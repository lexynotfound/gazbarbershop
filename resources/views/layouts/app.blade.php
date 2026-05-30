<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'GAZ Barbershop' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gaz-black font-sans text-white antialiased selection:bg-gaz-gold selection:text-black">
    <x-navbar />
    <main class="pb-24 lg:pb-0">{{ $slot ?? '' }}@yield('content')</main>
    <footer class="border-t border-gaz-border bg-black py-10">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 text-sm text-gaz-muted sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <x-brand />
            <p>Premium booking experience untuk grooming modern.</p>
            <p>© {{ date('Y') }} GAZ Barbershop</p>
        </div>
    </footer>
    <x-mobile-navbar />
</body>
</html>
