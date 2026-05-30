<form class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">{{ $title }}</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div><x-input-label>Nama layanan</x-input-label><x-text-input value="Cukur Rambut" /></div>
        <div><x-input-label>Harga</x-input-label><x-text-input type="number" value="40000" /></div>
        <div><x-input-label>Durasi dalam menit</x-input-label><x-text-input type="number" value="30" /></div>
        <div><x-input-label>Status aktif</x-input-label><x-select-input><option>Aktif</option><option>Nonaktif</option></x-select-input></div>
        <div class="sm:col-span-2"><x-input-label>Deskripsi</x-input-label><x-textarea-input>Potongan presisi dengan finishing premium.</x-textarea-input></div>
    </div>
    <x-primary-button class="mt-6">Simpan</x-primary-button>
</form>
