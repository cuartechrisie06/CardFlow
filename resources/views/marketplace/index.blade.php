@extends('layouts.app')

@section('title', 'CardFlow | Marketplace')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Marketplace</p>
                        <h1>Marketplace</h1>
                    </div>

                    <form method="GET" action="{{ route('marketplace.index') }}" class="dashboard-actions">
                        <label class="dashboard-search">
                            <span class="sr-only">Search marketplace</span>
                            <input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Search marketplace...">
                        </label>
                        <button type="submit" class="dashboard-search-submit">Search</button>
                        <a href="{{ route('marketplace.create') }}" class="dashboard-add-card">
                            Post listing
                        </a>
                    </form>
                </header>

                <section class="dashboard-card marketplace-shell">
                    <form method="GET" action="{{ route('marketplace.index') }}" class="marketplace-toolbar">
                        @if ($filters['search'] !== '')
                            <input type="hidden" name="q" value="{{ $filters['search'] }}">
                        @endif
                        @if ($filters['artist'] !== '')
                            <input type="hidden" name="artist" value="{{ $filters['artist'] }}">
                        @endif
                        @if ($filters['rarity'] !== '')
                            <input type="hidden" name="rarity" value="{{ $filters['rarity'] }}">
                        @endif
                        @if ($filters['type'] !== 'all')
                            <input type="hidden" name="type" value="{{ $filters['type'] }}">
                        @endif
                        @if ($filters['min_price'] !== null && $filters['min_price'] !== '')
                            <input type="hidden" name="min_price" value="{{ $filters['min_price'] }}">
                        @endif
                        @if ($filters['max_price'] !== null && $filters['max_price'] !== '')
                            <input type="hidden" name="max_price" value="{{ $filters['max_price'] }}">
                        @endif

                        <div class="collection-filters">
                            @foreach ($filters['items'] as $value => $label)
                                <button type="submit" name="filter" value="{{ $value }}" class="collection-filter {{ $filters['active'] === $value ? 'is-active' : '' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </form>

                    <form method="GET" action="{{ route('marketplace.index') }}" class="dashboard-card marketplace-advanced-filters">
                        <input type="hidden" name="filter" value="{{ $filters['active'] }}">
                        @if ($filters['search'] !== '')
                            <input type="hidden" name="q" value="{{ $filters['search'] }}">
                        @endif

                        <div class="card-form-grid marketplace-filter-grid">
                            <label class="form-field">
                                <span>Artist</span>
                                <input type="text" name="artist" value="{{ $filters['artist'] }}" placeholder="Search artist">
                            </label>

                            <label class="form-field">
                                <span>Rarity</span>
                                <select name="rarity">
                                    <option value="">All rarities</option>
                                    @foreach ($rarityOptions as $option)
                                        <option value="{{ $option }}" @selected($filters['rarity'] === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="form-field">
                                <span>Type</span>
                                <select name="type">
                                    <option value="all" @selected($filters['type'] === 'all')>All</option>
                                    <option value="sale" @selected($filters['type'] === 'sale')>Sale</option>
                                    <option value="trade" @selected($filters['type'] === 'trade')>Trade</option>
                                </select>
                            </label>

                            <label class="form-field">
                                <span>Min price</span>
                                <input type="number" name="min_price" value="{{ $filters['min_price'] }}" min="0" step="0.01" placeholder="0">
                            </label>

                            <label class="form-field">
                                <span>Max price</span>
                                <input type="number" name="max_price" value="{{ $filters['max_price'] }}" min="0" step="0.01" placeholder="5000">
                            </label>
                        </div>

                        <div class="marketplace-filter-actions">
                            <button type="submit" class="dashboard-add-card">Apply filters</button>
                            <a href="{{ route('marketplace.index', ['filter' => $filters['active'], 'q' => $filters['search'] ?: null]) }}" class="dashboard-search-submit">Reset</a>
                        </div>
                    </form>

                    <section class="stats-grid marketplace-stats">
                        <article class="stat-card">
                            <span class="stat-label">Open listings</span>
                            <div class="stat-value">{{ $marketMetrics['open_listings'] }}</div>
                            <div class="stat-note">ready for offers</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Open trades</span>
                            <div class="stat-value">{{ $marketMetrics['open_trades'] }}</div>
                            <div class="stat-note">active conversations</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Sale offers</span>
                            <div class="stat-value">{{ $marketMetrics['sale_offers'] }}</div>
                            <div class="stat-note">priced and ready</div>
                        </article>
                        <article class="stat-card">
                            <span class="stat-label">Quick action</span>
                            <div class="stat-value">{{ $marketMetrics['quick_actions'] }}</div>
                            <div class="stat-note">one-tap offer flow</div>
                        </article>
                    </section>

                    @if (! empty($wishlistMatchedListingIds))
                        <section class="dashboard-card featured-marketplace-card wishlist-match-section">
                            <div class="card-topline">
                                <div>
                                    <p class="mini-label">Wishlist Matches</p>
                                    <h2>Listings that match your wishlist</h2>
                                </div>
                                <span class="mini-chip">{{ count($wishlistMatchedListingIds) }} match{{ count($wishlistMatchedListingIds) === 1 ? '' : 'es' }}</span>
                            </div>

                            <div class="marketplace-grid">
                                @foreach ($featuredListings as $item)
                                    @continue(! in_array($item->id, $wishlistMatchedListingIds, true))

                                    @php
                                        $card = $item->card;
                                        $ownedCard = $item->userCard;
                                        $listingTags = collect([
                                            $ownedCard->is_for_trade ? 'Trade' : null,
                                            $ownedCard->is_for_sale ? 'Sale' : null,
                                            $ownedCard->is_public ? 'Public' : null,
                                            $card->rarity,
                                        ])->filter()->take(3);
                                        $photoUrl = $storagePhotoUrl($ownedCard->photo_path);
                                    @endphp
                                    <article class="marketplace-item marketplace-item-match">
                                        <a href="{{ route('marketplace.cards.show', $item) }}" class="marketplace-item-link">
                                            <div class="marketplace-thumb card-media-ratio {{ $card->thumbnail_style }}">
                                                <img
                                                    src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                                    alt="{{ $card->title }}"
                                                    class="card-media-image"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                                >
                                                <div class="marketplace-tags">
                                                    <span class="marketplace-match-badge">✦ Matches your wishlist</span>
                                                    @foreach ($listingTags as $tag)
                                                        <span class="collection-pill">{{ $tag }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div class="marketplace-meta">
                                                <h3>{{ $card->title }}</h3>
                                                <p>{{ strtoupper($card->artist) }}</p>
                                                <p>{{ $card->album ?: 'Standalone release' }}</p>
                                                <p>Owner: <a href="{{ route('marketplace.user', $item->user) }}" class="marketplace-owner-link">{{ $item->user->name }}</a></p>
                                                <div class="marketplace-meta-footer">
                                                    <span>{{ $ownedCard->is_for_trade ? 'Looking for trade' : ($ownedCard->is_for_sale ? 'Direct sale available' : 'Public showcase') }}</span>
                                                    <span class="marketplace-link">{{ $ownedCard->listing_price ? 'PHP '.number_format((float) $ownedCard->listing_price, 0) : 'View listing' }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <section class="dashboard-card featured-marketplace-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Featured listings</p>
                                <h2>Curated opportunities</h2>
                            </div>
                            <span class="mini-chip">Updated now</span>
                        </div>

                        <div class="marketplace-grid">
                            @forelse ($featuredListings as $item)
                                @php
                                    $card = $item->card;
                                    $ownedCard = $item->userCard;
                                    $listingTags = collect([
                                        $ownedCard->is_for_trade ? 'Trade' : null,
                                        $ownedCard->is_for_sale ? 'Sale' : null,
                                        $ownedCard->is_public ? 'Public' : null,
                                        $card->rarity,
                                    ])->filter()->take(3);
                                    $photoUrl = $storagePhotoUrl($ownedCard->photo_path);
                                @endphp
                                <article class="marketplace-item {{ in_array($item->id, $wishlistMatchedListingIds, true) ? 'marketplace-item-match' : '' }}">
                                    <a href="{{ route('marketplace.cards.show', $item) }}" class="marketplace-item-link">
                                        <div class="marketplace-thumb card-media-ratio {{ $card->thumbnail_style }}">
                                            <img
                                                src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                                alt="{{ $card->title }}"
                                                class="card-media-image"
                                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                            >
                                            <div class="marketplace-tags">
                                                @if (in_array($item->id, $wishlistMatchedListingIds, true))
                                                    <span class="marketplace-match-badge">✦ Matches your wishlist</span>
                                                @endif
                                                @foreach ($listingTags as $tag)
                                                    <span class="collection-pill">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="marketplace-meta">
                                            <h3>{{ $card->title }}</h3>
                                            <p>{{ strtoupper($card->artist) }}</p>
                                            <p>{{ $card->album ?: 'Standalone release' }}</p>
                                            <p>Owner: <a href="{{ route('marketplace.user', $item->user) }}" class="marketplace-owner-link">{{ $item->user->name }}</a></p>
                                            <div class="marketplace-meta-footer">
                                                <span>{{ $ownedCard->is_for_trade ? 'Looking for trade' : ($ownedCard->is_for_sale ? 'Direct sale available' : 'Public showcase') }}</span>
                                                <span class="marketplace-link">{{ $ownedCard->listing_price ? 'PHP '.number_format((float) $ownedCard->listing_price, 0) : 'View listing' }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @empty
                                <div class="collection-empty collection-empty-rich">
                                    <div class="collection-empty-icon" aria-hidden="true">🛍️</div>
                                    <h3>No active listings yet.</h3>
                                    <p>List a card from your collection to make it searchable for buyers and wishlist matches.</p>
                                    <a href="{{ route('marketplace.create') }}" class="dashboard-add-card">
                                        + Create Listing
                                    </a>
                                </div>
                            @endforelse
                        </div>

                        <div class="collection-footer">
                            <p>Showing {{ $featuredListings->count() }} featured listings</p>
                            <div class="collection-pagination">
                                @if ($featuredListings->onFirstPage())
                                    <span class="page-button is-disabled">&lsaquo;</span>
                                @else
                                    <a href="{{ $featuredListings->previousPageUrl() }}" class="page-button">&lsaquo;</a>
                                @endif

                                @foreach ($featuredListings->getUrlRange(1, $featuredListings->lastPage()) as $page => $url)
                                    <a href="{{ $url }}" class="page-button {{ $featuredListings->currentPage() === $page ? 'is-active' : '' }}">{{ $page }}</a>
                                @endforeach

                                @if ($featuredListings->hasMorePages())
                                    <a href="{{ $featuredListings->nextPageUrl() }}" class="page-button">&rsaquo;</a>
                                @else
                                    <span class="page-button is-disabled">&rsaquo;</span>
                                @endif
                            </div>
                        </div>
                    </section>
                </section>
@endsection
