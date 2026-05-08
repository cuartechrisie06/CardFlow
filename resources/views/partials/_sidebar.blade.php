<aside class="dashboard-sidebar">
    <a href="{{ route('profile.show', $username) }}" class="user-card-link" aria-label="View your profile">
        <div class="sidebar-brand user-card">
            <div class="sidebar-avatar"></div>
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
    </nav>

    @include('partials.sidebar-collector', ['user' => $user])
</aside>
