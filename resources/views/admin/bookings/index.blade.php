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
                        @foreach (['Kode', 'User', 'Layanan', 'Capster', 'Jadwal', 'Total', 'Status', 'Pembayaran', 'Aksi'] as $head)
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
                                @if ($booking->payment?->status === 'paid')
                                    <span class="inline-flex rounded-full border border-emerald-400/30 bg-emerald-400/10 px-3 py-1 text-xs font-bold text-emerald-200">Lunas</span>
                                    <p class="mt-1 text-xs text-gaz-muted">{{ str($booking->payment->method)->replace('_', ' ')->title() }}</p>
                                @else
                                    <span class="inline-flex rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-xs font-bold text-amber-200">Belum Dibayar</span>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <x-secondary-button href="{{ route('admin.bookings.show', $booking) }}">Detail</x-secondary-button>
                                    <x-primary-button href="{{ route('admin.bookings.whatsapp', $booking) }}">WhatsApp</x-primary-button>
                                    @if (in_array($booking->status, \App\Models\Booking::ACCEPT_STATUSES, true))
                                        <form method="POST" action="{{ route('admin.bookings.accept', $booking) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-secondary-button type="submit">User Jadi Datang</x-secondary-button>
                                        </form>
                                    @endif
                                    @if (in_array($booking->status, \App\Models\Booking::CHECK_IN_STATUSES, true))
                                        <form method="POST" action="{{ route('admin.bookings.check-in', $booking) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-secondary-button type="submit">Check-in</x-secondary-button>
                                        </form>
                                    @endif
                                    @if (in_array($booking->status, \App\Models\Booking::COMPLETE_STATUSES, true))
                                        <form method="POST" action="{{ route('admin.bookings.complete', $booking) }}">
                                            @csrf
                                            @method('PATCH')
                                            <x-secondary-button type="submit">Selesaikan</x-secondary-button>
                                        </form>
                                    @endif
                                    @if ($booking->payment?->status !== 'paid')
                                        <form method="POST" action="{{ route('admin.bookings.payment.paid', $booking) }}" class="flex flex-wrap gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <x-select-input name="method" class="min-h-11 w-32">
                                                <option value="cash">Cash</option>
                                                <option value="qris">QRIS</option>
                                                <option value="transfer">Transfer</option>
                                            </x-select-input>
                                            <x-secondary-button type="submit">Tandai Lunas</x-secondary-button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-10 text-center text-gaz-muted">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
