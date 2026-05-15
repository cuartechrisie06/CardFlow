@extends('layouts.app')

@section('title', 'CardFlow | ' . $profileUser->name)
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-card profile-hero">
    <div class="profile-hero-main">
        <div class="profile-avatar-shell">
            @if ($profileUser->avatar_url)
                <div
                    class="profile-avatar profile-avatar-photo"
                    style="background-image: url('{{ $profileUser->avatar_url }}');"
                    aria-label="{{ $profileUser->name }} avatar"
                ></div>
            @else
                <div class="profile-avatar profile-avatar-fallback" aria-hidden="true">
                    @initials($profileUser->name)
                </div>
            @endif

            @if ($isOwnProfile)
                <a href="{{ route('profile.edit') }}" class="mini-chip profile-avatar-action">Upload avatar</a>
            @endif
        </div>

        <div class="profile-hero-copy">
            <div>
                <p class="dashboard-kicker">Profile</p>
                <h1>{{ $profileUser->name }}</h1>
                <p class="profile-username">{{ '@'.($profileUser->username ?: 'collector') }}</p>
            </div>

            <div class="profile-trust-row">
                @if ($profileUser->seller_badge)
                    <span class="seller-trust-badge">✦ {{ $profileUser->seller_badge }}</span>
                @endif
                <span class="mini-chip">{{ $profileUser->completed_trades_count }} completed trade{{ $profileUser->completed_trades_count === 1 ? '' : 's' }}</span>
                <span class="mini-chip">{{ $profileUser->active_listings_count }} active listing{{ $profileUser->active_listings_count === 1 ? '' : 's' }}</span>
                <span class="mini-chip">Member since {{ $profileUser->created_at?->format('M Y') }}</span>
            </div>

            @if ($profileUser->bio)
                <p class="profile-bio">{{ $profileUser->bio }}</p>
            @endif

            <div class="profile-meta-row">
                @if ($profileUser->location)
                    <span class="mini-chip">{{ $profileUser->location }}</span>
                @endif

                @if ($profileUser->website)
                    <a href="{{ $profileUser->website }}" target="_blank" rel="noreferrer" class="mini-chip">
                        {{ preg_replace('#^https?://#', '', $profileUser->website) }}
                    </a>
                @endif
            </div>
        </div>
    </div>

    @if ($isOwnProfile)
        <div class="dashboard-actions">
            <a href="{{ route('profile.edit') }}" class="dashboard-add-card">Edit Profile</a>
            <button
                type="button"
                class="dashboard-add-card dashboard-add-card-secondary js-copy-showcase-link"
                data-showcase-url="{{ route('profile.showcase', $profileUser) }}"
            >
                Share Profile
            </button>
        </div>
    @endif
</header>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-label">Total cards</span>
        <div class="stat-value">{{ $totalCards }}</div>
        <div class="stat-note">{{ $isOwnProfile ? 'in your collection' : 'shared on this profile' }}</div>
    </article>
    <article class="stat-card">
        <span class="stat-label">Collection value</span>
        <div class="stat-value">{{ $formatMoney($totalCollectionValue) }}</div>
        <div class="stat-note">estimated total value</div>
    </article>
    <article class="stat-card">
        <span class="stat-label">Active listings</span>
        <div class="stat-value">{{ $activeListingsCount }}</div>
        <div class="stat-note">currently visible in marketplace</div>
    </article>
    <article class="stat-card">
        <span class="stat-label">Trades completed</span>
        <div class="stat-value">{{ $completedTradesCount }}</div>
        <div class="stat-note">recorded trading history</div>
    </article>
</section>

<div class="profile-tabs">
    <a href="{{ route('profile.show', ['user' => $profileUser->username, 'tab' => 'collection']) }}" class="profile-tab {{ $activeTab === 'collection' ? 'is-active' : '' }}">
        Collection
    </a>
    <a href="{{ route('profile.show', ['user' => $profileUser->username, 'tab' => 'listings']) }}" class="profile-tab {{ $activeTab === 'listings' ? 'is-active' : '' }}">
        Listings
    </a>
    @if ($canViewWishlist)
        <a href="{{ route('profile.show', ['user' => $profileUser->username, 'tab' => 'wishlist']) }}" class="profile-tab {{ $activeTab === 'wishlist' ? 'is-active' : '' }}">
            Wishlist
        </a>
    @endif
