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

    <a href="{{ route('profile.show', $username) }}" class="user-card-link" aria-label="View your profile">
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

    <nav class="sidebar-nav" aria-label="Primary">
        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Dashboard</a>
        <a href="{{ route('collection.index') }}" class="sidebar-link {{ request()->routeIs('collection.*', 'cards.show') ? 'is-active' : '' }}">My Collection</a>
        <a href="{{ route('marketplace.index') }}" class="sidebar-link {{ request()->routeIs('marketplace.*') ? 'is-active' : '' }}">
            <span>Marketplace</span>
            @if (($listingInboxCount ?? 0) > 0)
                <span class="nav-badge">{{ $listingInboxCount }}</span>
            @endif
        </a>
        <a href="{{ route('wishlist.index') }}" class="sidebar-link {{ request()->routeIs('wishlist.*') ? 'is-active' : '' }}">
            <span>Wishlist</span>
            @if (($wishlistMatchCount ?? 0) > 0)
                <span class="nav-badge">{{ $wishlistMatchCount }}</span>
            @endif
        </a>
        <a href="{{ route('messages.index') }}" class="sidebar-link {{ request()->routeIs('messages.*') ? 'is-active' : '' }}">
            <span>Messages</span>
            @if (($unreadCount ?? 0) > 0)
                <span class="nav-badge">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="{{ route('explorer.index') }}" class="sidebar-link {{ request()->routeIs('explorer.*') ? 'is-active' : '' }}">Explorer</a>
        <a href="{{ route('stats.index') }}" class="sidebar-link {{ request()->routeIs('stats.*') ? 'is-active' : '' }}">Stats</a>
        @if($user->isAdmin())
            <a href="{{ route('admin.index') }}"
               class="sidebar-link {{ request()->routeIs('admin.*') ? 'is-active' : '' }}"
               style="border:1px solid rgba(255,255,255,0.2);background:{{ request()->routeIs('admin.*') ? 'rgba(255,255,255,0.18)' : 'rgba(139,69,19,0.22)' }};">
                Admin Panel
            </a>
        @endif
    </nav>

    @include('partials.sidebar-collector', ['user' => $user])
</aside>
