@extends('layouts.admin')
@section('title', 'Edit Penempatan PKL')
@section('page-title', 'Edit Penempatan PKL')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.penempatan.update', $penempatan) }}">
            @csrf
            @method('PUT')
            @include('admin.penempatan._form', ['penempatan' => $penempatan])
            <button class="btn btn-primary">Simpan</button>
            <a href="{{ route('admin.penempatan.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection