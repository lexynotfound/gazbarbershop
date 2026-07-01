@extends('layouts.admin', ['heading' => 'Detail Booking'])

@section('content')
@php
    $services = $booking->items->map(fn ($item) => $item->service->name)->join(' + ');
@endphp

<div class="grid gap-6">
    <x-breadcrumbs :items="[
        ['label' => 'Booking', 'url' => route('admin.bookings.index')],
        ['label' => $booking->booking_code],
    ]" />

    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <x-booking-status-badge :status="$booking->status" />
                <h1 class="mt-4 text-3xl font-black">{{ $booking->booking_code }}</h1>
                <p class="mt-2 text-sm text-gaz-muted">{{ $booking->booking_start->translatedFormat('d F Y H:i') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-secondary-button href="{{ route('admin.bookings.index') }}">Kembali</x-secondary-button>
                <x-primary-button href="{{ route('admin.bookings.whatsapp', $booking) }}">WhatsApp</x-primary-button>
                @if (in_array($booking->status, \App\Models\Booking::RESCHEDULABLE_STATUSES, true))
                    <x-secondary-button href="{{ route('admin.bookings.reschedule.form', $booking) }}">Reschedule</x-secondary-button>
                @endif
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
                        <x-primary-button type="submit">Selesaikan Booking</x-primary-button>
                    </form>
                @endif
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Pelanggan', $booking->user->name], ['Layanan', $services], ['Capster', $booking->capster->name], ['Total', 'Rp'.number_format($booking->grand_total, 0, ',', '.')], ['Check-in', $booking->checked_in_at?->translatedFormat('d F Y H:i') ?? '-'], ['Selesai', $booking->completed_at?->translatedFormat('d F Y H:i') ?? '-']] as [$label, $value])
                <div class="rounded-xl bg-black/25 p-4"><p class="text-sm text-gaz-muted">{{ $label }}</p><p class="mt-1 font-black">{{ $value }}</p></div>
            @endforeach
        </div>

        <div class="mt-6 rounded-2xl border border-gaz-border bg-black/25 p-5">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm text-gaz-muted">Pembayaran</p>
                    @if ($booking->payment?->status === 'paid')
                        <p class="mt-1 text-xl font-black text-emerald-200">Lunas - {{ str($booking->payment->method)->replace('_', ' ')->title() }}</p>
                        <p class="mt-1 text-sm text-gaz-muted">{{ $booking->payment->paid_at?->translatedFormat('d F Y H:i') ?? '-' }}</p>
                    @else
                        <p class="mt-1 text-xl font-black text-amber-200">Belum Dibayar</p>
                        <p class="mt-1 text-sm text-gaz-muted">Nominal Rp{{ number_format($booking->payment?->amount ?? $booking->grand_total, 0, ',', '.') }}</p>
                    @endif
                </div>

                @if ($booking->payment?->status !== 'paid')
                    <form method="POST" action="{{ route('admin.bookings.payment.paid', $booking) }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        @csrf
                        @method('PATCH')
                        <label class="grid gap-2 text-sm font-bold text-gaz-muted">
                            Metode Bayar
                            <x-select-input name="method" class="w-44">
                                <option value="cash">Cash</option>
                                <option value="qris">QRIS</option>
                                <option value="transfer">Transfer</option>
                            </x-select-input>
                        </label>
                        <x-primary-button type="submit">Tandai Lunas</x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection
