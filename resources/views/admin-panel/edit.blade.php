<?php /** role edit view */ ?>
@extends('layouts.app')

@section('content')
<div class="container" style="max-width:600px;margin:auto;margin-top:40px;">
    <h2 class="mb-4">Edit Role</h2>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-3">
        <form method="POST" action="{{ route('roles.update', $role) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name (kode)</label>
                <input name="name" class="form-control" value="{{ $role->name }}" required />
            </div>
            <div class="mb-3">
                <label class="form-label">Display Name</label>
                <input name="display_name" class="form-control" value="{{ $role->display_name }}" />
            </div>
            <div style="display:flex;gap:10px;">
                <button class="btn btn-primary">Simpan</button>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
