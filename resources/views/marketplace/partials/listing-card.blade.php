@php
    $card = $item->card;
    $ownedCard = $item->userCard;
    $photoUrl = $storagePhotoUrl($ownedCard?->photo_path) ?: $card?->photo_url;
    $listingTypeLabel = $ownedCard?->is_for_trade
        ? 'Looking for trade'
        : ($ownedCard?->is_for_sale ? 'For sale' : 'Public');
    $priceLabel = $ownedCard?->listing_price
        ? 'PHP '.number_format((float) $ownedCard->listing_price, 2)
        : 'View listing';
    $rarityClass = $card?->rarity ? 'badge-'.\Illuminate\Support\Str::slug(strtolower($card->rarity)) : '';
@endphp

<article class="listing-card {{ $isWishlistMatch ? 'marketplace-item-match' : '' }}">
    <a href="{{ route('marketplace.cards.show', $item) }}" class="listing-card-link" style="text-decoration:none;">
        <div class="listing-card-image-wrapper">
            <img
                src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                alt="{{ $card?->title ?: 'Marketplace listing' }}"
                class="listing-card-image"
                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
            >

            @if($isWishlistMatch)
                <span class="listing-card-badge badge-public">Match</span>
            @elseif($ownedCard?->is_public)
                <span class="listing-card-badge badge-public">Public</span>
            @endif

            @if($card?->rarity)
                <span class="listing-card-badge listing-card-badge-secondary {{ $rarityClass }}">{{ $card->rarity }}</span>
            @endif

            @if($item->proof_verified)
                <span style="position:absolute;bottom:8px;right:8px;background:rgba(45,106,79,0.9);color:#ffffff;font-family:'DM Sans',sans-serif;font-size:0.6rem;font-weight:600;padding:3px 8px;border-radius:20px;backdrop-filter:blur(2px);">
                    Verified
                </span>
            @endif
        </div>

        <div class="listing-card-body">
            <p class="listing-card-artist">{{ $card?->artist ?: 'Unknown artist' }}</p>
            <h3 class="listing-card-title">{{ $card?->title ?: 'Marketplace listing' }}</h3>
            <p class="listing-card-album">{{ $card?->album ?: 'Standalone release' }}</p>
            <p class="listing-card-owner">
                Owner:
                @if($item->user?->username)
                    <span
                        class="collector-profile-link profile-click-target"
                        role="link"
                        tabindex="0"
                        onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('profile.showcase', $item->user) }}';"
                        onkeydown="if(event.key === 'Enter'){ event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('profile.showcase', $item->user) }}'; }"
                    >
                        <strong>{{ $item->user->name ?: 'Collector' }}</strong>
                    </span>
                    @if($item->user->seller_badge)
                        <span class="seller-trust-badge listing-owner-trust-badge">{{ $item->user->seller_badge }}</span>
                    @endif
                @else
                    <strong>Collector</strong>
                @endif
            </p>

            <div class="listing-card-footer">
                <span class="listing-type-badge">{{ $listingTypeLabel }}</span>
                <span class="listing-price">{{ $priceLabel }}</span>
            </div>
        </div>
    </a>
</article>
