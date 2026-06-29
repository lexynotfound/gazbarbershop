@extends('layouts.user')

@section('user-content')
<div class="grid gap-6">
    <div class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <p class="text-sm font-bold text-gaz-gold">Member Area</p>
        <h1 class="mt-2 text-3xl font-black">Dashboard User</h1>
        <p class="mt-3 text-gaz-muted">Pantau booking aktif, riwayat, dan review dari satu tempat.</p>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Booking Aktif" :value="$activeBookingsCount" icon="B" />
        <x-stat-card label="Selesai" :value="$finishedBookingsCount" icon="S" />
        <x-stat-card label="Review" :value="$reviewsCount" icon="R" />
    </div>
    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-6">
        <div>
            <p class="text-sm font-bold text-gaz-gold">Riwayat Komunikasi</p>
            <h2 class="mt-2 text-2xl font-black">Notifikasi Terbaru</h2>
        </div>
        <div class="mt-5 grid gap-3">
            @forelse ($notifications as $notification)
                <article class="rounded-xl border border-gaz-border bg-black/25 p-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="font-black">{{ $notification->data['title'] ?? 'Notifikasi' }}</p>
                            <p class="mt-1 text-sm text-gaz-muted">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="mt-2 text-xs text-gaz-muted">{{ $notification->created_at?->diffForHumans() }}</p>
                        </div>
                        @if ($notification->data['action_url'] ?? null)
                            <x-secondary-button href="{{ $notification->data['action_url'] }}" class="shrink-0">Booking Ulang</x-secondary-button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-gaz-border bg-black/20 p-5 text-sm text-gaz-muted">Belum ada notifikasi.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
