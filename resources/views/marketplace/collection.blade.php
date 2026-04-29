<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | {{ $profileUser->name }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $viewer = auth()->user();
            $viewerUsername = $viewer->username ?: 'collector';
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $viewer->name }}</p>
                        <span>{{ '@'.$viewerUsername }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link is-active">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="#" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $viewer])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Marketplace Collection</p>
                        <h1>{{ $profileUser->name }}</h1>
                        <p class="dashboard-intro">Public and listed cards visible from this collector’s marketplace profile.</p>
                    </div>

                    <a href="{{ route('marketplace.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Back to marketplace</a>
                </header>

                <section class="dashboard-card featured-marketplace-card">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Public collection</p>
                            <h2>{{ '@'.$profileUser->username }}</h2>
                        </div>
                        <span class="mini-chip">{{ $publicCards->total() }} visible cards</span>
                    </div>

                    <div class="marketplace-grid">
                        @forelse ($publicCards as $item)
                            @php
                                $card = $item->card;
                                $ownedCard = $item->userCard;
                                $photoUrl = $ownedCard->photo_path ? \Illuminate\Support\Facades\Storage::url($ownedCard->photo_path) : null;
                            @endphp
                            <article class="marketplace-item">
                                <a href="{{ route('marketplace.cards.show', $item) }}" class="marketplace-item-link">
                                    <div class="marketplace-thumb {{ $photoUrl ? 'collection-thumb-photo' : $card->thumbnail_style }}" @if ($photoUrl) style="background-image: url('{{ $photoUrl }}');" @endif></div>
                                    <div class="marketplace-meta">
                                        <h3>{{ $card->title }}</h3>
                                        <p>{{ strtoupper($card->artist) }}</p>
                                        <p>{{ $card->album ?: 'Standalone release' }}</p>
                                        <div class="marketplace-meta-footer">
                                            <span>{{ $ownedCard->is_for_trade ? 'Trade listing' : ($ownedCard->is_for_sale ? 'For sale' : 'Public showcase') }}</span>
                                            <span class="marketplace-link">View card</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @empty
                            <div class="collection-empty">This collector has no public marketplace cards yet.</div>
                        @endforelse
                    </div>
                </section>
            </section>
        </main>
    </body>
</html>
