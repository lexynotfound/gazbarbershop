@extends('layouts.admin', ['heading' => 'Tambah Jadwal'])

@section('content')
@include('admin.schedules.form', ['title' => 'Tambah Jadwal', 'capsters' => $capsters])
@endsection
