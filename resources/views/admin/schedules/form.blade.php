@php
    $schedule ??= null;
@endphp

<form class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">{{ $title }}</h1>
            <p class="mt-2 text-sm text-gaz-muted">Atur tanggal, jam kerja, dan status ketersediaan capster.</p>
        </div>
        <x-secondary-button href="{{ route('admin.schedules.index') }}">Kembali</x-secondary-button>
    </div>

    <div class="mt-6 grid gap-4">
        <div>
            <x-input-label>Pilih capster</x-input-label>
            <x-select-input name="capster_id">
                @foreach ($capsters as $capster)
                    <option value="{{ $capster->id }}" @selected((int) old('capster_id', $schedule?->capster_id) === $capster->id)>{{ $capster->name }}</option>
                @endforeach
            </x-select-input>
        </div>
        <div>
            <x-input-label>Pilih tanggal</x-input-label>
            <x-text-input name="work_date" type="date" value="{{ old('work_date', $schedule?->work_date?->toDateString()) }}" />
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label>Jam mulai</x-input-label>
                <x-text-input name="start_time" type="time" value="{{ old('start_time', $schedule ? str($schedule->start_time)->substr(0, 5) : '08:00') }}" />
            </div>
            <div>
                <x-input-label>Jam selesai</x-input-label>
                <x-text-input name="end_time" type="time" value="{{ old('end_time', $schedule ? str($schedule->end_time)->substr(0, 5) : '18:00') }}" />
            </div>
        </div>
        <div>
            <x-input-label>Status</x-input-label>
            <x-select-input name="is_available">
                <option value="1" @selected((string) old('is_available', (int) ($schedule?->is_available ?? true)) === '1')>Tersedia</option>
                <option value="0" @selected((string) old('is_available', (int) ($schedule?->is_available ?? true)) === '0')>Tidak Tersedia</option>
            </x-select-input>
        </div>
    </div>
    <div class="mt-6 flex flex-wrap gap-3">
        <x-primary-button>Simpan Jadwal</x-primary-button>
        <x-secondary-button href="{{ route('admin.schedules.index') }}">Batal</x-secondary-button>
    </div>
</form>
