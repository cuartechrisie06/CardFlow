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
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link is-active">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Marketplace</p>
                        <h1>Marketplace</h1>
                    </div>

                    <form method="GET" action="{{ route('marketplace.index') }}" class="dashboard-actions">
                        <label class="dashboard-search">
                            <span class="sr-only">Search marketplace</span>
                            <input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Search marketplace...">
                        </label>
                        <button type="submit" class="dashboard-search-submit">Search</button>
                        <a href="{{ route('collection.create') }}" class="dashboard-add-card">Post listing</a>
                    </form>
                </header>

                <section class="dashboard-card marketplace-shell">
                    <form method="GET" action="{{ route('marketplace.index') }}" class="marketplace-toolbar">
                        @if ($filters['search'] !== '')
                            <input type="hidden" name="q" value="{{ $filters['search'] }}">
                        @endif

                        <div class="collection-filters">
                            @foreach ($filters['items'] as $value => $label)
                                <button type="submit" name="filter" value="{{ $value }}" class="collection-filter {{ $filters['active'] === $value ? 'is-active' : '' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </form>

                    <section class="stats-grid marketplace-stats">
                        <article class="stat-card">
                            <span class="stat-label">Open listings</span>
                            <div class="stat-value">{{ $marketMetrics['open_listings'] }}</div>
                            <div class="stat-note">ready for offers</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Open trades</span>
                            <div class="stat-value">{{ $marketMetrics['open_trades'] }}</div>
                            <div class="stat-note">active conversations</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Sale offers</span>
                            <div class="stat-value">{{ $marketMetrics['sale_offers'] }}</div>
                            <div class="stat-note">priced and ready</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Quick action</span>
                            <div class="stat-value">{{ $marketMetrics['quick_actions'] }}</div>
                            <div class="stat-note">one-tap offer flow</div>
                        </article>
                    </section>

                    <section class="dashboard-card featured-marketplace-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Featured listings</p>
                                <h2>Curated opportunities</h2>
                            </div>
                            <span class="mini-chip">Updated now</span>
                        </div>

                        <div class="marketplace-grid">
                            @forelse ($featuredListings as $item)
                                @php
                                    $card = $item->card;
                                    $ownedCard = $item->userCard;
                                    $listingTags = collect([
                                        $ownedCard->is_for_trade ? 'Trade' : null,
                                        $ownedCard->is_for_sale ? 'Sale' : null,
                                        $ownedCard->is_public ? 'Public' : null,
                                        $card->rarity,
                                    ])->filter()->take(3);
                                    $photoUrl = $ownedCard->photo_path ? \Illuminate\Support\Facades\Storage::url($ownedCard->photo_path) : null;
                                @endphp
                                <article class="marketplace-item">
                                    <a href="{{ route('marketplace.cards.show', $item) }}" class="marketplace-item-link">
                                        <div class="marketplace-thumb {{ $photoUrl ? 'collection-thumb-photo' : $card->thumbnail_style }}" @if ($photoUrl) style="background-image: url('{{ $photoUrl }}');" @endif>
                                            <div class="marketplace-tags">
                                                @foreach ($listingTags as $tag)
                                                    <span class="collection-pill">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="marketplace-meta">
                                            <h3>{{ $card->title }}</h3>
                                            <p>{{ strtoupper($card->artist) }}</p>
                                            <p>{{ $card->album ?: 'Standalone release' }}</p>
                                            <p>Owner: <a href="{{ route('marketplace.user', $item->user) }}" class="marketplace-owner-link">{{ $item->user->name }}</a></p>
                                            <div class="marketplace-meta-footer">
                                                <span>{{ $ownedCard->is_for_trade ? 'Looking for trade' : ($ownedCard->is_for_sale ? 'Direct sale available' : 'Public showcase') }}</span>
                                                <span class="marketplace-link">{{ $ownedCard->listing_price ? 'PHP '.number_format((float) $ownedCard->listing_price, 0) : 'View listing' }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @empty
                                <div class="collection-empty">
                                    No listings match this marketplace filter yet.
                                </div>
                            @endforelse
                        </div>

                        <div class="collection-footer">
                            <p>Showing {{ $featuredListings->count() }} featured listings</p>
                            <div class="collection-pagination">
                                @if ($featuredListings->onFirstPage())
                                    <span class="page-button is-disabled">&lsaquo;</span>
                                @else
                                    <a href="{{ $featuredListings->previousPageUrl() }}" class="page-button">&lsaquo;</a>
                                @endif

                                @foreach ($featuredListings->getUrlRange(1, $featuredListings->lastPage()) as $page => $url)
                                    <a href="{{ $url }}" class="page-button {{ $featuredListings->currentPage() === $page ? 'is-active' : '' }}">{{ $page }}</a>
                                @endforeach

                                @if ($featuredListings->hasMorePages())
                                    <a href="{{ $featuredListings->nextPageUrl() }}" class="page-button">&rsaquo;</a>
                                @else
                                    <span class="page-button is-disabled">&rsaquo;</span>
                                @endif
                            </div>
                        </div>
                    </section>
                </section>
            </section>
        </main>
    </body>
</html>
