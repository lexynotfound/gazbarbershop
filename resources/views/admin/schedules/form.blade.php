@php
    $schedule ??= null;
    $isEdit = $schedule !== null;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.schedules.update', $schedule) : route('admin.schedules.store') }}" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    @csrf
    @if ($isEdit)
        @method('PATCH')
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">{{ $title }}</h1>
            <p class="mt-2 text-sm text-gaz-muted">Atur tanggal, jam kerja, dan status ketersediaan capster.</p>
        </div>
        <x-secondary-button href="{{ route('admin.schedules.index') }}">Kembali</x-secondary-button>
    </div>

    <div class="mt-6 grid gap-4">
        <div>
            <x-input-label for="capster_id">Pilih capster</x-input-label>
            <x-select-input id="capster_id" name="capster_id" required>
                @foreach ($capsters as $capster)
                    <option value="{{ $capster->id }}" @selected((int) old('capster_id', $selectedCapsterId) === $capster->id)>{{ $capster->name }}</option>
                @endforeach
            </x-select-input>
            @error('capster_id')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>
        <div>
            <x-input-label for="work_date">Pilih tanggal</x-input-label>
            <x-text-input id="work_date" name="work_date" type="date" value="{{ old('work_date', $schedule?->work_date?->toDateString()) }}" required />
            @error('work_date')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="start_time">Jam mulai</x-input-label>
                <x-text-input id="start_time" name="start_time" type="time" min="{{ $operatingStart }}" max="21:30" step="1800" value="{{ old('start_time', $schedule ? str($schedule->start_time)->substr(0, 5) : $operatingStart) }}" required />
                <p class="mt-2 text-xs text-gaz-muted">Minimal {{ $operatingStart }}, dalam batas operasional sampai {{ $operatingEnd }}.</p>
                @error('start_time')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
            </div>
            <div>
                <x-input-label for="end_time">Jam selesai</x-input-label>
                <x-text-input id="end_time" name="end_time" type="time" min="10:30" max="{{ $operatingEnd }}" step="1800" value="{{ old('end_time', $schedule ? str($schedule->end_time)->substr(0, 5) : $operatingEnd) }}" required />
                <p class="mt-2 text-xs text-gaz-muted">Maksimal {{ $operatingEnd }} dan harus setelah jam mulai.</p>
                @error('end_time')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            <x-input-label for="is_available">Status</x-input-label>
            <x-select-input id="is_available" name="is_available" required>
                <option value="1" @selected((string) old('is_available', (int) ($schedule?->is_available ?? true)) === '1')>Tersedia</option>
                <option value="0" @selected((string) old('is_available', (int) ($schedule?->is_available ?? true)) === '0')>Tidak Tersedia</option>
            </x-select-input>
            @error('is_available')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="mt-6 flex flex-wrap gap-3">
        <x-primary-button type="submit">Simpan Jadwal</x-primary-button>
        <x-secondary-button href="{{ route('admin.schedules.index') }}">Batal</x-secondary-button>
    </div>
</form>
