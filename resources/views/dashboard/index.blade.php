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
                                    <span>{{ $row['label'] }}</span>
                                    <div class="status-bar"><i style="width: {{ $row['percentage'] }}%"></i></div>
                                    <strong>{{ $row['percentage'] }}%</strong>
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

                        <div class="activity-chart-panel">
                            <div class="bar-chart" aria-hidden="true">
                                @forelse ($wishlistMomentum['bars'] as $bar)
                                    <div class="bar-chart-item"><i style="height: {{ $bar['height'] }}%"></i><span>{{ $bar['label'] }}</span></div>
                                @empty
                                    <div class="empty-state">No wishlist match data yet.</div>
                                @endforelse
                            </div>
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
                                    <li>
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
                            <h2>Trending in your circle</h2>
                        </div>
                        <span class="mini-chip">Updated hourly</span>
                    </div>

                    <div class="market-grid">
                        @foreach ($trendingCards as $card)
                            <article class="market-item">
                                <div class="market-thumb {{ $card->thumbnail_style }}"></div>
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

