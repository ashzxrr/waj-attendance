<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>WAJ Attendance</title>
    @include('partials.base-url-meta')
    <script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-slate-900">
    <div id="app"></div>
</body>
</html>
