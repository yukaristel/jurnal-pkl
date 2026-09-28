@extends('layouts.admin')
@section('title', 'Tambah Penempatan PKL')
@section('page-title', 'Tambah Penempatan PKL')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.penempatan.store') }}">
            @csrf
            @include('admin.penempatan._form', ['penempatan' => null])
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.penempatan.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection