@extends('layouts.admin', ['heading' => 'Kelola Capster'])

@section('content')
<section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">Capster</h1>
            <p class="mt-2 text-gaz-muted">Kelola profil, harga jasa, rating, dan status capster.</p>
        </div>
        <x-primary-button href="{{ route('admin.capsters.create') }}">Tambah Capster</x-primary-button>
    </div>

    @if (session('status'))
        <div class="mt-5 rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
    @endif

    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="text-gaz-muted">
                <tr>
                    @foreach (['Foto', 'Nama', 'Rating', 'Harga Jasa', 'Status', 'Aksi'] as $head)
                        <th class="border-b border-gaz-border px-4 py-3">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gaz-border">
                @forelse ($capsters as $capster)
                    <tr>
                        <td class="px-4 py-4">
                            @if ($capster->photo)
                                <img src="{{ asset('storage/'.$capster->photo) }}" alt="Foto {{ $capster->name }}" class="size-12 rounded-xl object-cover">
                            @else
                                <div class="grid size-12 place-items-center rounded-xl bg-gaz-gold font-black text-black">{{ str($capster->name)->substr(0, 1) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-4 font-black">{{ $capster->name }}</td>
                        <td class="px-4 py-4">{{ $capster->rating }}</td>
                        <td class="px-4 py-4">Rp{{ number_format($capster->service_fee, 0, ',', '.') }}</td>
                        <td class="px-4 py-4">{{ $capster->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-4">
                            <div class="flex flex-wrap gap-2">
                                <x-secondary-button href="{{ route('admin.capsters.edit', $capster) }}">Edit</x-secondary-button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gaz-muted">Belum ada capster.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
