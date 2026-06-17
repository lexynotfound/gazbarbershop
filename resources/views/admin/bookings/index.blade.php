@extends('layouts.admin', ['heading' => 'Booking'])

@section('content')
<div class="grid gap-6">
    <section class="overflow-hidden rounded-2xl border border-gaz-border bg-gaz-card">
        <div class="flex flex-col gap-3 border-b border-gaz-border p-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-black">List Booking</h2>
                <p class="mt-1 text-sm text-gaz-muted">Semua booking pelanggan yang masuk ke sistem.</p>
            </div>
            <span class="inline-flex rounded-full border border-gaz-gold/30 bg-gaz-gold/10 px-3 py-1 text-xs font-bold text-gaz-gold">{{ $bookings->count() }} Booking</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[940px] text-left text-sm">
                <thead class="bg-white/[0.04] text-gaz-muted">
                    <tr>
                        @foreach (['Kode', 'User', 'Layanan', 'Capster', 'Jadwal', 'Total', 'Status', 'Aksi'] as $head)
                            <th class="px-4 py-4 font-bold">{{ $head }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gaz-border">
                    @forelse ($bookings as $booking)
                        @php
                            $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
                        @endphp
                        <tr class="hover:bg-white/[0.03]">
                            <td class="px-4 py-4 font-black">{{ $booking->booking_code }}</td>
                            <td class="px-4 py-4">{{ $booking->user->name }}</td>
                            <td class="px-4 py-4">{{ $services }}</td>
                            <td class="px-4 py-4">{{ $booking->capster->name }}</td>
                            <td class="px-4 py-4">{{ $booking->booking_start->translatedFormat('d F Y H:i') }}</td>
                            <td class="px-4 py-4">Rp{{ number_format($booking->grand_total, 0, ',', '.') }}</td>
                            <td class="px-4 py-4"><x-booking-status-badge :status="$booking->status" /></td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <x-secondary-button href="{{ route('admin.bookings.show', $booking) }}">Detail</x-secondary-button>
                                    <x-primary-button href="{{ route('admin.bookings.whatsapp', $booking) }}">WhatsApp</x-primary-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-10 text-center text-gaz-muted">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
