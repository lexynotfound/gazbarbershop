@extends('layouts.admin', ['heading' => 'Kelola Layanan'])

@section('content')
<section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">Layanan</h1>
            <p class="mt-2 text-gaz-muted">Kelola gambar, harga, durasi, dan status layanan.</p>
        </div>
        <x-primary-button href="{{ route('admin.services.create') }}">Tambah Layanan</x-primary-button>
    </div>

    @if (session('status'))
        <div class="mt-5 rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
    @endif

    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[780px] text-left text-sm">
            <thead class="text-gaz-muted">
                <tr>
                    @foreach (['Gambar', 'Nama', 'Harga', 'Durasi', 'Status', 'Aksi'] as $head)
                        <th class="border-b border-gaz-border px-4 py-3">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gaz-border">
                @forelse ($services as $service)
                    <tr>
                        <td class="px-4 py-4">
                            @if ($service->image)
                                <img src="{{ asset('storage/'.$service->image) }}" alt="Gambar {{ $service->name }}" class="size-12 rounded-xl object-cover">
                            @else
                                <div class="grid size-12 place-items-center rounded-xl bg-gaz-gold/10 font-black text-gaz-gold">{{ str($service->name)->substr(0, 1) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-black">{{ $service->name }}</td>
                        <td class="px-4 py-4">Rp{{ number_format($service->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-4">{{ $service->duration_minutes }} menit</td>
                        <td class="px-4 py-4">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-2">
                                <x-secondary-button href="{{ route('admin.services.edit', $service) }}">Edit</x-secondary-button>
                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
                                      onsubmit="return confirm('Hapus layanan {{ $service->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <x-danger-button type="submit">Hapus</x-danger-button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gaz-muted">Belum ada layanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
