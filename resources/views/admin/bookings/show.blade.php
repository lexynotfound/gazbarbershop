@extends('layouts.admin', ['heading' => 'Detail Booking'])

@section('content')
<section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
    <x-booking-status-badge status="ACCEPTED" />
    <h1 class="mt-4 text-3xl font-black">GAZ-260531-001</h1>
    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Pelanggan', 'Rizky Pratama'], ['Layanan', 'Cukur + Cuci'], ['Capster', 'Rudi'], ['Total', 'Rp150.000']] as [$label, $value])
            <div class="rounded-xl bg-black/25 p-4"><p class="text-sm text-gaz-muted">{{ $label }}</p><p class="mt-1 font-black">{{ $value }}</p></div>
        @endforeach
    </div>
</section>
@endsection
