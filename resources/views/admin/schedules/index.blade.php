@extends('layouts.admin', ['heading' => 'Jadwal Capster'])

@section('content')
<div class="grid gap-6 xl:grid-cols-[420px_1fr]">
    @include('admin.schedules.form', ['title' => 'Atur Jadwal'])
    <section class="rounded-2xl border border-gaz-border bg-gaz-card p-5">
        <h2 class="text-2xl font-black">List Jadwal Capster</h2>
        <div class="mt-5 grid gap-3">
            @foreach ([['Rudi', '31 Mei 2026', '08:00', '18:00', 'Tersedia'], ['Dika', '31 Mei 2026', '10:00', '20:00', 'Tersedia'], ['Bayu', '1 Juni 2026', '12:00', '20:00', 'Tidak Tersedia']] as [$name, $date, $start, $end, $status])
                <article class="rounded-2xl border border-gaz-border bg-black/25 p-4"><div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-black">{{ $name }}</p><p class="text-sm text-gaz-muted">{{ $date }} · {{ $start }}-{{ $end }}</p></div><span class="text-sm font-bold text-gaz-gold">{{ $status }}</span></div></article>
            @endforeach
        </div>
    </section>
</div>
@endsection
