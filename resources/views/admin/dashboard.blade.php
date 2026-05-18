@extends('layouts.admin')

@section('title', 'CardFlow Admin')
@section('body_class', 'dashboard-body')

@section('content')
<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>CardFlow Admin</h1>
        <p class="dashboard-intro">
            Platform overview, user activity, listing health, and moderation queue.
        </p>
    </div>

    <form method="GET" action="{{ route('admin.index') }}" class="dashboard-actions">
        <label class="dashboard-search">
            <span class="sr-only">Search admin data</span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search users, listings, reports...">
        </label>
        <a href="{{ route('admin.analytics') }}" class="dashboard-search-submit" style="text-decoration:none;">Export report</a>
        <a href="{{ route('admin.listings') }}" class="dashboard-add-card">+ Add catalog card</a>
    </form>
</header>

<section class="dashboard-card onboarding-card">
    <div class="card-topline">
        <div>
            <p class="mini-label">Quick Actions</p>
            <h2>Manage CardFlow in three steps</h2>
        </div>
        <span class="mini-chip">Setup checklist</span>
    </div>

    <div class="onboarding-steps">
        <a href="{{ route('admin.moderation') }}" class="onboarding-step {{ $moderationCount === 0 ? 'is-complete' : '' }}">
            <span>{{ $moderationCount === 0 ? 'Done' : '1' }}</span>
            <strong>Review moderation queue</strong>
            <p>Approve or reject flagged listings and reports.</p>
        </a>

        <a href="{{ route('admin.users') }}" class="onboarding-step">
            <span>2</span>
            <strong>Manage users</strong>
            <p>View, edit, or suspend registered accounts.</p>
        </a>

        <a href="#analytics" class="onboarding-step {{ $quickActionsDone ? 'is-complete' : '' }}">
            <span>{{ $quickActionsDone ? 'Done' : '3' }}</span>
            <strong>Check platform analytics</strong>
            <p>Monitor growth, trades, and listing health.</p>
        </a>
    </div>
</section>

<section class="stats-grid" aria-label="Platform stats">
    @foreach ($stats as $stat)
        <article class="stat-card">
            <span class="stat-label">{{ $stat['label'] }}</span>
            <div class="stat-value">{{ $stat['value'] }}</div>
            <div class="stat-note">{{ $stat['note'] }}</div>
        </article>
    @endforeach
</section>

