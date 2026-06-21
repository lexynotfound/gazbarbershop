@props(['status'])

@php
    $tone = match ($status) {
        'ACCEPTED', 'CHECKED_IN', 'IN_PROGRESS', 'PAID', 'COMPLETED', 'REVIEWED' => 'border-green-500/30 bg-green-500/10 text-green-300',
        'AUTO_CANCELLED', 'LATE_CANCELLED', 'CANCELLED', 'REJECTED' => 'border-red-500/30 bg-red-500/10 text-red-300',
        'WAITING_CUSTOMER_CONFIRMATION', 'WAITING_PAYMENT' => 'border-yellow-500/30 bg-yellow-500/10 text-yellow-300',
        default => 'border-gaz-gold/30 bg-gaz-gold/10 text-gaz-gold',
    };

    $label = match ($status) {
        'PENDING' => 'Menunggu Diproses',
        'WAITING_CUSTOMER_CONFIRMATION' => 'Menunggu Balasan WA',
        'WAITING_PAYMENT' => 'Menunggu Pembayaran',
        'ACCEPTED' => 'Jadi Datang',
        'CHECKED_IN' => 'Sudah Check-in',
        'IN_PROGRESS' => 'Sedang Dilayani',
        'PAID' => 'Menunggu Selesai',
        'COMPLETED' => 'Selesai',
        'REVIEWED' => 'Sudah Direview',
        'AUTO_CANCELLED' => 'Batal: Tidak Ada Balasan',
        'LATE_CANCELLED' => 'Batal: Terlambat',
        'CANCELLED' => 'Dibatalkan',
        'REJECTED' => 'Ditolak/Batal',
        default => str($status)->replace('_', ' ')->title(),
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex rounded-full border px-3 py-1 text-xs font-bold '.$tone]) }}>{{ $label }}</span>
