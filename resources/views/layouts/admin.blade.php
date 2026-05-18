<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'CardFlow Admin')</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="@yield('body_class', 'dashboard-body')">
        @php
            $layoutUser = auth()->user();
            $layoutUsername = $layoutUser?->username ?: 'admin';
            $moderationCount = $moderationCount
                ?? \App\Models\MarketplaceListing::query()
                    ->where('proof_status', 'pending')
                    ->whereNotNull('proof_photo')
                    ->count();
        @endphp

        <main class="dashboard-shell">
            @include('partials._admin_sidebar', [
                'user' => $layoutUser,
                'username' => $layoutUsername,
                'moderationCount' => $moderationCount,
            ])

            <section class="dashboard-main">
                @if ($errors->any())
                    <div class="auth-errors">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(session('status') || session('success'))
                    <div class="toast-notification" id="toast-success">
                        {{ session('status') ?: session('success') }}
                    </div>
                @endif

                @yield('content')
            </section>
        </main>

        @stack('modals')
        @stack('scripts')
    </body>
</html>
