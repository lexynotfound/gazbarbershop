@extends('layouts.admin', ['heading' => 'Pengaturan'])

@section('content')
@php
    $initial = str($user->name)->substr(0, 1);
@endphp

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="rounded-2xl border border-gaz-border bg-gaz-card p-4 sm:p-6">
        @csrf
        @method('PATCH')

        <div>
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-gaz-gold">Akun Administrator</p>
            <h1 class="mt-2 text-3xl font-black">Pengaturan</h1>
            <p class="mt-2 text-sm text-gaz-muted">Kelola identitas, kontak, foto profil, dan keamanan akun admin.</p>
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
        @endif

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="name">Nama admin</x-input-label>
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
                <x-text-input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08123456789" />
                @error('phone')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
            </div>

            <div>
                <x-input-label for="avatar">Foto profil</x-input-label>
                <x-text-input id="avatar" name="avatar" type="file" accept="image/*" />
                @error('avatar')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-8 border-t border-gaz-border pt-6">
            <h2 class="text-xl font-black">Keamanan Akun</h2>
            <p class="mt-2 text-sm text-gaz-muted">Kosongkan bagian password jika tidak ingin mengubahnya.</p>

            <div class="mt-5 grid gap-4 lg:grid-cols-3">
                <div>
                    <x-input-label for="current_password">Password saat ini</x-input-label>
                    <x-password-input id="current_password" name="current_password" autocomplete="current-password" />
                    @error('current_password')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-input-label for="password">Password baru</x-input-label>
                    <x-password-input id="password" name="password" autocomplete="new-password" />
                    @error('password')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-input-label for="password_confirmation">Konfirmasi password</x-input-label>
                    <x-password-input id="password_confirmation" name="password_confirmation" autocomplete="new-password" />
                </div>
            </div>
        </div>

        <x-primary-button type="submit" class="mt-6">Simpan Pengaturan</x-primary-button>
    </form>

    <aside class="h-fit rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <h2 class="text-lg font-black">Profil Admin</h2>
        <div class="mt-5 flex items-center gap-4">
            @if ($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" alt="Foto {{ $user->name }}" class="size-16 rounded-2xl object-cover">
            @else
                <div class="grid size-16 shrink-0 place-items-center rounded-2xl bg-gaz-gold text-2xl font-black text-black">{{ $initial }}</div>
            @endif
            <div class="min-w-0">
                <p class="truncate font-black">{{ $user->name }}</p>
                <p class="truncate text-sm text-gaz-muted">{{ $user->email }}</p>
            </div>
        </div>
        <dl class="mt-6 grid gap-4 border-t border-gaz-border pt-5 text-sm">
            <div class="flex items-center justify-between gap-4">
                <dt class="text-gaz-muted">Role</dt>
                <dd class="font-bold text-gaz-gold">Administrator</dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-gaz-muted">WhatsApp</dt>
                <dd class="font-bold">{{ $user->phone ?: 'Belum diisi' }}</dd>
            </div>
        </dl>
    </aside>
</div>
@endsection
