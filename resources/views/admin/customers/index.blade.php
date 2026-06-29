@extends('layouts.admin', ['heading' => 'Pelanggan'])

@section('content')
<section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-black">Pelanggan</h1>
            <p class="mt-2 text-gaz-muted">Data pelanggan yang terdaftar di sistem.</p>
        </div>
        <span class="inline-flex rounded-full border border-gaz-gold/30 bg-gaz-gold/10 px-3 py-1 text-xs font-bold text-gaz-gold">{{ $customers->count() }} Pelanggan</span>
    </div>

    @if (session('status'))
        <div class="mt-5 rounded-xl border border-gaz-gold/30 bg-gaz-gold/10 p-4 text-sm font-bold text-gaz-gold">{{ session('status') }}</div>
    @endif

    <div class="mt-6 overflow-x-auto">
        <table class="w-full min-w-[980px] text-left text-sm">
            <thead class="text-gaz-muted">
                <tr>
                    @foreach (['Nama', 'Email', 'No. HP', 'Booking Selesai', 'Segmen CRM', 'Bergabung', 'Aksi'] as $head)
                        <th class="border-b border-gaz-border px-4 py-3">{{ $head }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gaz-border">
                @forelse ($customers as $customer)
                    <tr class="hover:bg-white/[0.03]">
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="grid size-11 shrink-0 place-items-center rounded-xl bg-gaz-gold font-black text-black">{{ str($customer->name)->substr(0, 1) }}</div>
                                <span class="font-black">{{ $customer->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4">{{ $customer->email }}</td>
                        <td class="px-4 py-4">{{ $customer->phone ?: '-' }}</td>
                        <td class="px-4 py-4">{{ $customer->completed_bookings_count }} booking</td>
                        <td class="px-4 py-4">
                            <span class="inline-flex rounded-full border px-3 py-1 text-xs font-bold {{ $customer->crm_segment === 'Loyal' ? 'border-gaz-gold/30 bg-gaz-gold/10 text-gaz-gold' : ($customer->crm_segment === 'Repeat' ? 'border-blue-400/30 bg-blue-400/10 text-blue-200' : 'border-white/15 bg-white/5 text-white') }}">{{ $customer->crm_segment }}</span>
                        </td>
                        <td class="px-4 py-4">{{ $customer->created_at?->translatedFormat('d F Y') ?: '-' }}</td>
                        <td class="px-4 py-4">
                            @if ($customer->crm_segment === 'Loyal' && $customer->phone)
                                <x-primary-button href="{{ route('admin.customers.promo-whatsapp', $customer) }}" target="_blank">Promo Personal</x-primary-button>
                            @elseif ($customer->crm_segment === 'Loyal')
                                <span class="text-xs text-gaz-muted">No. HP belum ada</span>
                            @else
                                <span class="text-xs text-gaz-muted">Belum memenuhi syarat</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-gaz-muted">Belum ada pelanggan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
