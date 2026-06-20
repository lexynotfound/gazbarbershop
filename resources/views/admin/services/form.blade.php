@php
    $service ??= null;
    $isEdit = $service !== null;
@endphp

<form method="POST" action="{{ $isEdit ? route('admin.services.update', $service) : route('admin.services.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    @csrf
    @if ($isEdit)
        @method('PATCH')
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">{{ $title }}</h1>
            <p class="mt-2 text-sm text-gaz-muted">Lengkapi nama, gambar contoh, harga, durasi, dan status layanan.</p>
        </div>
        <x-secondary-button href="{{ route('admin.services.index') }}">Batal</x-secondary-button>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label>Nama layanan</x-input-label>
            <x-text-input name="name" value="{{ old('name', $service?->name) }}" required />
            @error('name')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label>Gambar layanan</x-input-label>
            <x-text-input name="image" type="file" accept="image/*" />
            @error('image')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror

            @if ($service?->image)
                <div class="mt-3 flex items-center gap-3 rounded-xl border border-gaz-border bg-black/20 p-3">
                    <img src="{{ asset('storage/'.$service->image) }}" alt="Gambar {{ $service->name }}" class="size-14 rounded-xl object-cover">
                    <p class="text-sm text-gaz-muted">Gambar saat ini</p>
                </div>
            @endif
        </div>

        <div>
            <x-input-label>Harga</x-input-label>
            <x-text-input name="price" type="number" min="0" value="{{ old('price', $service?->price) }}" required />
            @error('price')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label>Durasi dalam menit</x-input-label>
            <x-text-input name="duration_minutes" type="number" min="1" value="{{ old('duration_minutes', $service?->duration_minutes) }}" required />
            @error('duration_minutes')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <x-input-label>Status aktif</x-input-label>
            <x-select-input name="is_active" required>
                <option value="1" @selected((string) old('is_active', (int) ($service?->is_active ?? true)) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', (int) ($service?->is_active ?? true)) === '0')>Nonaktif</option>
            </x-select-input>
            @error('is_active')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <x-input-label>Deskripsi</x-input-label>
            <x-textarea-input name="description">{{ old('description', $service?->description) }}</x-textarea-input>
            @error('description')<p class="mt-2 text-sm font-bold text-red-300">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3">
        <x-primary-button type="submit">Simpan</x-primary-button>
        <x-secondary-button href="{{ route('admin.services.index') }}">Batal</x-secondary-button>
    </div>
</form>
