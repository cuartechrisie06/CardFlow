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

        $currentMode = 'trade';

        if ($userCard->is_for_trade && $userCard->is_for_sale) {
            $currentMode = 'both';
        } elseif ($userCard->is_for_sale) {
            $currentMode = 'sale';
        }
    @endphp

    <main class="dashboard-shell">
        <aside class="dashboard-sidebar">
            <a href="{{ $user->username ? route('profile.show', $user->username) : route('profile.edit') }}" class="sidebar-brand sidebar-profile-link">
                <div class="sidebar-avatar"></div>
                <div>
                    <p>{{ $user->name }}</p>
                    <span>{{ '@' . $username }}</span>
                </div>
            </a>

            <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link is-active">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>
        </aside>

        <section class="dashboard-main">
            <header class="dashboard-header marketplace-header">
                <div>
                    <span class="dashboard-eyebrow">Marketplace</span>
                    <h1>Edit listing</h1>
                    <p>Update your listing mode or price.</p>
                </div>

                <div class="dashboard-actions">
                    <a href="{{ route('marketplace.index', ['filter' => 'my_listings']) }}" class="dashboard-search-submit">
                        Back to My listings
                    </a>
                </div>
            </header>

            <section class="dashboard-card marketplace-create-card">
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

                <div class="listing-edit-preview">
                    <strong>{{ $userCard->card->title ?? $userCard->card->name ?? 'Untitled Card' }}</strong>
                    <span>{{ $userCard->card->artist ?? 'Unknown Group' }}</span>
                    <span>{{ $userCard->condition ?? 'No condition' }}</span>
                </div>

                <form method="POST" action="{{ route('marketplace.update', $listing) }}" class="marketplace-create-form">
                    @csrf
                    @method('PUT')

                    <label class="form-field">
                        <span>Listing mode</span>

                        <select name="listing_mode" required>
                            <option value="trade" @selected(old('listing_mode', $currentMode) === 'trade')>Trade only</option>
                            <option value="sale" @selected(old('listing_mode', $currentMode) === 'sale')>Sale only</option>
                            <option value="both" @selected(old('listing_mode', $currentMode) === 'both')>Trade and sale</option>
                        </select>
                    </label>

                    <label class="form-field">
                        <span>Listing price</span>

                        <input
                            type="number"
                            name="listing_price"
                            value="{{ old('listing_price', $userCard->listing_price) }}"
                            min="0"
                            step="0.01"
                            placeholder="Required for sale listings"
                        >
                    </label>

                    <button type="submit" class="dashboard-add-card">
                        Save changes
                    </button>
                </form>

                <form method="POST" action="{{ route('marketplace.destroy', $listing) }}" class="delete-listing-form">
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="delete-listing-button"
                        onclick="return confirm('Remove this listing from the marketplace? Your card will stay in your collection.')"
                    >
                        Delete posting
                    </button>
                </form>
            </section>
        </section>
    </main>
</body>
</html>