@extends('layouts.user')

@section('user-content')
@php
    $initial = str($user->name)->substr(0, 1);
@endphp

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    @csrf
    @method('PATCH')

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">Profil</h1>
            <p class="mt-2 text-sm text-gaz-muted">Kelola nama, email, WhatsApp, dan foto profil akun kamu.</p>
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-gaz-border bg-black/25 p-3">
            @if ($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" alt="Foto {{ $user->name }}" class="size-14 rounded-xl object-cover">
            @else
                <div class="grid size-14 place-items-center rounded-xl bg-gaz-gold text-xl font-black text-black">{{ $initial }}</div>
            @endif
            <div>
                <p class="text-sm font-black">{{ $user->name }}</p>
                <p class="text-xs text-gaz-muted">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
    @endif

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="name">Nama</x-input-label>
            <x-text-input id="name" name="name" value="{{ old('name', $user->name) }}" required />
            @error('name')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label for="email">Email</x-input-label>
            <x-text-input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required />
            @error('email')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label for="phone">WhatsApp</x-input-label>
            <x-text-input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="628..." />
            @error('phone')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label for="avatar">Foto profil</x-input-label>
            <x-text-input id="avatar" name="avatar" type="file" accept="image/*" />
            @error('avatar')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror

            @if ($user->avatar)
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-gaz-border bg-black/20 p-3">
                    <img src="{{ asset('storage/'.$user->avatar) }}" alt="Foto {{ $user->name }}" class="size-14 rounded-xl object-cover">
                    <p class="text-sm text-gaz-muted">Foto saat ini</p>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-8 border-t border-gaz-border pt-6">
        <h2 class="text-xl font-black">Ubah Password</h2>
        <p class="mt-2 text-sm text-gaz-muted">Kosongkan bagian ini kalau kamu hanya ingin mengubah profil.</p>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            <div>
                <x-input-label for="current_password">Password saat ini</x-input-label>
                <x-text-input id="current_password" name="current_password" type="password" autocomplete="current-password" />
                @error('current_password')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="password">Password baru</x-input-label>
                <x-text-input id="password" name="password" type="password" autocomplete="new-password" />
                @error('password')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="password_confirmation">Konfirmasi password</x-input-label>
                <x-text-input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            </div>
        </div>
    </div>

    <x-primary-button type="submit" class="mt-6">Simpan Profil</x-primary-button>
</form>
@endsection
