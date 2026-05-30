@extends('layouts.admin', ['heading' => 'Kelola Layanan'])

@section('content')
@php
    $services = [['Cukur Rambut', 40000, 30, 'Aktif'], ['Cukur + Cuci', 60000, 45, 'Aktif'], ['Warnai Rambut', 150000, 90, 'Aktif'], ['Perawatan Jenggot', 50000, 30, 'Aktif']];
@endphp
<section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"><div><h1 class="text-3xl font-black">Layanan</h1><p class="mt-2 text-gaz-muted">Kelola harga, durasi, dan status layanan.</p></div><x-primary-button href="{{ route('admin.services.create') }}">Tambah Layanan</x-primary-button></div>
    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="text-gaz-muted"><tr>@foreach (['Nama', 'Harga', 'Durasi', 'Status', 'Aksi'] as $head)<th class="border-b border-gaz-border px-4 py-3">{{ $head }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-gaz-border">
                @foreach ($services as [$name, $price, $duration, $status])
                    <tr><td class="px-4 py-4 font-black">{{ $name }}</td><td class="px-4 py-4">Rp{{ number_format($price, 0, ',', '.') }}</td><td class="px-4 py-4">{{ $duration }} menit</td><td class="px-4 py-4">{{ $status }}</td><td class="px-4 py-4"><div class="flex gap-2"><x-secondary-button href="{{ route('admin.services.edit') }}">Edit</x-secondary-button><x-danger-button>Hapus</x-danger-button></div></td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
