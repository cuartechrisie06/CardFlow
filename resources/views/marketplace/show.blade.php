@extends('layouts.app')

@section('title', 'CardFlow | Listing Details')
@section('body_class', 'dashboard-body card-details-page')

@section('topbar')
@endsection

@section('content')
<x-photocard-detail
    context-label="Marketplace Listing"
    eyebrow="Listing Details"
    :page-title="$userCard->card->title"
    subtitle="Listed by {{ $owner->name }} for marketplace browsing."
    :back-url="route('marketplace.index', ['filter' => $listing->user_id === auth()->id() ? 'my_listings' : 'all'])"
    back-label="Back to Marketplace"
    :image-url="$photoUrl ?: asset('images/placeholder-card.png')"
    :image-alt="$userCard->card->title"
    :rarity-label="$rarityLabel($userCard->card->rarity)"
    :rarity-class="\Illuminate\Support\Str::slug($rarityLabel($userCard->card->rarity))"
    :artist-name="$userCard->card->artist"
    :card-title="$userCard->card->title"
    :primary-meta="[
        ['label' => 'Album', 'value' => $userCard->card->album ?: 'Standalone'],
        ['label' => 'Edition', 'value' => $userCard->card->edition ?: 'Standard'],
        ['label' => 'Rarity', 'value' => $rarityLabel($userCard->card->rarity)],
    ]"
    :secondary-meta="[
        ['label' => 'Condition', 'value' => $userCard->condition],
        ['label' => 'Visibility', 'value' => $userCard->is_public ? 'Public' : 'Listed only'],
        ['label' => 'Listing Type', 'value' => $userCard->is_for_trade ? 'Trade' : ($userCard->is_for_sale ? 'Sale' : 'Showcase')],
    ]"
    :price-tiles="[
        ['label' => 'Market Value', 'value' => 'PHP '.number_format($marketValue, 2)],
        ['label' => 'Purchase Price', 'value' => 'PHP '.number_format($purchasePrice, 2)],
        ['label' => 'Estimated Value', 'value' => 'PHP '.number_format($estimatedValue, 2)],
    ]"
    price-summary-label="Unrealized gain"
    :price-summary-value="($isPositiveDelta ? '+' : '-') . 'PHP ' . number_format(abs($valueDelta), 2)"
    :price-summary-tone="$isPositiveDelta ? 'is-positive' : 'is-negative'"
>
    <x-slot name="actions">
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
    </x-slot>

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
                    @initials($owner->name)
                </div>
            @endif

            <div class="seller-trust-copy">
                <p class="mini-label">Seller</p>
                <h3>{{ $owner->name }}</h3>
                <p>
                    <a href="{{ route('profile.showcase', $owner) }}" class="seller-profile-link">
                        {{ '@'.$owner->username }}
                    </a>
                </p>
            </div>
        </div>

        @if ($listing->proof_photo)
            <section class="proof-status-panel">
                <div class="proof-status-copy">
                    <p class="mini-label">Proof of Possession</p>

                    @if ($listing->proof_status === 'verified')
                        <span class="proof-badge-verified">Proof of Possession Verified</span>
                    @elseif ($listing->proof_status === 'failed')
                        <span class="proof-badge-failed">Verification failed - photo may be edited</span>
                    @else
                        <span class="proof-badge-pending">Verification in progress</span>
                    @endif

                    @if (! is_null($listing->proof_score))
                        <p class="proof-status-score">Confidence score: {{ $listing->proof_score }}/100</p>
                    @endif

                    <p class="proof-status-help">
                        Our team reviews proof photos within 24 hours. Verified listings get a badge.
                    </p>
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
                <span class="seller-trust-badge">{{ $owner->seller_badge }}</span>
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
                    Message seller
                </button>
            </form>
        @endif
    </div>
</x-photocard-detail>

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
