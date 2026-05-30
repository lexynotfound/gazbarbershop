@extends('layouts.user')

@section('user-content')
<div x-data="{ rating: 0 }" class="mx-auto max-w-2xl rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <h1 class="text-3xl font-black">Berikan Penilaian untuk Rudi</h1>
    <div class="mt-6 flex items-center gap-4 rounded-2xl bg-black/25 p-4">
        <div class="grid size-16 place-items-center rounded-2xl bg-gaz-gold text-xl font-black text-black">R</div>
        <div><p class="font-black">Rudi</p><p class="text-sm text-gaz-muted">Capster · Cukur Rambut</p></div>
    </div>
    <div class="mt-6">
        <x-input-label>Rating</x-input-label>
        <div class="mt-2 flex gap-2">
            <template x-for="star in [1,2,3,4,5]" :key="star">
                <button @click="rating = star" class="text-4xl transition" :class="rating >= star ? 'text-gaz-gold' : 'text-gaz-border'">★</button>
            </template>
        </div>
    </div>
    <div class="mt-6"><x-input-label>Komentar</x-input-label><x-textarea-input placeholder="Pelayanan bagus dan hasil memuaskan..." /></div>
    <x-primary-button class="mt-6 w-full" x-bind:disabled="rating === 0">Kirim Ulasan</x-primary-button>
</div>
@endsection