<section class="dashboard-grid" id="analytics">
    <article class="dashboard-card card-chart card-wide">
        <div class="card-topline">
            <div>
                <p class="mini-label">Platform Pulse</p>
                <h2>User growth trend</h2>
            </div>
            <span class="mini-chip">Last 6 months</span>
        </div>

        <div class="line-chart" aria-hidden="true">
            <div class="chart-months">
                @foreach ($userGrowthTrend['points'] as $point)
                    <span>{{ $point['label'] }}</span>
                @endforeach
            </div>
            <svg viewBox="0 0 420 150" role="presentation">
                <path d="{{ $userGrowthTrend['path'] }}" />
                @foreach ($userGrowthTrend['svg_points'] as $point)
                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" />
                @endforeach
            </svg>
        </div>

        <div class="chart-summary">
            <div>
                <span class="summary-label">Peak month</span>
                <strong>{{ $userGrowthTrend['peak_month'] }}</strong>
            </div>
            <div>
                <span class="summary-label">New users</span>
                <strong>+{{ $userGrowthTrend['new_users'] }}</strong>
            </div>
            <div>
                <span class="summary-label">Retention</span>
                <strong>{{ $userGrowthTrend['retention'] }}%</strong>
            </div>
        </div>
    </article>

    <article class="dashboard-card card-status">
        <div class="card-topline">
            <div>
                <p class="mini-label">Listing Health</p>
                <h2>Status distribution</h2>
            </div>
            <span class="mini-chip">This month</span>
        </div>

        <div class="status-total">
            <span class="summary-label">Listings</span>
            <strong>{{ $listingHealth['total'] }}</strong>
        </div>

        <div class="status-list">
            @foreach ($listingHealth['rows'] as $row)
                <div class="status-row">
                    <span>
                        {{ $row['label'] }}
                        @if (($row['count'] ?? 0) > 0)
                            <small style="color:#b09070;">({{ $row['count'] }})</small>
                        @endif
                    </span>
                    <div class="status-bar">
                        <i style="width: {{ $row['percentage'] }}%; background: {{ $row['color'] }};"></i>
                    </div>
                    <strong style="color: {{ $row['percentage'] > 0 ? $row['color'] : '#b09070' }};">
                        {{ $row['percentage'] }}%
                    </strong>
                </div>
            @endforeach
        </div>
    </article>

    <article class="dashboard-card card-activity">
        <div class="card-topline">
            <div>
                <p class="mini-label">Moderation Queue</p>
                <h2>Pending actions</h2>
            </div>
            <span class="mini-chip">Top flags</span>
        </div>

        <div class="activity-chart-panel">
            @if ($moderationQueue->isEmpty())
                <div class="empty-state">
                    <p>No items in the moderation queue.</p>
                    <span>Flagged listings and reports will appear here.</span>
                </div>
            @else
                <div class="wishlist-match-list">
                    @foreach ($moderationQueue as $listing)
                        <div class="wishlist-match-card">
                            <img
                                src="{{ $listing->card?->photo_url ?: asset('images/placeholder-card.png') }}"
                                alt="{{ $listing->card?->title ?? 'Flagged listing' }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                            >
                            <div>
                                <strong>{{ $listing->card?->title ?? 'Untitled listing' }}</strong>
                                <span>{{ '@'.($listing->user?->username ?? 'collector') }} · {{ $formatMoney($listing->userCard?->listing_price ?? $listing->card?->market_value ?? 0) }}</span>
                            </div>
                            <div style="display:flex;gap:0.4rem;align-items:center;">
                                <form method="POST" action="{{ route('admin.listings.verify-proof', $listing) }}" class="dashboard-inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="mini-chip" style="border:1px solid rgba(137,104,78,0.12);cursor:pointer;">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.listings.delete', $listing) }}" class="dashboard-inline-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mini-chip" style="border:1px solid rgba(137,104,78,0.12);cursor:pointer;">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="metric-strip">
            <div>
                <span class="summary-label">Flagged today</span>
                <strong>{{ $moderationStats['flagged_today'] }}</strong>
            </div>
            <div>
                <span class="summary-label">Resolved this week</span>
                <strong>{{ $moderationStats['resolved_this_week'] }}</strong>
            </div>
            <div>
                <span class="summary-label">Avg response</span>
                <strong>{{ $moderationStats['average_response'] }}h</strong>
            </div>
        </div>
    </article>

    <article class="dashboard-card card-feed">
        <div class="card-topline">
            <div>
                <p class="mini-label">Recent Activity</p>
                <h2>Platform rhythm</h2>
            </div>
            <span class="mini-chip">Live feed</span>
        </div>

        <div class="feed-panel">
            <ul class="activity-list">
                @forelse ($platformActivity as $item)
                    @php
                        $dotColor = match($item['type'] ?? '') {
                            'trade' => '#2d6a4f',
                            'listing' => '#8B4513',
                            'user' => '#c8956c',
                            default => '#b09070',
                        };
                    @endphp
                    <li style="--activity-dot: {{ $dotColor }};">
                        <strong>{{ $item['title'] }}</strong>
                        <span>{{ $item['time'] }}</span>
                    </li>
                @empty
                    <li>
                        <strong>No platform activity yet</strong>
                        <span>Admin activity will appear once collectors start using CardFlow.</span>
                    </li>
                @endforelse
            </ul>
        </div>

        <div class="feed-footer">
            <div>
                <span class="summary-label">Daily actions</span>
                <strong>{{ $dailyActions }}</strong>
            </div>
            <div>
                <span class="summary-label">Moderation rate</span>
                <strong>{{ $moderationRate }}%</strong>
            </div>
        </div>
    </article>
</section>

<section class="dashboard-card market-card">
    <div class="card-topline">
        <div>
            <p class="mini-label">Recent Listings</p>
            <h2>Latest on the marketplace</h2>
        </div>
        <span class="mini-chip">Updated hourly</span>
    </div>

    <div class="market-grid">
        @forelse ($recentListings as $listing)
            <article class="market-item">
                <div class="hot-card-wrapper">
                    <img
                        src="{{ $listing->card?->photo_url ?: asset('images/placeholder-card.png') }}"
                        alt="{{ $listing->card?->title ?? 'Marketplace listing' }}"
                        class="hot-card-image"
                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                    >
                </div>
                <div class="market-meta">
                    <h3>{{ $listing->card?->title ?? 'Untitled card' }}</h3>
                    <p>{{ $listing->card?->edition ?: ($listing->card?->album ?? 'Marketplace listing') }}</p>
                    <div>
                        <span class="mini-chip">{{ $listing->userCard?->is_for_trade ? 'For trade' : 'For sale' }}</span>
                        <strong>{{ $formatMoney($listing->userCard?->listing_price ?? $listing->card?->market_value ?? 0) }}</strong>
                    </div>
                    <a href="{{ route('admin.listings') }}" style="color:var(--cardflow-muted);font-size:0.78rem;text-decoration:none;">Flag</a>
                </div>
            </article>
        @empty
            <div class="empty-state">
                <p>No recent listings yet.</p>
                <span>Marketplace listings will appear here.</span>
            </div>
        @endforelse
    </div>
</section>
@endsection
