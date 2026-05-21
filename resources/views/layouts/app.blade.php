<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Expires" content="0">

        @php
            $layoutMode = trim($__env->yieldContent('layout_mode', 'dashboard'));
        @endphp

        <title>@yield('title', config('app.name', 'CardFlow'))</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

        @stack('head')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="@yield('body_class', 'dashboard-body')">
        @php
            $layoutUser = auth()->user();
            $layoutUsername = $layoutUser?->username ?: 'collector';
        @endphp

        @if ($layoutMode === 'shellless')
            @if ($errors->any())
                <div class="auth-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        @else
            <main class="dashboard-shell">
                @auth
                    @include('partials._sidebar', [
                        'user' => $layoutUser,
                        'username' => $layoutUsername,
                    ])
                @endauth

                <section class="dashboard-main">
                    @auth
                        @hasSection('topbar')
                            @yield('topbar')
                        @else
                            @include('partials._topbar')
                        @endif
                    @endauth

                    @php
                        $announcement = \App\Models\Setting::get('announcement_message');
                        $isAnnouncementActive = \App\Models\Setting::get('announcement_active') === '1';
                        $isAnnouncementDismissible = \App\Models\Setting::get('announcement_dismissible') === '1';
                    @endphp

                    @if($isAnnouncementActive && $announcement)
                        <div class="announcement-banner" id="announcement-banner">
                            <p>{{ $announcement }}</p>
                            @if($isAnnouncementDismissible)
                                <button type="button" data-announcement-dismiss>x</button>
                            @endif
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="auth-errors">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </section>
            </main>
        @endif

        @if (session('status'))
            <div class="toast-notification" id="toast-success">
                ✓ {{ session('status') }}
            </div>
        @endif

        @stack('modals')
        @stack('scripts')
    </body>
</html>
