<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Edit Profile</title>
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
                        <p class="dashboard-kicker">Profile</p>
                        <h1>Edit profile</h1>
                    </div>
                </header>

                <section class="dashboard-card collection-card-shell">
                    @if (session('status'))
                        <div class="auth-status">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" class="card-create-form">
                        @csrf
                        @method('PUT')
                        <div class="card-form-grid">
                            <label class="field-group">
                                <span>Full Name</span>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}">
                                @error('name') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Username</span>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}">
                                @error('username') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group field-group-wide">
                                <span>Email Address</span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}">
                                @error('email') <small class="field-error">{{ $message }}</small> @enderror
                            </label>
                        </div>

                        <div class="create-form-actions">
                            <button type="submit" class="dashboard-add-card">Save changes</button>
                        </div>
                    </form>
                </section>
            </section>
        </main>
    </body>
</html>
