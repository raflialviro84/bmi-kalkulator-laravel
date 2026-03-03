@extends('layouts.app')

@section('content')
<div class="container" style="max-width:500px;margin:auto;margin-top:60px;">
    <h2 class="mb-4">Ubah Password</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card p-4">
        <form method="POST" action="{{ route('profile.password.update') }}">
            @csrf

            <div class="mb-3">
                <label for="current_password" class="form-label">Password Saat Ini</label>
                <input id="current_password" name="current_password" type="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input id="new_password" name="new_password" type="password" class="form-control" required minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}" title="Minimal 8 karakter, 1 huruf kecil, 1 huruf kapital, dan 1 simbol.">
                <small class="form-text text-muted">Minimal 8 karakter, 1 huruf kecil, 1 huruf kapital, dan 1 simbol.</small>
            </div>

            <div class="mb-3">
                <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                <input id="new_password_confirmation" name="new_password_confirmation" type="password" class="form-control" required minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}" title="Samakan dengan password baru">
            </div>

            <div style="display:flex;gap:10px;">
                <a href="{{ route('profile') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Ubah Password</button>
            </div>
        </form>
    </div>
</div>
@endsection
