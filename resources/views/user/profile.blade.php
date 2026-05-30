@extends('layouts.user')

@section('user-content')
<form class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">Profil</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div><x-input-label>Nama</x-input-label><x-text-input value="Member Demo" /></div>
        <div><x-input-label>Email</x-input-label><x-text-input value="user@gazbarbershop.com" /></div>
        <div class="sm:col-span-2"><x-input-label>WhatsApp</x-input-label><x-text-input value="6281234567002" /></div>
    </div>
    <x-primary-button class="mt-6">Simpan Profil</x-primary-button>
</form>
@endsection
