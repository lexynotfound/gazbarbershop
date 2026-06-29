@extends('layouts.admin', ['heading' => 'Jadwal ' . $capster->name])

@section('content')
<div class="grid gap-6">
    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ route('admin.schedules.index') }}" class="mb-2 inline-flex items-center gap-1 text-sm text-gaz-muted hover:text-white">← Kembali ke Daftar Capster</a>
                <h2 class="text-2xl font-black">Jadwal {{ $capster->name }}</h2>
                <p class="mt-2 text-sm text-gaz-muted">{{ $schedules->count() }} jadwal terdaftar.</p>
            </div>
            <x-primary-button href="{{ route('admin.schedules.create', ['capster' => $capster->id]) }}">Tambah Jadwal</x-primary-button>
        </div>

        <div class="mt-5 grid gap-3">
            @forelse ($schedules as $schedule)
                <div class="rounded-2xl border border-gaz-border bg-black/25 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('admin.schedules.show', $schedule) }}" class="flex-1 transition hover:text-gaz-gold focus:outline-none">
                            <p class="font-black">{{ $schedule->work_date->translatedFormat('d F Y') }}</p>
                            <p class="text-sm text-gaz-muted">{{ str($schedule->start_time)->substr(0, 5) }} - {{ str($schedule->end_time)->substr(0, 5) }}</p>
                        </a>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-bold {{ $schedule->is_available ? 'text-gaz-gold' : 'text-red-300' }}">
                                {{ $schedule->is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                            </span>
                            <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}"
                                  onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">Hapus</x-danger-button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gaz-border bg-black/20 p-5 text-sm text-gaz-muted">Belum ada jadwal untuk capster ini.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
