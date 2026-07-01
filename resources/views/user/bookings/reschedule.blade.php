@extends('layouts.user')

@section('user-content')
@php
    $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
    $durationMinutes = (int) $booking->items->sum('duration_minutes');
@endphp
<div x-data="{
    selectedDate: '{{ $booking->booking_start->toDateString() }}',
    selectedTime: null,
    slots: [],
    loadingSlots: false,
    money(value) { return new Intl.NumberFormat('id-ID').format(value || 0) },
    init() { this.loadSlots(); this.$watch('selectedDate', () => this.loadSlots()) },
    async loadSlots() {
        this.slots = [];
        this.selectedTime = null;
        if (!this.selectedDate) return;

        this.loadingSlots = true;
        const params = new URLSearchParams({
            capster_id: {{ $booking->capster_id }},
            booking_date: this.selectedDate,
            duration_minutes: {{ $durationMinutes }},
            exclude_booking_id: {{ $booking->id }},
        });
        const response = await fetch(`{{ route('booking.available-times') }}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
        const data = await response.json();
        this.slots = data.slots || [];
        this.loadingSlots = false;
    }
}" class="grid gap-6 lg:grid-cols-[1fr_360px]">
    <form method="POST" action="{{ route('booking.reschedule', $booking) }}" class="rounded-2xl border border-gaz-border bg-gaz-card p-5 sm:p-6">
        @csrf
        @method('PATCH')

        <input type="hidden" name="booking_date" :value="selectedDate">
        <input type="hidden" name="booking_time" :value="selectedTime || ''">

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <h1 class="text-3xl font-black">Reschedule Booking</h1>
        <p class="mt-2 text-sm text-gaz-muted">{{ $booking->booking_code }} - {{ $services }} - {{ $booking->capster->name }}</p>

        <div class="mt-6"><x-input-label>Tanggal Baru</x-input-label><x-text-input type="date" x-model="selectedDate" /></div>
        <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
            <template x-if="loadingSlots">
                <p class="col-span-full rounded-xl border border-gaz-border bg-black/20 p-4 text-sm text-gaz-muted">Memuat slot jadwal...</p>
            </template>
            <template x-if="!loadingSlots && !slots.length">
                <p class="col-span-full rounded-xl border border-yellow-500/30 bg-yellow-500/10 p-4 text-sm text-yellow-200">Tidak ada slot tersedia untuk tanggal ini.</p>
            </template>
            <template x-for="slot in slots" :key="slot.time">
                <button type="button" @click="if (slot.available) selectedTime = slot.time" class="rounded-xl border px-4 py-3 text-left font-bold disabled:cursor-not-allowed disabled:opacity-50" :disabled="!slot.available" :class="selectedTime === slot.time ? 'border-gaz-gold bg-gaz-gold text-black' : (slot.available ? 'border-gaz-border text-white hover:border-gaz-gold/50' : 'border-red-500/30 bg-red-500/10 text-red-200')">
                    <span class="block" x-text="slot.time"></span>
                    <span class="mt-1 block text-xs font-bold" x-text="slot.status"></span>
                </button>
            </template>
        </div>
        <p class="mt-4 rounded-xl border border-yellow-500/30 bg-yellow-500/10 p-4 text-sm text-yellow-200">Datang maksimal 15 menit sebelum jadwal dimulai.</p>

        <div class="mt-8 flex justify-between gap-3">
            <x-secondary-button href="{{ route('booking.show', $booking) }}">Batal</x-secondary-button>
            <x-primary-button type="submit" x-bind:disabled="!selectedTime">Simpan Jadwal Baru</x-primary-button>
        </div>
    </form>

    <aside class="h-fit rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <h2 class="text-xl font-black">Jadwal Saat Ini</h2>
        <div class="mt-4 rounded-xl bg-black/30 p-4 text-sm text-gaz-muted">{{ $booking->booking_start->translatedFormat('d F Y, H:i') }}</div>
        <div class="mt-4 grid gap-2 text-sm">
            <div class="flex justify-between gap-4 text-gaz-muted"><span>Total Harga</span><span class="font-bold text-white">Rp{{ number_format($booking->grand_total, 0, ',', '.') }}</span></div>
        </div>
    </aside>
</div>
@endsection
