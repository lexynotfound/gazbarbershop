@extends('layouts.app')

@section('content')
<section class="mx-auto grid min-h-[70vh] max-w-md place-items-center px-4 py-12">
    <form method="POST" action="{{ route('login.store') }}" class="w-full rounded-2xl border border-gaz-border bg-gaz-card p-6 shadow-2xl">
        @csrf

        <h1 class="text-3xl font-black">Login</h1>

        @if (session('status'))
            <div class="mt-5 rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="mt-6 grid gap-4">
            <div><x-input-label>Email</x-input-label><x-text-input name="email" type="email" value="{{ old('email') }}" placeholder="user@gazbarbershop.com" required /></div>
            <div><x-input-label>Password</x-input-label><x-text-input name="password" type="password" placeholder="password" required /></div>
            <x-primary-button type="submit" class="w-full">Masuk</x-primary-button>
            <a href="{{ route('register') }}" class="text-center text-sm font-bold text-gaz-gold">Buat akun baru</a>
        </div>
    </form>
</section>
@endsection
