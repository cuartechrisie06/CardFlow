<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | {{ $userCard->card->title }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $viewer = auth()->user();
            $photoUrl = $userCard->photo_path ? \Illuminate\Support\Facades\Storage::url($userCard->photo_path) : null;
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $viewer->name }}</p>
                        <span>{{ '@'.($viewer->username ?: 'collector') }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link is-active">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $viewer])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Marketplace Card</p>
                        <h1>{{ $userCard->card->title }}</h1>
                        <p class="dashboard-intro">Listed by {{ $owner->name }} for marketplace browsing.</p>
                    </div>

                    <div class="dashboard-actions">
                        <a href="{{ route('marketplace.user', $owner) }}" class="dashboard-add-card dashboard-add-card-secondary">View {{ $owner->name }}'s collection</a>
                        @if ($viewer->id !== $owner->id)
                            <form action="{{ route('messages.listings.store', $listing) }}" method="POST" class="dashboard-inline-form">
                                @csrf
                                <button type="submit" class="dashboard-add-card">
                                    {{ $userCard->is_for_sale ? 'Message seller' : 'Message trader' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </header>

                <section class="dashboard-card card-detail-shell">
                    <div class="card-detail-media {{ $photoUrl ? 'collection-thumb-photo' : $userCard->card->thumbnail_style }}" @if ($photoUrl) style="background-image: url('{{ $photoUrl }}');" @endif></div>
                    <div class="card-detail-copy">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Card details</p>
                                <h2>{{ $userCard->card->title }}</h2>
                            </div>
                            <span class="mini-chip">{{ strtoupper($userCard->card->rarity) }}</span>
                        </div>
                        <div class="card-detail-grid">
                            <div><span class="summary-label">Artist</span><strong>{{ $userCard->card->artist }}</strong></div>
                            <div><span class="summary-label">Album</span><strong>{{ $userCard->card->album ?: 'Standalone' }}</strong></div>
                            <div><span class="summary-label">Edition</span><strong>{{ $userCard->card->edition ?: 'Standard' }}</strong></div>
                            <div><span class="summary-label">Condition</span><strong>{{ $userCard->condition }}</strong></div>
                            <div><span class="summary-label">Visibility</span><strong>{{ $userCard->is_public ? 'Public' : 'Listed only' }}</strong></div>
                            <div><span class="summary-label">Listing</span><strong>{{ $userCard->is_for_trade ? 'Trade' : ($userCard->is_for_sale ? 'Sale' : 'Showcase') }}</strong></div>
                        </div>
                        @if ($userCard->listing_price)
                            <p class="dashboard-intro">Listing price: PHP {{ number_format((float) $userCard->listing_price, 0) }}</p>
                        @endif
                        @if ($userCard->notes)
                            <div class="dashboard-card card-note-shell">
                                <p class="mini-label">Owner note</p>
                                <p>{{ $userCard->notes }}</p>
                            </div>
                        @endif
                    </div>
                </section>
            </section>
        </main>
    </body>
</html>
