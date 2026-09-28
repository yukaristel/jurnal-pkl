@extends('layouts.admin')
@section('title', 'Edit Jurnal')
@section('page-title', 'Edit Jurnal')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('siswa.jurnal.update', $jurnal) }}">
            @csrf
            @method('PUT')
            @include('siswa.jurnal._form', ['jurnal' => $jurnal])
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('siswa.jurnal.show', $jurnal) }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection