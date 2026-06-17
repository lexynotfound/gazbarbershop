@extends('layouts.admin', ['heading' => 'Konfirmasi WhatsApp'])

@section('content')
<div class="grid gap-6">
    <x-breadcrumbs :items="[
        ['label' => 'Booking', 'url' => route('admin.bookings.index')],
        ['label' => $booking->booking_code, 'url' => route('admin.bookings.show', $booking)],
        ['label' => 'WhatsApp'],
    ]" />

    <section class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
            <h1 class="text-3xl font-black">Preview Pesan WhatsApp</h1>
            <pre class="mt-6 whitespace-pre-wrap rounded-2xl border border-gaz-border bg-black/35 p-5 text-sm leading-7 text-gaz-muted">{{ $message }}</pre>
            <div class="mt-6 flex flex-wrap gap-3">
                <x-secondary-button href="{{ route('admin.bookings.show', $booking) }}">Kembali</x-secondary-button>
                <x-primary-button href="{{ $whatsappUrl }}" target="_blank">Buka WhatsApp</x-primary-button>

                <form method="POST" action="{{ route('admin.bookings.confirm', $booking) }}">
                    @csrf @method('PATCH')
                    <x-secondary-button type="submit">Tandai Sudah Dikonfirmasi</x-secondary-button>
                </form>

                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}">
                    @csrf @method('PATCH')
                    <x-danger-button type="submit">Tolak Booking</x-danger-button>
                </form>
            </div>
        </div>
        <aside class="h-fit rounded-2xl border border-yellow-500/30 bg-yellow-500/10 p-5 text-yellow-100">
            <p class="font-black">Menunggu respons user selama 15 menit.</p>
            <p class="mt-2 text-sm">Jika tidak ada respons, booking akan dibatalkan otomatis oleh sistem.</p>
            <div class="mt-4 border-t border-yellow-500/20 pt-4 text-sm">
                <p><span class="font-bold">Pelanggan:</span> {{ $booking->user->name }}</p>
                <p class="mt-1"><span class="font-bold">No. HP:</span> +{{ $booking->user->phone }}</p>
                <p class="mt-1"><span class="font-bold">Kode:</span> {{ $booking->booking_code }}</p>
            </div>
        </aside>
    </section>
</div>
@endsection
