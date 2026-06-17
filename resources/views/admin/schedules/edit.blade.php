@extends('layouts.admin', ['heading' => 'Edit Jadwal'])

@section('content')
<div class="max-w-2xl">
    @include('admin.schedules.form', ['title' => 'Edit Jadwal', 'schedule' => $schedule, 'capsters' => $capsters])
</div>
@endsection