</div>

@if ($activeTab === 'collection')
    <section class="dashboard-card profile-tab-panel">
        <div class="section-heading-row">
            <div>
                <p class="dashboard-kicker">Collection</p>
                <h2>{{ $isOwnProfile ? 'Your collection items' : $profileUser->name . '\'s public collection' }}</h2>
                <p class="dashboard-intro">A curated view of the photocards on this profile.</p>
            </div>
        </div>

        <div class="profile-collection-grid">
            @forelse ($collectionCards as $userCard)
                @php
                    $card = $userCard->card;
                    $cardPhotoUrl = $storagePhotoUrl($userCard->photo_path);
                    $cardValue = $userCard->estimated_value ?? $userCard->purchase_price ?? $card?->market_value;
                @endphp
                <article class="profile-collection-card">
                    <a href="{{ route('collection.show', $userCard) }}" class="profile-collection-link">
                        <div class="profile-collection-thumb card-media-ratio {{ $card?->thumbnail_style ?? 'market-thumb-one' }}">
                            <img
                                src="{{ $cardPhotoUrl ?: asset('images/placeholder-card.png') }}"
                                alt="{{ $card?->title ?: 'Photocard image' }}"
                                class="card-media-image"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                            >
                        </div>

                        <div class="profile-collection-body">
                            <h3>{{ $card?->title ?: 'Untitled card' }}</h3>
                            <p>{{ $card?->artist ?: 'Unknown artist' }}</p>
                            <div class="profile-collection-meta">
                                <span>{{ $rarityLabel($card?->rarity) }}</span>
                                <span>{{ $cardValue !== null ? $formatMoney($cardValue) : 'No value set' }}</span>
                            </div>
                        </div>
                    </a>
                </article>
            @empty
                <div class="empty-state">No cards to show on this profile yet.</div>
            @endforelse
        </div>
    </section>
@elseif ($activeTab === 'listings')
    <section class="dashboard-card profile-listings-section profile-tab-panel">
        <div class="section-heading-row">
            <div>
                <p class="dashboard-kicker">Marketplace Listings</p>
                <h2>{{ $isOwnProfile ? 'Your active listings' : $profileUser->name . '\'s active listings' }}</h2>
                <p class="dashboard-intro">Cards currently live in the marketplace.</p>
            </div>

            @if ($isOwnProfile)
                <a href="{{ route('marketplace.create') }}" class="dashboard-add-card">
                    Post new listing
                </a>
            @endif
        </div>

        <div class="profile-listing-grid">
            @forelse ($marketplaceListings as $listing)
                @php
                    $userCard = $listing->userCard;
                    $card = $listing->card ?? $userCard?->card;
                    $photoUrl = $storagePhotoUrl($userCard?->photo_path);
                    $listingType = $userCard?->is_for_trade
                        ? 'Trade'
                        : ($userCard?->is_for_sale ? 'Sale' : 'Showcase');
                @endphp

                <article class="profile-listing-card">
                    <a href="{{ route('marketplace.cards.show', $listing) }}" class="profile-listing-link">
                        <div class="profile-listing-thumb card-media-ratio {{ $card?->thumbnail_style ?? 'market-thumb-one' }}">
                            <img
                                src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                alt="{{ $card?->title ?: 'Listing image' }}"
                                class="card-media-image"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                            >
                        </div>

                        <div class="profile-listing-body">
                            <span class="mini-chip">{{ $listingType }}</span>
                            <h3>{{ $card?->title ?? 'Untitled card' }}</h3>
                            <p>{{ $card?->artist ?? 'Unknown artist' }}</p>

                            <div class="profile-listing-meta">
                                <span>Condition: {{ $userCard->condition ?? 'N/A' }}</span>
                                <span>
                                    {{ $userCard?->listing_price ? $formatMoney($userCard->listing_price) : 'No price set' }}
                                </span>
                            </div>
                        </div>
                    </a>

                    @if (auth()->id() === $listing->user_id)
                        <div class="my-listing-actions">
                            <a href="{{ route('marketplace.edit', $listing) }}" class="my-listing-edit">Edit</a>
                            <button
                                type="button"
                                class="my-listing-delete js-open-delete-modal"
                                data-delete-url="{{ route('marketplace.destroy', $listing) }}"
                            >
                                Delete
                            </button>
                        </div>
                    @endif
                </article>
            @empty
                <div class="empty-state">No active marketplace listings yet.</div>
            @endforelse
        </div>
    </section>
