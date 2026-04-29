<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Wishlist</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
            $priorityLabel = fn (string $priority) => match ($priority) {
                'high' => 'high priority',
                'medium' => 'medium priority',
                default => 'low priority',
            };
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
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link is-active">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header wishlist-header">
                    <div>
                        <p class="dashboard-kicker">Wishlist & Matching</p>
                        <h1>Wishlist & matching</h1>
                    </div>

                    <div class="dashboard-actions">
                        <form method="GET" action="{{ route('wishlist.index') }}" class="dashboard-actions">
                            <label class="dashboard-search">
                                <span class="sr-only">Search wanted cards</span>
                                <input type="search" name="q" value="{{ $search }}" placeholder="Search wanted cards...">
                            </label>
                        </form>
                        <a href="#wishlist-add-form" class="dashboard-add-card">+ Add to Wishlist</a>
                    </div>
                </header>

                @if (session('status'))
                    <div class="auth-status">{{ session('status') }}</div>
                @endif

                <section class="wishlist-layout">
                    <article class="wishlist-panel">
                        <div class="wishlist-panel-top">
                            <div>
                                <p class="mini-label">Wishlist</p>
                                <h2>{{ $wishlistItems->count() }} cards on your wishlist</h2>
                            </div>
                            <span class="mini-chip">Live matching</span>
                        </div>

                        <div class="wishlist-list">
                            @forelse ($wishlistItems as $item)
                                @php
                                    $matches = $matchesByWishlist->get($item->id, collect());
                                    $topMatch = $matches->first();
                                @endphp
                                <div class="wishlist-row">
                                    <div>
                                        <strong>{{ $item->card->title }}</strong>
                                        <p>{{ strtoupper($item->card->artist) }} • {{ strtoupper($item->card->album ?? 'WISHLIST') }}</p>
                                    </div>
                                    <div class="wishlist-row-actions">
                                        <span class="mini-chip">{{ $priorityLabel($item->priority) }}</span>
                                        <span class="wishlist-match-chip">{{ $matches->isNotEmpty() ? $matches->count().' matches' : 'No match yet' }}</span>
                                        <form action="{{ route('wishlist.destroy', $item) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="wishlist-remove-button">Remove</button>
                                        </form>
                                    </div>
                                </div>
                                @if ($topMatch)
                                    @php
                                        $topListing = $topMatch['listing'];
                                    @endphp
                                    <div class="wishlist-row-match-preview">
                                        <span>{{ $topListing->card->title }}</span>
                                        <a href="{{ route('marketplace.cards.show', $topListing) }}" class="wishlist-inline-link">View listing from {{ '@'.$topListing->user->username }}</a>
                                    </div>
                                @endif
                            @empty
                                <div class="collection-empty">No wishlist items yet. Add your first wanted card below.</div>
                            @endforelse
                        </div>
                    </article>

                    <article class="wishlist-matches-panel">
                        <div class="wishlist-panel-top">
                            <div>
                                <p class="mini-label">Active matches</p>
                                <h2>{{ $activeMatches->isNotEmpty() ? 'Real marketplace matches' : 'No live matches yet' }}</h2>
                            </div>
                            <span class="mini-chip">{{ $activeMatches->isNotEmpty() ? 'Live matches' : 'Waiting' }}</span>
                        </div>

                        @if ($activeMatches->isNotEmpty())
                            <div class="wishlist-match-results">
                                @foreach ($activeMatches as $item)
                                    @foreach ($matchesByWishlist->get($item->id, collect()) as $match)
                                        @php
                                            $listing = $match['listing'];
                                            $listedCard = $listing->card;
                                            $owner = $listing->user;
                                            $ownedCard = $listing->userCard;
                                            $photoUrl = $ownedCard->photo_path ? \Illuminate\Support\Facades\Storage::url($ownedCard->photo_path) : null;
                                        @endphp
                                        <div class="wishlist-match-card">
                                            <div class="wishlist-match-meta">
                                                <span class="mini-chip">{{ '@'.$owner->username }}</span>
                                                <span class="mini-chip">{{ $ownedCard->is_for_sale ? 'For sale' : ($ownedCard->is_for_trade ? 'Open for trade' : 'Public listing') }}</span>
                                            </div>
                                            <div class="wishlist-match-thumb {{ $photoUrl ? 'collection-thumb-photo' : $listedCard->thumbnail_style }}" @if ($photoUrl) style="background-image: url('{{ $photoUrl }}');" @endif></div>
                                            <div class="wishlist-match-copy">
                                                <strong>{{ $listedCard->title }}</strong>
                                                <p>{{ $listedCard->artist }} • {{ $listedCard->album ?: 'Standalone release' }}</p>
                                                <p>{{ $ownedCard->listing_price ? 'PHP '.number_format((float) $ownedCard->listing_price, 0) : ($ownedCard->is_for_trade ? 'Trade listing' : 'Public showcase') }}</p>
                                            </div>
                                            <a href="{{ route('marketplace.cards.show', $listing) }}" class="marketplace-link">View listing</a>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <div class="collection-empty">No active matches yet. We’ll surface real marketplace listings here when they appear.</div>
                        @endif
                    </article>
                </section>

                <section id="wishlist-add-form" class="dashboard-card wishlist-add-shell">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Add wishlist item</p>
                            <h2>Track a wanted photocard</h2>
                        </div>
                        <span class="mini-chip">Saved to your account</span>
                    </div>

                    <form method="POST" action="{{ route('wishlist.store') }}" class="card-create-form">
                        @csrf
                        <div class="card-form-grid">
                            <label class="field-group">
                                <span>Artist / Group</span>
                                <input type="text" name="artist" value="{{ old('artist') }}" placeholder="Aespa">
                                @error('artist') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Card Title</span>
                                <input type="text" name="title" value="{{ old('title') }}" placeholder="Winter - Broadcast card">
                                @error('title') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Album</span>
                                <input type="text" name="album" value="{{ old('album') }}" placeholder="Armageddon">
                                @error('album') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Priority</span>
                                <select name="priority" class="field-select">
                                    <option value="high" @selected(old('priority', 'high') === 'high')>High priority</option>
                                    <option value="medium" @selected(old('priority') === 'medium')>Medium priority</option>
                                    <option value="low" @selected(old('priority') === 'low')>Low priority</option>
                                </select>
                                @error('priority') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Target Price</span>
                                <input type="number" name="target_price" value="{{ old('target_price') }}" min="0" step="0.01" placeholder="1200">
                                @error('target_price') <small class="field-error">{{ $message }}</small> @enderror
                            </label>
                        </div>

                        <div class="create-form-actions">
                            <button type="submit" class="dashboard-add-card">Save wishlist item</button>
                        </div>
                    </form>
                </section>
            </section>
        </main>
    </body>
</html>
