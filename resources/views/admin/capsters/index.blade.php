@extends('layouts.admin', ['heading' => 'Kelola Capster'])

@section('content')
@php
    $capsters = [['Rudi', 4.9, 50000, 'Aktif'], ['Dika', 4.8, 45000, 'Aktif'], ['Fahmi', 4.7, 40000, 'Aktif'], ['Bayu', 4.9, 60000, 'Nonaktif']];
@endphp
<section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-3xl font-black">Capster</h1><p class="mt-2 text-gaz-muted">Kelola profil, harga jasa, rating, dan status capster.</p></div><x-primary-button href="{{ route('admin.capsters.create') }}">Tambah Capster</x-primary-button></div>
    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="text-gaz-muted"><tr>@foreach (['Foto', 'Nama', 'Rating', 'Harga Jasa', 'Status', 'Aksi'] as $head)<th class="border-b border-gaz-border px-4 py-3">{{ $head }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-gaz-border">
                @foreach ($capsters as [$name, $rating, $fee, $status])
                    <tr><td class="px-4 py-4"><div class="grid size-12 place-items-center rounded-xl bg-gaz-gold font-black text-black">{{ str($name)->substr(0, 1) }}</div></td><td class="px-4 py-4 font-black">{{ $name }}</td><td class="px-4 py-4">⭐ {{ $rating }}</td><td class="px-4 py-4">Rp{{ number_format($fee, 0, ',', '.') }}</td><td class="px-4 py-4">{{ $status }}</td><td class="px-4 py-4"><div class="flex gap-2"><x-secondary-button href="{{ route('admin.capsters.edit') }}">Edit</x-secondary-button><x-danger-button>Hapus</x-danger-button><x-secondary-button>{{ $status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}</x-secondary-button></div></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
