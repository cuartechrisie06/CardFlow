<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Account Settings</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $username = $user->username ?: 'collector';
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $user->name }}</p>
                        <span>{{ '@'.$username }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header">
                    <div>
                        <p class="dashboard-kicker">Settings</p>
                        <h1>Account settings</h1>
                        <p class="dashboard-intro">Review your account details and current CardFlow sign-in information.</p>
                    </div>
                </header>

                <section class="stats-grid">
                    <article class="stat-card">
                        <span class="stat-label">Account email</span>
                        <div class="stat-value">{{ $user->email }}</div>
                        <div class="stat-note">used to sign in</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Username</span>
                        <div class="stat-value">{{ '@'.$username }}</div>
                        <div class="stat-note">visible across CardFlow</div>
                    </article>
                </section>
            </section>
        </main>
    </body>
</html>
