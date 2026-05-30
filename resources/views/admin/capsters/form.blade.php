<form class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">{{ $title }}</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div><x-input-label>Nama capster</x-input-label><x-text-input value="Rudi" /></div>
        <div><x-input-label>Foto capster</x-input-label><x-text-input type="file" /></div>
        <div><x-input-label>Harga jasa</x-input-label><x-text-input type="number" value="50000" /></div>
        <div><x-input-label>Status aktif</x-input-label><x-select-input><option>Aktif</option><option>Nonaktif</option></x-select-input></div>
        <div class="sm:col-span-2"><x-input-label>Deskripsi singkat</x-input-label><x-textarea-input>Specialist fade dan gentleman cut.</x-textarea-input></div>
    </div>
    <x-primary-button class="mt-6">Simpan</x-primary-button>
</form>
