@extends('layouts.app')

@section('title', 'CardFlow | Dashboard')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header">
                    <div>
                        <p class="dashboard-kicker">Dashboard</p>
                        <h1>Welcome back to your collection</h1>
                        <p class="dashboard-intro">
                            Real-time collection totals, trade movement, wishlist matches, and activity from your own CardFlow account.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('dashboard') }}" class="dashboard-actions">
                        <label class="dashboard-search">
                            <span class="sr-only">Search cards</span>
                            <input type="search" name="q" value="{{ $searchQuery ?? '' }}" placeholder="Search cards, users, sets...">
                        </label>
                        <button type="submit" class="dashboard-search-submit">Search</button>
                        <a href="{{ route('collection.create') }}" class="dashboard-add-card">+ Add card</a>
                    </form>
                </header>

                @if($showOnboardingBanner ?? false)
                    <div style="background:linear-gradient(135deg,#f5e6d8,#fdf6f0);border:1px solid #e8d5c0;border-radius:14px;padding:16px 20px;margin-bottom:20px;display:flex;align-items:center;gap:16px;">
                        <span style="font-size:1.15rem;color:#8B4513;font-weight:800;">Tour</span>
                        <div style="flex:1;">
                            <p style="font-family:'Playfair Display',serif;font-size:0.95rem;font-weight:700;color:#3d2b1f;margin:0 0 2px;">
                                New to CardFlow?
                            </p>
                            <p style="font-family:'DM Sans',sans-serif;font-size:0.8rem;color:#8B6F5E;margin:0;">
                                Take the quick tour to learn collection tracking, marketplace listings, wishlist matches, messages, and Explorer.
                            </p>
                        </div>
                        <div style="display:flex;gap:8px;flex-shrink:0;">
                            <a href="{{ route('onboarding.start') }}"
                               style="font-family:'DM Sans',sans-serif;font-size:0.8rem;font-weight:600;background:#8B4513;color:#ffffff;padding:8px 16px;border-radius:20px;text-decoration:none;">
                                Start tour
                            </a>
                            <form method="POST" action="{{ route('onboarding.skip') }}">
                                @csrf
                                <button type="submit" style="font-family:'DM Sans',sans-serif;font-size:0.8rem;background:transparent;border:1px solid #d4b896;color:#8B4513;padding:8px 16px;border-radius:20px;cursor:pointer;">
                                    Dismiss
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                @if (!empty($searchQuery))
                    <section class="dashboard-card search-results-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Dashboard Search</p>
                                <h2>Results for "{{ $searchQuery }}"</h2>
                            </div>
                            <span class="mini-chip">{{ $searchResults['cards']->count() + $searchResults['trades']->count() }} results</span>
                        </div>

                        <div class="search-results-grid">
                            <div class="search-results-panel">
                                <h3>Your collection items</h3>
                                @forelse ($searchResults['cards'] as $item)
                                    <article class="search-result-item">
                                        <div>
                                            <strong>{{ $item->card->title }}</strong>
                                            <p>{{ $item->card->artist }} • {{ $item->card->album ?: 'Standalone' }}</p>
                                        </div>
                                        <span>{{ $item->condition }}</span>
                                    </article>
                                @empty
                                    <p class="search-empty">No collection items matched.</p>
                                @endforelse
                            </div>

                            <div class="search-results-panel">
                                <h3>Trade matches</h3>
                                @forelse ($searchResults['trades'] as $trade)
                                    <article class="search-result-item">
                                        <div>
                                            <strong>{{ $trade->partner_handle ?: $trade->partner_name }}</strong>
                                            <p>{{ $trade->card?->title ?? 'No card linked' }} • {{ ucfirst(str_replace('_', ' ', $trade->status)) }}</p>
                                        </div>
                                        <span>{{ $trade->partner_name }}</span>
                                    </article>
                                @empty
                                    <p class="search-empty">No trades or user matches found.</p>
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endif

                @if (! $onboarding->isComplete())
                    <section class="dashboard-card onboarding-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Start here</p>
                                <h2>Make CardFlow useful in three steps</h2>
                            </div>
                            <span class="mini-chip">Setup checklist</span>
                        </div>

                        <div class="onboarding-steps">
                            <a href="{{ route('collection.create') }}" class="onboarding-step {{ $onboarding->added_first_card ? 'is-complete' : '' }}">
                                <span>{{ $onboarding->added_first_card ? 'Done' : '1' }}</span>
                                <strong>Add your first photocard</strong>
                                <p>Unlock collection value, stats, and future listings.</p>
                            </a>

                            <a href="{{ route('wishlist.index') }}" class="onboarding-step {{ $onboarding->added_wishlist_item ? 'is-complete' : '' }}">
                                <span>{{ $onboarding->added_wishlist_item ? 'Done' : '2' }}</span>
                                <strong>Add one wishlist item</strong>
                                <p>Tell CardFlow which artist, album, or card you are searching for.</p>
                            </a>

                            <a href="{{ route('marketplace.index') }}" class="onboarding-step {{ $onboarding->browsed_marketplace ? 'is-complete' : '' }}">
                                <span>{{ $onboarding->browsed_marketplace ? 'Done' : '3' }}</span>
                                <strong>Browse marketplace matches</strong>
                                <p>Find active listings and start trade conversations with context.</p>
                            </a>
                        </div>
                    </section>
                @endif

                <section class="stats-grid" aria-label="Collection stats">
                    <article class="stat-card">
                        <span class="stat-label">Total cards</span>
                        <div class="stat-value">{{ $metrics['total_cards'] }}</div>
                        <div class="stat-note">Cards in your collection</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Collection value</span>
                        <div class="stat-value">{{ $formatMoney($metrics['collection_value']) }}</div>
                        <div class="stat-note">Estimated total market value</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Active trades</span>
                        <div class="stat-value">{{ $metrics['active_trades'] }}</div>
                        <div class="stat-note">Pending, offers, and active swaps</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Wishlist matches</span>
                        <div class="stat-value">{{ $metrics['wishlist_matches'] }}</div>
                        <div class="stat-note">Matched wishlist items</div>
                    </article>
                </section>

                <section class="dashboard-grid">
                    <article class="dashboard-card card-chart card-wide">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Collection pulse</p>
                                <h2>Estimated value trend</h2>
                            </div>
                            <span class="mini-chip">Last 6 months</span>
                        </div>

                        <div class="line-chart" aria-hidden="true">
                            <div class="chart-months">
                                @foreach ($valueTrend['points'] as $point)
                                    <span>{{ $point['label'] }}</span>
                                @endforeach
                            </div>
                            <svg viewBox="0 0 420 150" role="presentation">
                                <path d="{{ $valueTrend['path'] }}" />
                                @foreach ($valueTrend['svg_points'] as $point)
                                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" />
                                @endforeach
                            </svg>
                        </div>

                        <div class="chart-summary">
                            <div>
                                <span class="summary-label">Peak month</span>
                                <strong>{{ $valueTrend['peak_month'] }}</strong>
                            </div>
                            <div>
                                <span class="summary-label">Growth</span>
                                <strong>{{ $valueTrend['growth'] >= 0 ? '+' : '' }}{{ $valueTrend['growth'] }}%</strong>
                            </div>
                            <div>
                                <span class="summary-label">Stability</span>
                                <strong>{{ $valueTrend['stability'] }}</strong>
                            </div>
                        </div>
                    </article>

                    <article class="dashboard-card card-status">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Trade mix</p>
                                <h2>Status distribution</h2>
                            </div>
                            <span class="mini-chip">This month</span>
                        </div>

                        <div class="status-total">
                            <span class="summary-label">Trades</span>
                            <strong>{{ $tradeDistribution['total'] }}</strong>
                        </div>

                        <div class="status-list">
                            @foreach ($tradeDistribution['rows'] as $row)
                                <div class="status-row">
                                    <span>
                                        {{ $row['label'] }}
                                        @if (($row['count'] ?? 0) > 0)
                                            <small style="color:#b09070;">({{ $row['count'] }})</small>
                                        @endif
                                    </span>
                                    <div class="status-bar">
                                        <i style="width: {{ $row['percentage'] }}%; background: {{ $row['color'] ?? '#8B4513' }};"></i>
                                    </div>
                                    <strong style="color: {{ $row['percentage'] > 0 ? ($row['color'] ?? '#8B4513') : '#b09070' }};">
                                        {{ $row['percentage'] }}%
                                    </strong>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <article class="dashboard-card card-activity">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Wishlist activity</p>
                                <h2>Match momentum</h2>
                            </div>
                            <span class="mini-chip">Top categories</span>
                        </div>

                        @php
                            $wishlistMatchListings = $wishlistMomentum['matches'] ?? collect();
                        @endphp

                        <div class="activity-chart-panel">
                            @if ($wishlistMatchListings->isEmpty())
                                <div class="empty-state">
                                    <p>No wishlist match data yet.</p>
                                    <span>Add cards to your wishlist to see marketplace matches here.</span>
                                </div>
                            @else
                                <div class="wishlist-match-list">
                                    @foreach ($wishlistMatchListings->take(3) as $listing)
                                        <a href="{{ route('marketplace.cards.show', $listing) }}" class="wishlist-match-card">
                                            <img
                                                src="{{ $listing->card?->photo_url ?: asset('images/placeholder-card.png') }}"
                                                alt="{{ $listing->card?->title ?? 'Wishlist match' }}"
                                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                            >
                                            <div>
                                                <strong>{{ $listing->card?->title ?? 'Untitled card' }}</strong>
                                                <span>{{ '@'.($listing->user?->username ?? 'collector') }}</span>
                                            </div>
                                            <em>{{ $formatMoney($listing->userCard?->listing_price ?? $listing->card?->market_value ?? 0) }}</em>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="metric-strip">
                            <div>
                                <span class="summary-label">Strongest</span>
                                <strong>{{ $wishlistMomentum['strongest'] }}</strong>
                            </div>
                            <div>
                                <span class="summary-label">Fresh matches</span>
                                <strong>{{ $wishlistMomentum['fresh_matches'] }} today</strong>
                            </div>
                            <div>
                                <span class="summary-label">Avg. price</span>
                                <strong>{{ $formatMoney($wishlistMomentum['average_price']) }}</strong>
                            </div>
                        </div>
                    </article>

                    <article class="dashboard-card card-feed">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Recent activity</p>
                                <h2>Collection rhythm</h2>
                            </div>
                            <span class="mini-chip">Live feed</span>
                        </div>

                        <div class="feed-panel">
                            <ul class="activity-list">
                                @forelse ($activityFeed['items'] as $item)
                                    @php
                                        $dotColor = match($item['type'] ?? '') {
                                            'trade_request',
                                            'new_message',
                                            'listing_published',
                                            'listing_created' => '#8B4513',
                                            'wishlist_match',
                                            'trade_completed',
                                            'listing_sold',
                                            'card_sold',
                                            'card_traded' => '#2d6a4f',
                                            default => '#b09070',
                                        };
                                    @endphp
                                    <li style="--activity-dot: {{ $dotColor }};">
                                        <strong>{{ $item['title'] }}</strong>
                                        <span>{{ $item['time'] }}</span>
                                    </li>
                                @empty
                                    <li>
                                        <strong>No activity yet</strong>
                                        <span>Your activity feed will appear here once you start collecting.</span>
                                    </li>
                                @endforelse
                            </ul>
                        </div>

                        <div class="feed-footer">
                            <div>
                                <span class="summary-label">Daily actions</span>
                                <strong>{{ $activityFeed['daily_actions'] }}</strong>
                            </div>
                            <div>
                                <span class="summary-label">Reply rate</span>
                                <strong>{{ $activityFeed['reply_rate'] }}%</strong>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="dashboard-card market-card">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Hot cards</p>
                            <h2><span class="hot-cards-icon" aria-hidden="true">&#8599;</span> Trending in your circle</h2>
                        </div>
                        <span class="mini-chip">Updated hourly</span>
                    </div>

                    <div class="market-grid">
                        @foreach ($trendingCards as $card)
                            <article class="market-item">
                                <div class="hot-card-wrapper">
                                    <img
                                        src="{{ $card->photo_url ?: asset('images/placeholder-card.png') }}"
                                        alt="{{ $card->title }}"
                                        class="hot-card-image"
                                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                    >
                                </div>
                                <div class="market-meta">
                                    <h3>{{ $card->title }}</h3>
                                    <p>{{ $card->edition ?: $card->album }}</p>
                                    <div><span class="mini-chip">{{ $card->rarity }}</span><strong>{{ $formatMoney($card->market_value) }}</strong></div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
@endsection
