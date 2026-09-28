@extends('layouts.admin')
@section('title', 'Tambah Jurnal')
@section('page-title', 'Tambah Jurnal')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('siswa.jurnal.store') }}">
            @csrf
            @include('siswa.jurnal._form')
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('siswa.jurnal.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection