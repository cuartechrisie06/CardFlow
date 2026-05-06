<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CardFlow') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="dashboard-body">
    <div class="dashboard-shell">



        <main class="main-content">
            @yield('content')
        </main>

    </div>
</body>
</html>