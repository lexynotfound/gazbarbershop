@extends('layouts.admin', ['heading' => 'Jadwal Capster'])

@section('content')
<div class="grid gap-6">
    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black">List Jadwal Capster</h2>
                <p class="mt-2 text-sm text-gaz-muted">Klik capster untuk melihat atau mengubah jadwal kerjanya.</p>
            </div>
            <x-primary-button href="{{ route('admin.schedules.create') }}">Tambah Jadwal</x-primary-button>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            @forelse ($capsters as $capster)
                <a href="{{ route('admin.schedules.by-capster', $capster) }}" class="block rounded-2xl border border-gaz-border bg-black/25 p-4 transition hover:border-gaz-gold/60 hover:bg-gaz-gold/10 focus:outline-none focus:ring-2 focus:ring-gaz-gold/40">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-black">{{ $capster->name }}</p>
                            <p class="text-sm text-gaz-muted">{{ $capster->schedules_count }} jadwal terdaftar</p>
                        </div>
                        @php
                            $hasAvailable = $capster->schedules->contains('is_available', true);
                        @endphp
                        <span class="text-sm font-bold {{ $hasAvailable ? 'text-gaz-gold' : 'text-red-300' }}">
                            {{ $hasAvailable ? 'Tersedia' : 'Tidak Tersedia' }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="col-span-2 rounded-2xl border border-dashed border-gaz-border bg-black/20 p-5 text-sm text-gaz-muted">Belum ada jadwal capster.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
