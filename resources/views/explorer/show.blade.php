<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | {{ $catalog['artist'] }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
            $formatMoney = fn (float|int $value) => 'PHP '.number_format((float) $value, 0);
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
                    <a href="{{ route('explorer.index') }}" class="sidebar-link is-active">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Catalog detail</p>
                        <h1>{{ $catalog['artist'] }}</h1>
                        <p class="dashboard-intro">Live catalog detail built from existing cards, wishlists, and marketplace listings.</p>
                    </div>

                    <div class="dashboard-actions">
                        <a href="{{ route('explorer.index', array_filter(['q' => $search ?: null, 'filter' => $filters['active']])) }}" class="dashboard-add-card dashboard-add-card-secondary">Back to explorer</a>
                    </div>
                </header>

                <section class="stats-grid explorer-stats">
                    <article class="stat-card">
                        <span class="stat-label">Cards</span>
                        <div class="stat-value">{{ $catalog['card_count'] }}</div>
                        <div class="stat-note">photocards in this catalog</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Idols</span>
                        <div class="stat-value">{{ $catalog['idol_count'] }}</div>
                        <div class="stat-note">distinct card names</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Eras</span>
                        <div class="stat-value">{{ $catalog['era_count'] }}</div>
                        <div class="stat-note">albums or editions</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Avg. value</span>
                        <div class="stat-value">{{ $formatMoney($catalog['average_value']) }}</div>
                        <div class="stat-note">{{ $catalog['marketplace_listings'] }} active listings</div>
                    </article>
                </section>

                <section class="explorer-bottom-grid">
                    <article class="dashboard-card explorer-snapshot-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Catalog snapshot</p>
                                <h2>Active demand</h2>
                            </div>
                        </div>

                        <div class="catalog-detail-metrics">
                            <div class="catalog-detail-metric">
                                <span class="summary-label">Active wishlists</span>
                                <strong>{{ $catalog['active_wishlists'] }}</strong>
                            </div>
                            <div class="catalog-detail-metric">
                                <span class="summary-label">Marketplace listings</span>
                                <strong>{{ $catalog['marketplace_listings'] }}</strong>
                            </div>
                        </div>

                        <div class="catalog-era-list">
                            @foreach ($eras as $era)
                                <span class="collection-pill">{{ $era }}</span>
                            @endforeach
                        </div>
                    </article>

                    <article class="dashboard-card explorer-quick-picks">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Catalog search</p>
                                <h2>Refine this catalog</h2>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('explorer.catalogs.show', \Illuminate\Support\Str::slug($catalog['artist'])) }}" class="collection-filters explorer-filter-list">
                            <label class="dashboard-search">
                                <span class="sr-only">Search catalog cards</span>
                                <input type="search" name="q" value="{{ $search }}" placeholder="Search cards, idols, eras...">
                            </label>
                            <button type="submit" class="dashboard-search-submit">Search</button>
                        </form>
                    </article>
                </section>

                <section class="dashboard-card explorer-feature-card">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Catalog cards</p>
                            <h2>{{ $cards->total() }} cards in view</h2>
                        </div>
                    </div>

                    <div class="collection-grid">
                        @forelse ($cards as $card)
                            <article class="collection-item-card">
                                <div class="marketplace-thumb {{ $card->thumbnail_style ?: 'market-thumb-one' }}">
                                    <div class="marketplace-tags">
                                        <span class="collection-pill">{{ $card->rarity }}</span>
                                        @if ($card->active_listings_count > 0)
                                            <span class="collection-pill">{{ $card->active_listings_count }} listings</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="collection-item-copy">
                                    <h3>{{ $card->title }}</h3>
                                    <p>{{ $card->artist }}</p>
                                    <p>{{ $card->album ?: ($card->edition ?: 'Standalone') }}</p>
                                    <div class="collection-meta-row">
                                        <span class="collection-pill">{{ $card->wishlist_items_count }} wishlists</span>
                                        <strong>{{ $formatMoney($card->market_value) }}</strong>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="collection-empty">No catalog cards match this filter yet.</div>
                        @endforelse
                    </div>

                    {{ $cards->links() }}
                </section>
            </section>
        </main>
    </body>
</html>
