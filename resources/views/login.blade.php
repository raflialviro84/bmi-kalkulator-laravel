@extends('layouts.app')

@section('content')
<div class="container" style="max-width:400px;margin:auto;margin-top:60px;">
    <h2 class="mb-4">Login Fithub Account</h2>
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ url('/login') }}" id="loginForm">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <input type="hidden" name="device_uuid" id="device_uuid">
        <input type="hidden" name="device_name" id="device_name">
        <input type="hidden" name="platform" id="platform">
        <input type="hidden" name="app_id" id="app_id">
        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div>
<script>
    // Generate device_uuid jika belum ada
    if (!localStorage.getItem('device_uuid')) {
        localStorage.setItem('device_uuid', crypto.randomUUID());
    }
    document.getElementById('device_uuid').value = localStorage.getItem('device_uuid');
    // Ambil browser dan OS ringkas untuk device_name
    function getDeviceName() {
        var ua = navigator.userAgent;
        var browser = 'Browser';
        if (ua.indexOf('Chrome') > -1 && ua.indexOf('Edge') === -1 && ua.indexOf('OPR') === -1) {
            browser = 'Chrome';
        } else if (ua.indexOf('Safari') > -1 && ua.indexOf('Chrome') === -1) {
            browser = 'Safari';
        } else if (ua.indexOf('Firefox') > -1) {
            browser = 'Firefox';
        } else if (ua.indexOf('Edge') > -1) {
            browser = 'Edge';
        } else if (ua.indexOf('OPR') > -1 || ua.indexOf('Opera') > -1) {
            browser = 'Opera';
        }
        var os = 'Unknown';
        if (ua.indexOf('Win') > -1) os = 'Win';
        else if (ua.indexOf('Mac') > -1) os = 'Mac';
        else if (ua.indexOf('Linux') > -1) os = 'Linux';
        else if (ua.indexOf('Android') > -1) os = 'Android';
        else if (ua.indexOf('like Mac') > -1) os = 'iOS';
        return browser + ' ' + os;
    }
    document.getElementById('device_name').value = getDeviceName();
    document.getElementById('platform').value = 'web';
    document.getElementById('app_id').value = 'bmi-kalkulator';
</script>
@endsection
