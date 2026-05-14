@extends('layouts.app')

@section('title', 'CardFlow | ' . $catalog['artist'])
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Catalog detail</p>
                        <h1>{{ $catalog['artist'] }}</h1>
                        <p class="dashboard-intro">Live catalog detail built from existing cards, wishlists, and marketplace listings.</p>
                    </div>

                    <div class="dashboard-actions">
                        <a href="{{ route('explorer.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Back to explorer</a>
                    </div>
                </header>

                <section class="stats-grid explorer-stats">
                    <article class="stat-card">
                        <span class="stat-label">Cards</span>
                        <div class="stat-value">{{ $catalog['card_count'] }}</div>
                        <div class="stat-note">photocards in this catalog</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Idols</span>
                        <div class="stat-value">{{ $catalog['idol_count'] }}</div>
                        <div class="stat-note">distinct card names</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Eras</span>
                        <div class="stat-value">{{ $catalog['era_count'] }}</div>
                        <div class="stat-note">albums or editions</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Avg. value</span>
                        <div class="stat-value">{{ $formatMoney($catalog['average_value']) }}</div>
                        <div class="stat-note">{{ $catalog['marketplace_listings'] }} active listings</div>
                    </article>
                </section>

                <section class="explorer-bottom-grid">
                    <article class="dashboard-card explorer-snapshot-card">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Catalog snapshot</p>
                                <h2>Active demand</h2>
                            </div>
                        </div>

                        <div class="catalog-detail-metrics">
                            <div class="catalog-detail-metric">
                                <span class="summary-label">Active wishlists</span>
                                <strong>{{ $catalog['active_wishlists'] }}</strong>
                            </div>
                            <div class="catalog-detail-metric">
                                <span class="summary-label">Marketplace listings</span>
                                <strong>{{ $catalog['marketplace_listings'] }}</strong>
                            </div>
                        </div>

                        <div class="catalog-era-list">
                            @foreach ($eras as $era)
                                <span class="collection-pill">{{ $era }}</span>
                            @endforeach
                        </div>
                    </article>

                    <article class="dashboard-card explorer-quick-picks">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Catalog search</p>
                                <h2>Refine this catalog</h2>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('explorer.catalogs.show', \Illuminate\Support\Str::slug($catalog['artist'])) }}" class="collection-filters explorer-filter-list">
                            <label class="dashboard-search">
                                <span class="sr-only">Search catalog cards</span>
                                <input type="search" name="q" value="{{ $search }}" placeholder="Search cards, idols, eras...">
                            </label>
                            <button type="submit" class="dashboard-search-submit">Search</button>
                        </form>
                    </article>
                </section>

                <section class="dashboard-card explorer-feature-card">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Catalog cards</p>
                            <h2>{{ $cards->total() }} cards in view</h2>
                        </div>
                    </div>

                    <div class="collection-grid">
                        @forelse ($cards as $card)
                            @php
                                $photoPath = \App\Models\UserCard::query()
                                    ->where('card_id', $card->id)
                                    ->whereNotNull('photo_path')
                                    ->latest('updated_at')
                                    ->value('photo_path');

                                $photoUrl = $storagePhotoUrl($photoPath);
                            @endphp

                            <article class="collection-item-card">
                                <div class="marketplace-thumb card-media-ratio {{ $card->thumbnail_style ?: 'market-thumb-one' }}">
                                    <img
                                        src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                        alt="{{ $card->title }}"
                                        class="card-media-image"
                                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                    >
                                    <div class="marketplace-tags">
                                        <span class="collection-pill">{{ $card->rarity }}</span>

                                        @if ($card->active_listings_count > 0)
                                            <span class="collection-pill">{{ $card->active_listings_count }} listings</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="collection-item-copy">
                                    <h3>{{ $card->title }}</h3>
                                    <p>{{ $card->artist }}</p>
                                    <p>{{ $card->album ?: ($card->edition ?: 'Standalone') }}</p>

                                    <div class="collection-meta-row">
                                        <span class="collection-pill">{{ $card->wishlist_items_count }} wishlists</span>
                                        <strong>{{ $formatMoney($card->market_value) }}</strong>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="collection-empty">No catalog cards match this filter yet.</div>
                        @endforelse
                    </div>

                    {{ $cards->links() }}
                </section>
@endsection


