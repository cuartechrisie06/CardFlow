@extends('layouts.app')

@section('title', 'CardFlow | Listing Details')
@section('body_class', 'dashboard-body card-details-page')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header marketplace-header card-details-header">
    <div>
        <p class="dashboard-kicker">Marketplace Listing</p>
        <p class="card-details-eyebrow">Listing Details</p>
        <div class="card-title-with-edit">
            <h1>{{ $userCard->card->title }}</h1>

            @if ($listing->user_id === auth()->id())
                <a
                    href="{{ route('marketplace.edit', $listing) }}"
                    class="card-title-edit-icon"
                    aria-label="Edit listing"
                    title="Edit listing"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                </a>
            @endif
        </div>
        <p class="dashboard-intro">
            Listed by {{ $owner->name }} for marketplace browsing.
        </p>
    </div>

    <div class="dashboard-actions card-details-actions">
        <a
            href="{{ route('marketplace.index', ['filter' => $listing->user_id === auth()->id() ? 'my_listings' : 'all']) }}"
            class="card-details-back-button"
        >
            Back to Marketplace
        </a>
    </div>
</header>

<section class="dashboard-card card-detail-shell card-detail-shell-premium">
    <div class="card-detail-media-column">
        <div class="card-detail-media-frame rarity-{{ \Illuminate\Support\Str::slug($rarityLabel($userCard->card->rarity)) }}">
            <div class="card-detail-media card-detail-media-premium {{ $userCard->card->thumbnail_style }}">
                <img
                    src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                    alt="{{ $userCard->card->title }}"
                    class="card-detail-media-image"
                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                >
                <div class="card-detail-media-overlay"></div>
                <span class="card-detail-rarity-badge">🌟 {{ $rarityLabel($userCard->card->rarity) }}</span>
            </div>
        </div>
    </div>

    <div class="card-detail-copy card-detail-copy-premium">
        <section class="card-detail-hero card-detail-fade" style="--card-detail-delay: 0ms;">
            <p class="mini-label">Artist / Group</p>
            <h2>{{ $userCard->card->artist }}</h2>
            <p class="card-detail-title-display">{{ $userCard->card->title }}</p>
        </section>

        <div class="card-detail-divider"></div>

        <section class="card-detail-chip-grid card-detail-fade" style="--card-detail-delay: 50ms;">
            <article class="card-detail-chip">
                <span class="summary-label">Album</span>
                <strong>{{ $userCard->card->album ?: 'Standalone' }}</strong>
            </article>
            <article class="card-detail-chip">
                <span class="summary-label">Edition</span>
                <strong>{{ $userCard->card->edition ?: 'Standard' }}</strong>
            </article>
            <article class="card-detail-chip">
                <span class="summary-label">Rarity</span>
                <strong>{{ $rarityLabel($userCard->card->rarity) }}</strong>
            </article>
        </section>

        <section class="card-detail-support-grid card-detail-fade" style="--card-detail-delay: 100ms;">
            <article class="card-detail-chip">
                <span class="summary-label">Condition</span>
                <strong>{{ $userCard->condition }}</strong>
            </article>
            <article class="card-detail-chip">
                <span class="summary-label">Visibility</span>
                <strong>{{ $userCard->is_public ? 'Public' : 'Listed only' }}</strong>
            </article>
            <article class="card-detail-chip">
                <span class="summary-label">Listing</span>
                <strong>{{ $userCard->is_for_trade ? 'Trade' : ($userCard->is_for_sale ? 'Sale' : 'Showcase') }}</strong>
            </article>
        </section>

        <div class="card-detail-divider"></div>

        <section class="card-financial-summary card-detail-fade" style="--card-detail-delay: 150ms;">
            <div class="card-financial-grid">
                <article class="card-financial-chip">
                    <span class="summary-label">Market Value</span>
                    <strong>PHP {{ number_format($marketValue, 2) }}</strong>
                </article>
                <article class="card-financial-chip">
                    <span class="summary-label">Purchase Price</span>
                    <strong>PHP {{ number_format($purchasePrice, 2) }}</strong>
                </article>
                <article class="card-financial-chip">
                    <span class="summary-label">Estimated Value</span>
                    <strong>PHP {{ number_format($estimatedValue, 2) }}</strong>
                </article>
            </div>

            <div class="card-detail-profit {{ $isPositiveDelta ? 'is-positive' : 'is-negative' }}">
                <span class="summary-label">Net change</span>
                <strong>{{ $isPositiveDelta ? '+' : '-' }}PHP {{ number_format(abs($valueDelta), 2) }}</strong>
            </div>
        </section>

        @if ($userCard->notes)
            <section class="dashboard-card card-note-shell card-detail-fade" style="--card-detail-delay: 200ms;">
                <p class="mini-label">Owner note</p>
                <p>{{ $userCard->notes }}</p>
            </section>
        @endif

        <section class="dashboard-card seller-trust-card card-detail-fade" style="--card-detail-delay: 225ms;">
            @php
                $proofPhotoUrl = $storagePhotoUrl($listing->proof_photo);
            @endphp

            <div class="seller-trust-header">
                @if ($owner->avatar_url)
                    <img
                        src="{{ $owner->avatar_url }}"
                        alt="{{ $owner->name }} avatar"
                        class="seller-trust-avatar"
                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                    >
                @else
                    <div class="seller-trust-avatar seller-trust-avatar-fallback" aria-hidden="true">
                        {{ $owner->initials }}
                    </div>
                @endif

                <div class="seller-trust-copy">
                    <p class="mini-label">Seller</p>
                    <h3>{{ $owner->name }}</h3>
                    <p>{{ '@'.$owner->username }}</p>
                </div>
            </div>

            @if ($listing->proof_photo)
                <section class="proof-status-panel">
                    <div class="proof-status-copy">
                        <p class="mini-label">Proof of Possession</p>

                        @if ($listing->proof_status === 'verified')
                            <span class="proof-badge-verified">✅ Proof of Possession Verified</span>
                        @elseif ($listing->proof_status === 'failed')
                            <span class="proof-badge-failed">❌ Verification failed - photo may be edited</span>
                        @else
                            <span class="proof-badge-pending">⏳ Verification in progress</span>
                        @endif

                        @if (! is_null($listing->proof_score))
                            <p class="proof-status-score">Confidence score: {{ $listing->proof_score }}/100</p>
                        @endif
                    </div>

                    @if ($proofPhotoUrl)
                        <div class="proof-status-preview">
                            <img
                                src="{{ $proofPhotoUrl }}"
                                alt="Proof of possession photo"
                                class="card-photo-preview"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                            >
                        </div>
                    @endif
                </section>
            @endif

            <div class="seller-trust-meta">
                @if ($owner->seller_badge)
                    <span class="seller-trust-badge">✦ {{ $owner->seller_badge }}</span>
                @endif
                <span class="mini-chip">{{ $owner->completed_trades_count }} trade{{ $owner->completed_trades_count === 1 ? '' : 's' }}</span>
                <span class="mini-chip">{{ $owner->active_listings_count }} listing{{ $owner->active_listings_count === 1 ? '' : 's' }}</span>
                <span class="mini-chip">Member since {{ $owner->created_at?->format('M Y') }}</span>
            </div>
        </section>

        <div class="card-detail-secondary-actions card-detail-fade" style="--card-detail-delay: 250ms;">
            <a
                href="{{ route('marketplace.user', $owner) }}"
                class="dashboard-add-card dashboard-add-card-secondary"
            >
                View {{ $owner->name }}'s listings
            </a>

            @if ($viewer->id !== $owner->id)
                <form
                    action="{{ route('messages.listings.store', $listing) }}"
                    method="POST"
                    class="dashboard-inline-form"
                >
                    @csrf
                    <button type="submit" class="dashboard-add-card">
                        Message Seller
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>

<div class="card-detail-fab-wrap">
    @if ($listing->user_id === auth()->id())
        <button
            type="button"
            class="card-detail-fab js-open-delete-modal"
            data-delete-url="{{ route('marketplace.destroy', $listing) }}"
            aria-label="Delete listing"
            title="Delete listing"
        >
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 6h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M8 6V4h8v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M6 6l1 14h10l1-14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M14 11v5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    @else
        <a
            href="{{ route('marketplace.user', $owner) }}"
            class="card-detail-fab"
            aria-label="More options"
            title="View collector"
        >
            ...
        </a>
    @endif
</div>

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
                <button type="button" class="delete-modal-cancel js-close-delete-modal">
                    Cancel
                </button>

                <button type="submit" class="delete-modal-confirm">
                    Yes, remove listing
                </button>
            </div>
        </form>
    </div>
</div>

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
@endsection
