@extends('layouts.app')

@section('title', 'CardFlow | ' . $profileUser->name . ' Showcase')
@section('body_class', 'dashboard-body showcase-page')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-card profile-hero showcase-hero">
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
        </div>

        <div class="profile-hero-copy">
            <div>
                <p class="dashboard-kicker">Collector Showcase</p>
                <h1>{{ $profileUser->name }}</h1>
                <p class="profile-username">{{ '@'.($profileUser->username ?: 'collector') }}</p>
            </div>

            <div class="profile-trust-row">
                @if ($profileUser->seller_badge)
                    <span class="seller-trust-badge">✦ {{ $profileUser->seller_badge }}</span>
                @endif
                <span class="mini-chip">Member since {{ $profileUser->created_at?->format('M Y') }}</span>
            </div>

            @if ($profileUser->bio)
                <p class="profile-bio">{{ $profileUser->bio }}</p>
            @endif
        </div>
    </div>

    <div class="dashboard-actions showcase-actions">
        @auth
            @if ($viewer && $viewer->id !== $profileUser->id)
                <a
                    href="{{ route('messages.create', ['recipient_id' => $profileUser->id]) }}"
                    class="dashboard-add-card"
                >
                    Message this collector
                </a>
            @endif
        @endauth
    </div>
</header>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-label">Public cards</span>
        <div class="stat-value">{{ $totalPublicCards }}</div>
        <div class="stat-note">visible in this showcase</div>
    </article>
    <article class="stat-card">
        <span class="stat-label">Active listings</span>
        <div class="stat-value">{{ $activeListingsCount }}</div>
        <div class="stat-note">live in the marketplace</div>
    </article>
    <article class="stat-card">
        <span class="stat-label">Completed trades</span>
        <div class="stat-value">{{ $completedTradesCount }}</div>
        <div class="stat-note">recorded collector history</div>
    </article>
    <article class="stat-card">
        <span class="stat-label">Member since</span>
        <div class="stat-value">{{ $profileUser->created_at?->format('M Y') }}</div>
        <div class="stat-note">part of CardFlow</div>
    </article>
</section>

<section class="dashboard-card profile-tab-panel showcase-section">
    <div class="section-heading-row">
        <div>
            <p class="dashboard-kicker">Collection</p>
            <h2>{{ $profileUser->name }}'s public collection</h2>
            <p class="dashboard-intro">A shareable view of this collector's public photocards.</p>
        </div>
    </div>

    <div class="profile-collection-grid">
        @forelse ($publicCards as $userCard)
            @php
                $card = $userCard->card;
                $photoUrl = $storagePhotoUrl($userCard->photo_path);
            @endphp
            <article class="profile-collection-card showcase-card">
                <div class="profile-collection-thumb card-media-ratio {{ $card?->thumbnail_style ?? 'market-thumb-one' }}">
                    <img
                        src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                        alt="{{ $card?->title ?: 'Photocard image' }}"
                        class="card-media-image"
                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                    >
                </div>

                <div class="profile-collection-body">
                    <div class="showcase-card-topline">
                        <h3>{{ $card?->title ?: 'Untitled card' }}</h3>
                        <span class="showcase-rarity-badge">{{ $rarityLabel($card?->rarity) }}</span>
                    </div>
                    <p>{{ $card?->artist ?: 'Unknown artist' }}</p>
                </div>
            </article>
        @empty
            <div class="empty-state">No public cards to show yet.</div>
        @endforelse
    </div>
</section>

<section class="dashboard-card profile-listings-section showcase-section">
    <div class="section-heading-row">
        <div>
            <p class="dashboard-kicker">Marketplace Listings</p>
            <h2>Available listings</h2>
            <p class="dashboard-intro">Cards currently live in the marketplace.</p>
        </div>
    </div>

    <div class="profile-listing-grid">
        @forelse ($activeListings as $listing)
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
                            <span>{{ $rarityLabel($card?->rarity) }}</span>
                            <span>{{ $userCard?->listing_price ? $formatMoney($userCard->listing_price) : 'Message for details' }}</span>
                        </div>
                    </div>
                </a>
            </article>
        @empty
            <div class="empty-state">No active listings yet.</div>
        @endforelse
    </div>
</section>
        <form action="{{ route('cards.uploadProof', $card->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <label for="proof" class="block mb-1 font-medium text-gray-700">
                Upload Proof of Possession
            </label>

            <input type="file" name="proof" id="proof" accept="image/*" class="border p-2 rounded-md w-full">

            @error('proof')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror

            <button type="submit" class="mt-3 px-4 py-2 bg-[#b5651d] text-white rounded-md hover:bg-[#a0541a]">
                Upload
            </button>
        </form>
@endsection
