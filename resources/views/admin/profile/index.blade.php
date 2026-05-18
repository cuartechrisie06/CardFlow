@extends('layouts.admin')

@section('title', 'Admin Profile')

@section('content')
<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>My Profile</h1>
        <p class="dashboard-intro">Your admin identity and platform activity.</p>
    </div>
    <div class="dashboard-actions">
        <a href="{{ route('admin.settings') }}" class="dashboard-search-submit" style="text-decoration:none;">Edit profile</a>
    </div>
</header>

<section class="dashboard-card">
    <div style="display:flex;gap:1.25rem;align-items:flex-start;">
        <div style="display:grid;gap:0.7rem;justify-items:center;">
            @if($admin->avatar_url)
                <img src="{{ $admin->avatar_url }}" alt="{{ $admin->name }}" style="width:72px;height:72px;border-radius:999px;object-fit:cover;">
            @else
                <div style="width:72px;height:72px;border-radius:999px;background:#8B4513;color:#fff;display:grid;place-items:center;font-weight:800;font-size:1.25rem;">@initials($admin->name)</div>
            @endif
            <span class="mini-chip" style="background:#8B4513;color:#fff;">Administrator</span>
        </div>
        <div style="flex:1;">
            <h2 style="font-family:'Playfair Display',serif;font-size:1.8rem;color:#3d2b1f;margin:0;">{{ $admin->name }}</h2>
            <p style="color:#8B4513;margin:0.25rem 0;">{{ '@'.$admin->username }}</p>
            <p style="color:#8B6F5E;margin:0.2rem 0;">{{ $admin->email }}</p>
            <p style="color:#8B6F5E;margin:0.2rem 0;">Member since {{ $admin->created_at->format('M d, Y') }}</p>
            <p style="color:#8B6F5E;margin:0.2rem 0;">Last login: {{ $admin->last_login_at?->diffForHumans() ?? 'Not tracked yet' }}</p>
        </div>
    </div>

    <div class="stats-grid" style="margin-top:1rem;">
        @foreach([
            ['USERS MONITORED', $stats['users_monitored']],
            ['LISTINGS REVIEWED', $stats['listings_reviewed']],
            ['REPORTS RESOLVED', $stats['reports_resolved']],
            ['PLATFORM SINCE', $stats['platform_since']],
        ] as [$label, $value])
            <article class="stat-card" style="box-shadow:none;">
                <span class="stat-label">{{ $label }}</span>
                <div class="stat-value" style="font-size:1.45rem;">{{ $value }}</div>
            </article>
        @endforeach
    </div>
</section>

<section class="dashboard-card">
    @php($healthy = $health['no_proof'] === 0 && $health['unresolved_reports'] === 0)
    <div class="card-topline">
        <div>
            <p class="mini-label">Platform Health</p>
            <h2>Platform at a glance</h2>
        </div>
        <span class="mini-chip" style="color:#2d6a4f;background:rgba(45,106,79,0.1);">{{ $healthy ? 'Platform looks healthy' : 'Live' }}</span>
    </div>
    <ul class="activity-list" style="padding-top:0;">
        <li style="--activity-dot:#2d6a4f;"><strong>Active users this week</strong><span>{{ $health['active_users_week'] }}</span></li>
        <li style="--activity-dot:#c8956c;"><strong>Listings with no proof</strong><span>{{ $health['no_proof'] }} · <a href="{{ route('admin.moderation.proof') }}" style="color:#8B4513;">Review</a></span></li>
        <li style="--activity-dot:#A32D2D;"><strong>Unresolved reports</strong><span>{{ $health['unresolved_reports'] }} · <a href="{{ route('admin.moderation') }}" style="color:#8B4513;">View queue</a></span></li>
        <li style="--activity-dot:#b09070;"><strong>Trades completed this month</strong><span>{{ $health['trades_month'] }}</span></li>
    </ul>
</section>

<section class="dashboard-card">
    <div class="card-topline">
        <div>
            <p class="mini-label">Activity Log</p>
            <h2>Your recent actions</h2>
        </div>
        <span class="mini-chip">Last 30 days</span>
    </div>
    @if($actions->isEmpty())
        <div class="empty-state" style="min-height:150px;background:linear-gradient(180deg, rgba(247,242,237,0.95), rgba(243,237,230,0.9));border:1px solid rgba(237,227,217,0.95);border-radius:1.35rem;">
            <p>No actions recorded yet.</p>
            <span>Your moderation activity will appear here.</span>
        </div>
    @else
        <ul class="activity-list">
            @foreach($actions as $action)
                <li><strong>You {{ lcfirst($action->description) }}</strong><span>{{ $action->created_at->diffForHumans() }}</span></li>
            @endforeach
        </ul>
    @endif
</section>
@endsection
