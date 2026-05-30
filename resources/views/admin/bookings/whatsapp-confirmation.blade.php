@extends('layouts.admin', ['heading' => 'Konfirmasi WhatsApp'])

@section('content')
<section class="grid gap-6 lg:grid-cols-[1fr_360px]">
    <div class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <h1 class="text-3xl font-black">Preview Pesan WhatsApp</h1>
        <pre class="mt-6 whitespace-pre-wrap rounded-2xl border border-gaz-border bg-black/35 p-5 text-sm leading-7 text-gaz-muted">Halo Rizky Pratama, booking Anda di GAZ Barbershop sudah kami terima.

Detail Booking:
- Kode Booking: GAZ-260531-001
- Layanan: Cukur Rambut + Cuci
- Capster: Rudi
- Jadwal: 31 Mei 2026, 10:00
- Total Harga: Rp150.000

Mohon konfirmasi apakah Anda jadi datang.
Balas: Jadi / Tidak Jadi

Terima kasih.</pre>
        <div class="mt-6 flex flex-wrap gap-3"><x-primary-button>Buka WhatsApp</x-primary-button><x-secondary-button>Tandai Sudah Dikonfirmasi</x-secondary-button><x-danger-button>Batal</x-danger-button></div>
    </div>
    <aside class="h-fit rounded-2xl border border-yellow-500/30 bg-yellow-500/10 p-5 text-yellow-100">
        <p class="font-black">Menunggu respons user selama 15 menit.</p>
        <p class="mt-2 text-sm">Jika tidak ada respons, booking akan dibatalkan otomatis oleh sistem.</p>
    </aside>
</section>
@endsection
