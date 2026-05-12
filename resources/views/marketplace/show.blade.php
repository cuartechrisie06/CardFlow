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
                        <h1>{{ $userCard->card->title }}</h1>
                        <p class="dashboard-intro">
                            Listed by {{ $owner->name }} for marketplace browsing.
                        </p>
                    </div>

                    <div class="dashboard-actions card-details-actions">
                        <a
                            href="{{ route('marketplace.index', ['filter' => $listing->user_id === auth()->id() ? 'my_listings' : 'all']) }}"
                            class="card-details-back-button"
                        >
                            ← Back to Marketplace
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
                            {{-- Proof of Possession Upload --}}
                            @php
                                $proofCard = null;

                                if (isset($userCard)) {
                                    $proofCard = $userCard;
                                } elseif (isset($listing) && $listing->userCard) {
                                    $proofCard = $listing->userCard;
                                } elseif (isset($marketplaceListing) && $marketplaceListing->userCard) {
                                    $proofCard = $marketplaceListing->userCard;
                                }
                            @endphp

                            <div class="proof-possession mt-4 p-3 border rounded-md bg-gray-50">
                                <h3 class="font-semibold mb-2">Proof of Possession</h3>

                                @if ($proofCard && $proofCard->proof_image)
                                    <img 
                                        src="{{ asset('storage/' . $proofCard->proof_image) }}" 
                                        alt="Proof Image" 
                                        class="w-32 h-32 object-cover rounded-md mb-3"
                                    >

                                    @if ($proofCard->proof_verified)
                                        <p class="text-green-700 font-semibold">✅ Status: Verified</p>
                                    @else
                                        <p class="text-yellow-700 font-semibold">⏳ Status: Pending Verification</p>
                                    @endif

                                    <p>
                                        Uploaded at:
                                        {{ $proofCard->proof_uploaded_at ? $proofCard->proof_uploaded_at->format('M d, Y h:i A') : 'N/A' }}
                                    </p>
                                @else
                                    <p>No proof of possession uploaded.</p>
                                @endif
                            </div>

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
                    aria-label="Manage listing"
                    title="Manage listing"
                >
                    ✏
                </button>
            @else
                <a
                    href="{{ route('marketplace.user', $owner) }}"
                    class="card-detail-fab"
                    aria-label="More options"
                    title="View collector"
                >
                    ⋯
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
                @if(isset($card) && $card->proof_image)
                <div class="proof-section p-3 border rounded-md bg-gray-50 mb-4">
                    <h3 class="font-semibold mb-2">Proof of Possession</h3>
                    <img src="{{ asset('storage/' . $card->proof_image) }}" alt="Proof Image" class="w-full rounded-md mb-2">
                    <p class="text-sm text-gray-500">
                        Uploaded at: {{ $card->proof_timestamp ?? 'Unknown' }}
                        @if(isset($card->proof_verified))
                            - Status: {{ $card->proof_verified ? 'Verified' : 'Pending' }}
                        @endif
                    </p>
                </div>
            @endif
            
@endsection


