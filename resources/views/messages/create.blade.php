<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Marketplace</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <a href="{{ $user->username ? route('profile.show', $user->username) : route('profile.edit') }}"
                    class="sidebar-brand sidebar-profile-link">

                    <div class="sidebar-avatar"></div>

                    <div>
                        <p>{{ $user->name }}</p>
                    <span>{{ '@' . $username }}</span>
                 </div>
                </a>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link is-active">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>
        </aside>

        <section class="dashboard-main">
            <header class="dashboard-header marketplace-header">
                <div>
                    <span class="dashboard-eyebrow">Messages</span>
                    <h1>New message</h1>
                    <p>Start a conversation with another collector.</p>
                </div>

                <div class="dashboard-actions">
                    <a href="{{ route('messages.index') }}" class="dashboard-search-submit">
                        Back to Messages
                    </a>
                </div>
            </header>

            <section class="dashboard-card message-create-card">
                @if ($errors->any())
                    <div class="form-error-box">
                        <strong>Please check the form:</strong>

                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($users->isEmpty())
                    <div class="empty-state">
                        No other users are available to message yet.
                    </div>
                @else
                    <form method="POST" action="{{ route('messages.start') }}" class="message-create-form">
                        @csrf

                        <label class="form-field">
                            <span>Recipient</span>

                            <select name="recipient_id" required>
                                <option value="">Choose a user</option>

                                @foreach ($users as $recipient)
                                    <option value="{{ $recipient->id }}" @selected(old('recipient_id') == $recipient->id)>
                                        {{ $recipient->name }}
                                        {{ $recipient->username ? '@' . $recipient->username : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-field">
                            <span>Message</span>

                            <textarea
                                name="body"
                                rows="6"
                                required
                                placeholder="Write your first message..."
                            >{{ old('body') }}</textarea>
                        </label>

                        <button type="submit" class="dashboard-add-card">
                            Send message
                        </button>
                    </form>
                @endif
            </section>
        </section>
    </main>
</body>
</html>