@php
    $capster ??= null;
    $isEdit = $capster !== null;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.capsters.update', $capster) : route('admin.capsters.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    @csrf
    @if ($isEdit)
        @method('PATCH')
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">{{ $title }}</h1>
            <p class="mt-2 text-sm text-gaz-muted">Lengkapi profil capster, tarif jasa, status, dan foto.</p>
        </div>
        <x-secondary-button href="{{ route('admin.capsters.index') }}">Batal</x-secondary-button>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label>Nama capster</x-input-label>
            <x-text-input name="name" value="{{ old('name', $capster?->name) }}" required />
            @error('name')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label>Foto capster</x-input-label>
            <x-text-input name="photo" type="file" accept="image/*" />
            @error('photo')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror

            @if ($capster?->photo)
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-gaz-border bg-black/20 p-3">
                    <img src="{{ asset('storage/'.$capster->photo) }}" alt="Foto {{ $capster->name }}" class="size-14 rounded-xl object-cover">
                    <p class="text-sm text-gaz-muted">Foto saat ini</p>
                </div>
            @endif
        </div>

        <div>
            <x-input-label>Harga jasa</x-input-label>
            <x-text-input name="service_fee" type="number" min="0" value="{{ old('service_fee', $capster?->service_fee) }}" required />
            @error('service_fee')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label>Status aktif</x-input-label>
            <x-select-input name="is_active" required>
                <option value="1" @selected((string) old('is_active', (int) ($capster?->is_active ?? true)) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', (int) ($capster?->is_active ?? true)) === '0')>Nonaktif</option>
            </x-select-input>
            @error('is_active')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <x-input-label>Deskripsi singkat</x-input-label>
            <x-textarea-input name="description">{{ old('description', $capster?->description) }}</x-textarea-input>
            @error('description')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <x-primary-button type="submit">Simpan</x-primary-button>
        <x-secondary-button href="{{ route('admin.capsters.index') }}">Batal</x-secondary-button>
    </div>
</form>
