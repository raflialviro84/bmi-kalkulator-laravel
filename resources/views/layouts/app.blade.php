<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'BMI Kalkulator') }}</title>
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body>
    @yield('content')
    <script src="{{ asset('js/index.js') }}"></script>
</body>
</html>
