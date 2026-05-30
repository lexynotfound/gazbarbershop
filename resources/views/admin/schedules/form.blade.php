<form class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">{{ $title }}</h1>
    <div class="mt-6 grid gap-4">
        <div><x-input-label>Pilih capster</x-input-label><x-select-input><option>Rudi</option><option>Dika</option><option>Fahmi</option><option>Bayu</option></x-select-input></div>
        <div><x-input-label>Pilih tanggal</x-input-label><x-text-input type="date" /></div>
        <div class="grid gap-4 sm:grid-cols-2"><div><x-input-label>Jam mulai</x-input-label><x-text-input type="time" value="08:00" /></div><div><x-input-label>Jam selesai</x-input-label><x-text-input type="time" value="18:00" /></div></div>
        <div><x-input-label>Status</x-input-label><x-select-input><option>Tersedia</option><option>Tidak Tersedia</option></x-select-input></div>
    </div>
    <x-primary-button class="mt-6">Simpan Jadwal</x-primary-button>
</form>
