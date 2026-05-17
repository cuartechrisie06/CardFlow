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
        <div class="seller-trust-header">
            <a href="{{ route('profile.showcase', $owner) }}" class="seller-avatar-link" aria-label="View {{ $owner->name }} profile">
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
            </a>

            <div class="seller-trust-copy">
                <p class="mini-label">Seller</p>
                <h3>
                    <a href="{{ route('profile.showcase', $owner) }}" class="collector-profile-link">
                        {{ $owner->name }}
                    </a>
                </h3>
                <p>
                    <a href="{{ route('profile.showcase', $owner) }}" class="seller-profile-link">
                        {{ '@'.$owner->username }}
                    </a>
                </p>
            </div>
        </div>

        @if ($listing->proof_photo)
            <section style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:{{ $listing->proof_verified ? '#d4edda' : '#f5e6d8' }};border-radius:12px;margin-bottom:16px;border:1px solid {{ $listing->proof_verified ? '#a8d5b5' : '#e8d5c0' }};">
                <span style="font-size:1.3rem;flex-shrink:0;">
                    {{ $listing->proof_verified ? '✓' : '...' }}
                </span>

                <div style="flex:1;min-width:0;">
                    <p style="font-family:'DM Sans',sans-serif;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:{{ $listing->proof_verified ? '#2d6a4f' : '#8B4513' }};margin:0 0 2px;">
                        {{ $listing->proof_verified ? 'Proof of Possession - Verified' : 'Proof of Possession - Pending' }}
                    </p>
                    <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:{{ $listing->proof_verified ? '#2d6a4f' : '#8B6F5E' }};margin:0;">
                        {{ $listing->proof_verified ? 'Seller has verified physical possession of this card.' : 'Verification is being reviewed.' }}
                    </p>
                </div>

                @if ($listing->proof_photo_url)
                    <a href="{{ $listing->proof_photo_url }}"
                       target="_blank"
                       style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#8B4513;text-decoration:none;border:1px solid #d4b896;padding:5px 12px;border-radius:20px;flex-shrink:0;background:#ffffff;">
                        View proof
                    </a>
                @endif
            </section>
        @else
            <section style="display:flex;align-items:center;gap:10px;padding:12px 16px;background:#fdf6f0;border-radius:12px;margin-bottom:16px;border:1px solid #e8d5c0;">
                <span style="font-size:1.2rem;flex-shrink:0;">!</span>
                <p style="font-family:'DM Sans',sans-serif;font-size:0.8rem;color:#b09070;margin:0;">
                    No proof of possession uploaded. Trade with caution.
                </p>
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
            <button
                type="button"
                class="dashboard-add-card dashboard-add-card-secondary"
                onclick="document.getElementById('tradeRequestModal')?.classList.add('is-open')"
            >
                Request trade
            </button>

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

    @if ($tradeRequests->isNotEmpty())
        <section class="dashboard-card trade-request-panel card-detail-fade" style="--card-detail-delay: 275ms;">
            <div class="card-topline">
                <div>
                    <p class="mini-label">Trade Requests</p>
                    <h2>{{ $listing->user_id === auth()->id() ? 'Requests for this listing' : 'Your request status' }}</h2>
                </div>
                <span class="mini-chip">{{ $tradeRequests->count() }} total</span>
            </div>

            <div class="trade-request-list">
                @foreach ($tradeRequests as $tradeRequest)
                    @php
                        $isReceiver = $tradeRequest->receiver_id === auth()->id();
                        $otherUser = $isReceiver ? $tradeRequest->sender : $tradeRequest->receiver;
                    @endphp
                    <article class="trade-request-item">
                        <div>
                            <p class="mini-label">{{ ucfirst($tradeRequest->status) }}</p>
                            <h3>{{ $tradeRequest->offeredCard?->title ?: 'Offered card' }}</h3>
                            <p>
                                {{ $isReceiver ? 'From' : 'To' }}
                                <strong>{{ '@'.($otherUser?->username ?: $otherUser?->name ?: 'collector') }}</strong>
                            </p>
                            @if($tradeRequest->message)
                                <p class="trade-request-message">{{ $tradeRequest->message }}</p>
                            @endif
                        </div>

                        <div class="trade-request-actions">
                            @if($isReceiver && $tradeRequest->status === 'pending')
                                <form method="POST" action="{{ route('trade-requests.accept', $tradeRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dashboard-add-card">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('trade-requests.decline', $tradeRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dashboard-search-submit">Decline</button>
                                </form>
                            @elseif($tradeRequest->status === 'accepted')
                                <form method="POST" action="{{ route('trade-requests.complete', $tradeRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dashboard-add-card">Mark completed</button>
                                </form>
                            @elseif(!$isReceiver && $tradeRequest->status === 'pending')
                                <form method="POST" action="{{ route('trade-requests.cancel', $tradeRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="dashboard-search-submit">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($viewer->id !== $owner->id)
        <div class="delete-modal" id="tradeRequestModal" aria-hidden="true">
            <div class="delete-modal-backdrop" onclick="document.getElementById('tradeRequestModal')?.classList.remove('is-open')"></div>

            <div class="delete-modal-card trade-request-modal-card" role="dialog" aria-modal="true" aria-labelledby="tradeRequestTitle">
                <div class="delete-modal-icon">T</div>

                <div>
                    <p class="delete-modal-eyebrow">Trade request</p>
                    <h2 id="tradeRequestTitle">Offer one of your cards</h2>
                    <p class="delete-modal-text">
                        Choose a card from your collection to propose for {{ $userCard->card->title }}.
                    </p>
                </div>

                <form method="POST" action="{{ route('trade-requests.store') }}" class="trade-request-form">
                    @csrf
                    <input type="hidden" name="listing_id" value="{{ $listing->id }}">

                    <label class="form-field">
                        <span>Your offered card</span>
                        <select name="offered_card_id" required>
                            <option value="">Choose a card...</option>
                            @foreach($myTradeCards as $myCard)
                                <option value="{{ $myCard->card_id }}">
                                    {{ $myCard->card?->title }} - {{ $myCard->card?->artist }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="form-field">
                        <span>Message</span>
                        <textarea name="message" rows="4" maxlength="500" placeholder="Add condition notes, inclusions, or trade preferences..."></textarea>
                    </label>

                    @if($myTradeCards->isEmpty())
                        <p class="field-help">Add a card to your collection before requesting a trade.</p>
                    @endif

                    <div class="delete-modal-actions">
                        <button type="button" class="delete-modal-cancel" onclick="document.getElementById('tradeRequestModal')?.classList.remove('is-open')">
                            Cancel
                        </button>
                        <button type="submit" class="delete-modal-confirm" @disabled($myTradeCards->isEmpty())>
                            Send trade request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
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
