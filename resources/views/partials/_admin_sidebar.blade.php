<aside class="dashboard-sidebar">
    <div class="sidebar-logo">
        <svg xmlns="http://www.w3.org/2000/svg" height="28" viewBox="0 0 200 36" role="img" aria-label="CardFlow" class="sidebar-logo-img">
            <rect x="0" y="4" width="22" height="30" rx="4" fill="rgba(255,255,255,0.4)" transform="rotate(-6,11,19)" />
            <rect x="4" y="2" width="22" height="30" rx="4" fill="rgba(255,255,255,0.65)" transform="rotate(-1,15,17)" />
            <rect x="8" y="1" width="22" height="30" rx="4" fill="rgba(255,255,255,0.9)" />
            <text x="36" y="24" font-family="Georgia,serif" font-size="20" font-weight="700" fill="#ffffff">Card</text>
            <text x="90" y="24" font-family="Georgia,serif" font-size="20" font-weight="400" fill="rgba(255,255,255,0.75)">Flow</text>
        </svg>
    </div>

    <a href="{{ route('admin.profile') }}" class="user-card-link" aria-label="View your admin profile">
        <div class="sidebar-brand user-card">
            @if ($user->avatar_url)
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} avatar" class="sidebar-avatar sidebar-avatar-img">
            @else
                <div class="sidebar-avatar sidebar-avatar-initials">@initials($user->name)</div>
            @endif
            <div>
                <p>{{ $user->name }}</p>
                <span>{{ '@'.$username }}</span>
            </div>
        </div>
    </a>

    <nav class="sidebar-nav" aria-label="Admin">
        <a href="{{ route('admin.index') }}" class="sidebar-link {{ request()->routeIs('admin.index') ? 'is-active' : '' }}">Dashboard</a>
        <a href="{{ route('admin.profile') }}" class="sidebar-link {{ request()->routeIs('admin.profile') ? 'is-active' : '' }}">Profile</a>
        <a href="{{ route('admin.users') }}" class="sidebar-link {{ request()->routeIs('admin.users*') ? 'is-active' : '' }}">Users</a>
        <a href="{{ route('admin.listings') }}" class="sidebar-link {{ request()->routeIs('admin.listings*') ? 'is-active' : '' }}">Listings</a>
        <a href="{{ route('admin.trades') }}" class="sidebar-link {{ request()->routeIs('admin.trades*') ? 'is-active' : '' }}">Trades</a>
        <a href="{{ route('admin.moderation') }}" class="sidebar-link {{ request()->routeIs('admin.moderation', 'admin.reports') ? 'is-active' : '' }}">
            <span>Moderation</span>
            @if (($moderationCount ?? 0) > 0)
                <span class="nav-badge">{{ $moderationCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.analytics') }}" class="sidebar-link {{ request()->routeIs('admin.analytics') ? 'is-active' : '' }}">Analytics</a>
        <a href="{{ route('admin.catalog.index') }}" class="sidebar-link {{ request()->routeIs('admin.catalog*') ? 'is-active' : '' }}">Catalog</a>
        <a href="{{ route('admin.settings') }}" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'is-active' : '' }}">Settings</a>
    </nav>

    <div class="sidebar-collector">
        <form action="{{ route('admin.logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="logout-button">Log out</button>
        </form>
    </div>
</aside>
