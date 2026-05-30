@extends('layouts.user')

@section('user-content')
@php
    $services = [
        ['id' => 1, 'name' => 'Cukur Rambut', 'price' => 40000, 'duration' => 30],
        ['id' => 2, 'name' => 'Cukur + Cuci', 'price' => 60000, 'duration' => 45],
        ['id' => 3, 'name' => 'Warnai Rambut', 'price' => 150000, 'duration' => 90],
        ['id' => 4, 'name' => 'Perawatan Jenggot', 'price' => 50000, 'duration' => 30],
    ];
    $capsters = [
        ['id' => 1, 'name' => 'Rudi', 'rating' => 4.9, 'service_fee' => 50000],
        ['id' => 2, 'name' => 'Dika', 'rating' => 4.8, 'service_fee' => 45000],
        ['id' => 3, 'name' => 'Fahmi', 'rating' => 4.7, 'service_fee' => 40000],
        ['id' => 4, 'name' => 'Bayu', 'rating' => 4.9, 'service_fee' => 60000],
    ];
    $times = ['08:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
    $initialServiceId = (int) request('service');
@endphp
<div x-data="{
    step: 1,
    services: @js($services),
    capsters: @js($capsters),
    times: @js($times),
    selectedServices: @js($initialServiceId ? [$initialServiceId] : []),
    selectedCapster: null,
    selectedDate: new Date().toISOString().slice(0, 10),
    selectedTime: null,
    money(value) { return new Intl.NumberFormat('id-ID').format(value || 0) },
    toggleService(id) { this.selectedServices = this.selectedServices.includes(id) ? this.selectedServices.filter(serviceId => serviceId !== id) : [...this.selectedServices, id] },
    serviceTotal() { return this.services.filter(service => this.selectedServices.includes(service.id)).reduce((total, service) => total + service.price, 0) },
    capsterFee() { const capster = this.capsters.find(item => item.id === this.selectedCapster); return capster ? capster.service_fee : 0 },
    grandTotal() { return this.serviceTotal() + this.capsterFee() },
    canNext() { return (this.step === 1 && this.selectedServices.length) || (this.step === 2 && this.selectedCapster) || (this.step === 3 && this.selectedDate && this.selectedTime) || this.step === 4 },
    canSubmit() { return this.selectedServices.length && this.selectedCapster && this.selectedDate && this.selectedTime }
}" class="grid gap-6 lg:grid-cols-[1fr_360px]">
    <form method="POST" action="{{ route('booking.store') }}" class="rounded-2xl border border-gaz-border bg-gaz-card p-5 sm:p-6">
        @csrf

        <template x-for="serviceId in selectedServices" :key="serviceId">
            <input type="hidden" name="service_ids[]" :value="serviceId">
        </template>
        <input type="hidden" name="capster_id" :value="selectedCapster || ''">
        <input type="hidden" name="booking_date" :value="selectedDate">
        <input type="hidden" name="booking_time" :value="selectedTime || ''">

        @if (session('status'))
            <div class="mb-5 rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            <template x-for="number in [1,2,3,4]" :key="number">
                <button type="button" @click="step = number" class="rounded-full border px-4 py-2 text-sm font-bold" :class="step === number ? 'border-gaz-gold bg-gaz-gold text-black' : 'border-gaz-border text-gaz-muted'">Step <span x-text="number"></span></button>
            </template>
        </div>

        <div class="mt-7" x-show="step === 1">
            <h1 class="text-3xl font-black">Pilih Layanan</h1>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <template x-for="service in services" :key="service.id">
                    <button type="button" @click="toggleService(service.id)" class="cursor-pointer rounded-2xl border p-4 text-left transition" :class="selectedServices.includes(service.id) ? 'border-gaz-gold bg-gaz-gold/10 ring-2 ring-gaz-gold/40' : 'border-gaz-border bg-black/20 hover:border-gaz-gold/40'">
                        <span class="block font-black text-white" x-text="service.name"></span>
                        <span class="mt-1 block text-sm text-gaz-muted"><span x-text="service.duration"></span> menit · Rp<span x-text="money(service.price)"></span></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="mt-7" x-show="step === 2">
            <h1 class="text-3xl font-black">Pilih Capster</h1>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <template x-for="capster in capsters" :key="capster.id">
                    <button type="button" @click="selectedCapster = capster.id" class="rounded-2xl border p-4 text-left transition" :class="selectedCapster === capster.id ? 'border-gaz-gold bg-gaz-gold/10 ring-2 ring-gaz-gold/40' : 'border-gaz-border bg-black/20 hover:border-gaz-gold/40'">
                        <span class="flex items-center gap-4"><span class="grid size-14 place-items-center rounded-2xl bg-gaz-gold text-xl font-black text-black" x-text="capster.name[0]"></span><span><span class="block font-black" x-text="capster.name"></span><span class="text-sm text-gaz-muted">Rating <span x-text="capster.rating"></span> · Tersedia</span></span></span>
                        <span class="mt-4 block font-bold text-gaz-gold">Jasa Rp<span x-text="money(capster.service_fee)"></span></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="mt-7" x-show="step === 3">
            <h1 class="text-3xl font-black">Pilih Jadwal</h1>
            <div class="mt-5"><x-input-label>Tanggal</x-input-label><x-text-input type="date" x-model="selectedDate" /></div>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <template x-for="time in times" :key="time">
                    <button type="button" @click="selectedTime = time" class="rounded-xl border px-4 py-3 font-bold" :class="selectedTime === time ? 'border-gaz-gold bg-gaz-gold text-black' : 'border-gaz-border text-white hover:border-gaz-gold/50'" x-text="time"></button>
                </template>
            </div>
            <p class="mt-4 rounded-xl border border-yellow-500/30 bg-yellow-500/10 p-4 text-sm text-yellow-200">Datang maksimal 15 menit sebelum jadwal.</p>
        </div>

        <div class="mt-7" x-show="step === 4">
            <h1 class="text-3xl font-black">Ringkasan Booking</h1>
            <p class="mt-3 text-gaz-muted">Periksa kembali pilihanmu sebelum mengirim booking.</p>
        </div>

        <div class="mt-8 flex justify-between gap-3">
            <x-secondary-button x-show="step > 1" @click="step--">Kembali</x-secondary-button>
            <x-primary-button x-show="step < 4" @click="if (canNext()) step++" x-bind:disabled="!canNext()">Lanjut</x-primary-button>
            <x-primary-button type="submit" x-show="step === 4" x-bind:disabled="!canSubmit()">Booking Sekarang</x-primary-button>
        </div>
    </form>

    <aside class="h-fit rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <h2 class="text-xl font-black">Total Booking</h2>
        <div class="mt-5 grid gap-3 text-sm">
            <div class="flex justify-between gap-4 text-gaz-muted"><span>Layanan</span><span>Rp<span x-text="money(serviceTotal())"></span></span></div>
            <div class="flex justify-between gap-4 text-gaz-muted"><span>Jasa Capster</span><span>Rp<span x-text="money(capsterFee())"></span></span></div>
            <div class="flex justify-between gap-4 border-t border-gaz-border pt-3 text-lg font-black text-white"><span>Grand Total</span><span>Rp<span x-text="money(grandTotal())"></span></span></div>
            <div class="rounded-xl bg-black/30 p-4 text-gaz-muted"><span x-text="selectedDate"></span> · <span x-text="selectedTime || 'Pilih jam'"></span></div>
        </div>
    </aside>
</div>
@endsection