@elseif ($canViewWishlist)
    <section class="dashboard-card profile-tab-panel">
        <div class="section-heading-row">
            <div>
                <p class="dashboard-kicker">Wishlist</p>
                <h2>Your tracked wants</h2>
                <p class="dashboard-intro">Cards you're still hoping to find or trade for.</p>
            </div>
        </div>

        <div class="profile-wishlist-grid">
            @forelse ($wishlistItems as $wishlistItem)
                @php
                    $card = $wishlistItem->card;
                @endphp
                <article class="profile-wishlist-card">
                    <div class="profile-wishlist-copy">
                        <h3>{{ $card?->title ?: 'Wishlist card' }}</h3>
                        <p>{{ $card?->artist ?: 'Unknown artist' }}</p>
                    </div>
                    <div class="profile-wishlist-meta">
                        <span class="mini-chip">{{ ucfirst((string) ($wishlistItem->priority ?: 'normal')) }}</span>
                        <span>{{ $wishlistItem->target_price ? $formatMoney($wishlistItem->target_price) : 'No target price' }}</span>
                    </div>
                </article>
            @empty
                <div class="empty-state">No wishlist items yet.</div>
            @endforelse
        </div>
    </section>
@endif

@if ($isOwnProfile)
    <div class="delete-modal" id="deleteListingModal" aria-hidden="true">
        <div class="delete-modal-backdrop js-close-delete-modal"></div>

        <div
            class="delete-modal-card"
            role="dialog"
            aria-modal="true"
            aria-labelledby="deleteListingTitle"
        >
            <div class="delete-modal-icon">!</div>

            <div>
                <p class="delete-modal-eyebrow">Confirm action</p>
                <h2 id="deleteListingTitle">Remove this listing?</h2>
                <p class="delete-modal-text">
                    This will remove the card from the marketplace only. Your card will stay safely in your collection.
                </p>
            </div>

            <form method="POST" id="deleteListingForm">
                @csrf
                @method('DELETE')

                <div class="delete-modal-actions">
                    <button type="button" class="delete-modal-cancel js-close-delete-modal">Cancel</button>
                    <button type="submit" class="delete-modal-confirm">Yes, remove listing</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modal = document.getElementById('deleteListingModal');
                const form = document.getElementById('deleteListingForm');
                const openButtons = document.querySelectorAll('.js-open-delete-modal');
                const closeButtons = document.querySelectorAll('.js-close-delete-modal');

                if (!modal || !form) {
                    return;
                }

                openButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const deleteUrl = button.getAttribute('data-delete-url');

                        if (!deleteUrl) {
                            return;
                        }

                        form.setAttribute('action', deleteUrl);
                        modal.classList.add('is-open');
                        modal.setAttribute('aria-hidden', 'false');
                    });
                });

                closeButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                        form.removeAttribute('action');
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                        form.removeAttribute('action');
                    }
                });
            });
        </script>
    @endpush
@endif
@endsection

@if ($isOwnProfile)
    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const button = document.querySelector('.js-copy-showcase-link');

        if (!button || !navigator.clipboard) {
            return;
        }

        button.addEventListener('click', async function () {
            const url = button.getAttribute('data-showcase-url');

            if (!url) {
                return;
            }

            try {
                await navigator.clipboard.writeText(url);
                button.textContent = 'Link Copied';

                window.setTimeout(function () {
                    button.textContent = 'Share Profile';
                }, 1800);
            } catch (error) {
                button.textContent = 'Copy Failed';

                window.setTimeout(function () {
                    button.textContent = 'Share Profile';
                }, 1800);
            }
        });
    });
    </script>
    @endpush
@endif
