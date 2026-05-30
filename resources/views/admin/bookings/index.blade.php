@extends('layouts.admin', ['heading' => 'Booking'])

@section('content')
@php
    $bookings = [
        ['code' => 'GAZ-001', 'user' => 'Rizky Pratama', 'service' => 'Cukur + Cuci', 'capster' => 'Rudi', 'date' => '31 Mei 2026 10:00', 'total' => 150000, 'status' => 'WAITING_CUSTOMER_CONFIRMATION'],
        ['code' => 'GAZ-002', 'user' => 'Ariel Saputra', 'service' => 'Cukur Rambut', 'capster' => 'Dika', 'date' => '31 Mei 2026 11:00', 'total' => 85000, 'status' => 'ACCEPTED'],
        ['code' => 'GAZ-003', 'user' => 'Dedi Santoso', 'service' => 'Warnai Rambut', 'capster' => 'Bayu', 'date' => '31 Mei 2026 13:00', 'total' => 210000, 'status' => 'WAITING_PAYMENT'],
    ];
@endphp
<div x-data="{ detail: false }" class="grid gap-6">
    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <div class="grid gap-3 lg:grid-cols-[1fr_220px_180px]">
            <x-text-input placeholder="Cari booking, pelanggan, capster..." />
            <x-select-input><option>Semua Status</option><option>PENDING</option><option>ACCEPTED</option><option>COMPLETED</option></x-select-input>
            <x-text-input type="date" />
        </div>
    </section>
    <section class="overflow-hidden rounded-2xl border border-gaz-border bg-gaz-card">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[940px] text-left text-sm">
                <thead class="bg-white/[0.04] text-gaz-muted"><tr>@foreach (['Kode', 'User', 'Layanan', 'Capster', 'Jadwal', 'Total', 'Status', 'Aksi'] as $head)<th class="px-4 py-4 font-bold">{{ $head }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-gaz-border">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-white/[0.03]">
                            <td class="px-4 py-4 font-black">{{ $booking['code'] }}</td><td class="px-4 py-4">{{ $booking['user'] }}</td><td class="px-4 py-4">{{ $booking['service'] }}</td><td class="px-4 py-4">{{ $booking['capster'] }}</td><td class="px-4 py-4">{{ $booking['date'] }}</td><td class="px-4 py-4">Rp{{ number_format($booking['total'], 0, ',', '.') }}</td><td class="px-4 py-4"><x-booking-status-badge :status="$booking['status']" /></td>
                            <td class="px-4 py-4"><div class="flex gap-2"><x-secondary-button @click="detail = true">Detail</x-secondary-button><x-primary-button href="{{ route('admin.bookings.whatsapp') }}">WhatsApp</x-primary-button></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-gaz-muted">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <x-modal name="detail">
        <h2 class="text-2xl font-black">Detail Booking</h2>
        <p class="mt-2 text-gaz-muted">Aksi cepat untuk menerima, menolak, check-in, mulai layanan, dan tandai dibayar.</p>
        <div class="mt-6 flex flex-wrap gap-2">
            <x-primary-button>Terima</x-primary-button><x-danger-button>Tolak</x-danger-button><x-secondary-button>Check-in</x-secondary-button><x-secondary-button>Mulai Layanan</x-secondary-button><x-primary-button>Tandai Dibayar</x-primary-button>
        </div>
    </x-modal>
</div>
@endsection
