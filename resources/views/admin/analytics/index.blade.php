@extends('layouts.admin')

@section('title', 'Admin Analytics')

@section('content')
<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>Analytics</h1>
        <p class="dashboard-intro">Platform growth, listing activity, and trade trends.</p>
    </div>
    <div class="dashboard-actions">
        <span class="mini-chip">Last 30 days</span>
    </div>
</header>

<section class="dashboard-card card-chart">
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
            <span class="summary-label">Total users</span>
            <strong>{{ $userGrowthTrend['total_users'] }}</strong>
        </div>
    </div>
</section>

<section class="dashboard-grid">
    <article class="dashboard-card card-status">
        <div class="card-topline">
            <div>
                <p class="mini-label">Listing Activity</p>
                <h2>Status breakdown</h2>
            </div>
            <span class="mini-chip">This month</span>
        </div>

        <div class="status-list">
            @foreach($listingActivity['rows'] as $row)
                <div class="status-row">
                    <span>{{ $row['label'] }}</span>
                    <div class="status-bar">
                        <i style="width:{{ $row['percentage'] }}%;background:{{ $row['color'] }};"></i>
                    </div>
                    <strong>{{ $row['count'] }} · {{ $row['percentage'] }}%</strong>
                </div>
            @endforeach
        </div>
    </article>

    <article class="dashboard-card">
        <div class="card-topline">
            <div>
                <p class="mini-label">Trade Activity</p>
                <h2>Volume summary</h2>
            </div>
            <span class="mini-chip">All time</span>
        </div>

        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;">
            @foreach([
                ['TOTAL TRADES', $tradeStats['total']],
                ['COMPLETED', $tradeStats['completed']],
                ['PENDING', $tradeStats['pending']],
                ['CANCELLED', $tradeStats['cancelled']],
            ] as [$label, $value])
                <div class="stat-card" style="box-shadow:none;">
                    <span class="stat-label">{{ $label }}</span>
                    <div class="stat-value">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </article>
</section>

<section class="dashboard-card">
    <div class="card-topline">
        <div>
            <p class="mini-label">Top Collectors</p>
            <h2>Most active users</h2>
        </div>
        <span class="mini-chip">Top 5</span>
    </div>

    <div style="border:0.5px solid rgba(92,61,46,0.12);border-radius:12px;overflow:hidden;background:rgba(255,251,246,0.94);">
        <div style="display:grid;grid-template-columns:2fr 0.7fr 0.8fr 0.8fr 1fr;gap:16px;padding:13px 20px;background:#ede3d5;border-bottom:0.5px solid rgba(92,61,46,0.12);">
            @foreach(['USER', 'CARDS', 'LISTINGS', 'TRADES', 'JOINED'] as $col)
                <p class="stat-label" style="margin:0;font-size:11px;color:rgba(143,90,62,0.72);">{{ $col }}</p>
            @endforeach
        </div>
        @foreach($topCollectors as $collector)
            <div style="display:grid;grid-template-columns:2fr 0.7fr 0.8fr 0.8fr 1fr;gap:16px;padding:15px 20px;border-bottom:0.5px solid rgba(92,61,46,0.08);align-items:center;">
                <div>
                    <p style="font-size:0.9rem;font-weight:700;color:#3d2b1f;margin:0;">{{ $collector->name }}</p>
                    <p style="font-size:0.76rem;color:#8B6F5E;margin:2px 0 0;">{{ '@'.$collector->username }}</p>
                </div>
                <p style="font-size:0.86rem;color:#3d2b1f;margin:0;">{{ $collector->user_cards_count }}</p>
                <p style="font-size:0.86rem;color:#3d2b1f;margin:0;">{{ $collector->marketplace_listings_count }}</p>
                <p style="font-size:0.86rem;color:#3d2b1f;margin:0;">{{ $collector->sent_trade_requests_count + $collector->received_trade_requests_count }}</p>
                <p style="font-size:0.82rem;color:#8B6F5E;margin:0;">{{ $collector->created_at->format('M d, Y') }}</p>
            </div>
        @endforeach
    </div>
</section>
@endsection
