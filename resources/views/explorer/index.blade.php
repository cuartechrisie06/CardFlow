<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Explorer</title>
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
                        <p class="dashboard-kicker">Artist / Card Explorer</p>
                        <h1>Artist / card explorer</h1>
                    </div>

                    <div class="dashboard-actions">
                        <form method="GET" action="{{ route('explorer.index') }}" class="dashboard-actions">
                            <label class="dashboard-search">
                                <span class="sr-only">Search explorer</span>
                                <input type="search" name="q" value="{{ $search }}" placeholder="Search artists, idols, eras, cards...">
                            </label>
                            <input type="hidden" name="filter" value="{{ $filters['active'] }}">
                            <button type="submit" class="dashboard-search-submit">Search</button>
                        </form>

                        <form method="POST" action="{{ $saveViewAction }}">
                            @csrf
                            <input type="hidden" name="q" value="{{ $search }}">
                            <input type="hidden" name="filter" value="{{ $filters['active'] }}">
                            <button type="submit" class="dashboard-add-card">Save view</button>
                        </form>
                    </div>
                </header>

                @if (session('status'))
                    <div class="auth-status">{{ session('status') }}</div>
                @endif

                <section class="dashboard-card explorer-shell">
                    <section class="stats-grid explorer-stats">
                        <article class="stat-card">
                            <span class="stat-label">Groups indexed</span>
                            <div class="stat-value">{{ $metrics['groups_indexed'] }}</div>
                            <div class="stat-note">distinct artist or group catalogs</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Total cards</span>
                            <div class="stat-value">{{ number_format($metrics['total_cards']) }}</div>
                            <div class="stat-note">photocard records in scope</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Avg. trade value</span>
                            <div class="stat-value">{{ $formatMoney($metrics['average_trade_value']) }}</div>
                            <div class="stat-note">active marketplace listing average</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Hottest trend</span>
                            <div class="stat-value">{{ $metrics['hottest_artist'] }}</div>
                            <div class="stat-note">highest wishlist demand</div>
                        </article>
                    </section>

                    <section class="dashboard-card explorer-filter-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Explorer filters</p>
                                <h2>Browse by category</h2>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('explorer.index') }}" class="collection-filters explorer-filter-list">
                            @if ($search !== '')
                                <input type="hidden" name="q" value="{{ $search }}">
                            @endif
                            @foreach ($filters['items'] as $value => $label)
                                <button type="submit" name="filter" value="{{ $value }}" class="collection-filter {{ $filters['active'] === $value ? 'is-active' : '' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </form>
                    </section>

                    <section class="dashboard-card explorer-feature-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Featured catalogs</p>
                                <h2>Popular artists in the explorer</h2>
                            </div>
                            <span class="mini-chip">Live marketplace and wishlist data</span>
                        </div>

                        <div class="explorer-feature-grid">
                            @forelse ($featuredCatalogs as $catalog)
                                <a href="{{ route('explorer.catalogs.show', $catalog['slug']) }}" class="explorer-artist-card explorer-card-link">
                                    <div class="marketplace-thumb {{ $catalog['style'] }}">
                                        <div class="marketplace-tags">
                                            <span class="collection-pill">{{ $filters['items'][$filters['active']] ?? 'Catalog' }}</span>
                                        </div>
                                    </div>
                                    <div class="explorer-artist-copy">
                                        <h3>{{ $catalog['artist'] }}</h3>
                                        <p>{{ $catalog['idol_count'] }} cards · {{ $catalog['era_count'] }} eras</p>
                                        <p>{{ $catalog['blurb'] }}</p>
                                        <div class="explorer-artist-footer">
                                            <span class="mini-chip">Avg. {{ $formatMoney($catalog['average_value']) }}</span>
                                            <span>{{ $catalog['wishlist_count'] }} active wishlists</span>
                                        </div>
                                    </div>
                                </a>
                            @empty
                                <div class="collection-empty">No artists match this explorer view yet.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="explorer-bottom-grid">
                        <article class="dashboard-card explorer-snapshot-card">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Discovery snapshot</p>
                                    <h2>Top categories by demand</h2>
                                </div>
                                <span class="mini-chip">Wishlist + listings + trades</span>
                            </div>

                            <div class="activity-chart-panel">
                                <div class="bar-chart explorer-bar-chart" aria-hidden="true">
                                    @forelse ($categoryBars as $bar)
                                        <div class="bar-chart-item">
                                            <i style="height: {{ $bar['height'] }}%"></i>
                                            <span>{{ $bar['label'] }}</span>
                                        </div>
                                    @empty
                                        <div class="empty-state">No category data available yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </article>

                        <article class="dashboard-card explorer-quick-picks">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Quick picks</p>
                                    <h2>Suggested jumps</h2>
                                </div>
                            </div>

                            <div class="explorer-pick-list">
                                @forelse ($quickPicks as $pick)
                                    <article class="explorer-pick-item">
                                        <span class="summary-label">{{ $pick['label'] }}</span>
                                        <strong>{{ $pick['title'] }}</strong>
                                        <p>{{ $pick['subtitle'] }}</p>
                                        <span>{{ $pick['meta'] }}</span>
                                    </article>
                                @empty
                                    <div class="collection-empty">No quick picks available for this filter yet.</div>
                                @endforelse
                            </div>
                        </article>
                    </section>
                </section>
            </section>
        </main>
    </body>
</html>
