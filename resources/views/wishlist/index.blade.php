@extends('layouts.app')

@section('title', 'CardFlow | Wishlist')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
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
                        <button type="button" id="add-wishlist-btn" class="dashboard-add-card">+ Add to Wishlist</button>
                    </div>
                </header>

                <section class="wishlist-page-grid">
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
                                @endphp
                                <div class="wishlist-row">
                                    <div class="wishlist-row-copy">
                                        <strong>{{ $item->card->title }}</strong>
                                        <p>{{ strtoupper($item->card->artist) }} • {{ strtoupper($item->card->album ?? 'WISHLIST') }}</p>
                                    </div>
                                    <div class="wishlist-row-actions">
                                        <span class="mini-chip">{{ $priorityLabel($item->priority) }}</span>
                                        <span class="wishlist-match-chip">{{ $matches->isNotEmpty() ? $matches->count().' matches' : 'No match yet' }}</span>
                                        <form action="{{ route('wishlist.destroy', $item) }}" method="POST" class="wishlist-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="wishlist-remove-button" onclick="openDeleteModal('{{ $item->card->title ?? 'this card' }}', this)">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="collection-empty collection-empty-rich">
                                    <div class="collection-empty-icon" aria-hidden="true">⭐</div>
                                    <h3>Add a card you are actively hunting.</h3>
                                    <p>CardFlow matches by artist, album, title, and edition when other collectors publish listings.</p>
                                    <button type="button" id="empty-add-wishlist-btn" class="dashboard-add-card">
                                        + Add to Wishlist
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </article>

                    <article class="wishlist-matches-panel">
                        <div class="wishlist-panel-top">
                            <div>
                                <p class="mini-label">Active matches</p>
                                <h2>{{ $activeMatches->isNotEmpty() ? 'Real marketplace matches' : 'No live matches yet' }}</h2>
                            </div>
                            <span class="mini-chip">{{ $activeMatches->isNotEmpty() ? 'Live matching' : 'Waiting' }}</span>
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
                                            $photoUrl = $storagePhotoUrl($ownedCard->photo_path);
                                        @endphp
                                        <div class="match-card">
                                            <img
                                                src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                                alt="{{ $listedCard->title }}"
                                                class="match-card-image"
                                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                            >

                                            <div class="match-card-info">
                                                <div class="wishlist-match-meta">
                                                    <span class="mini-chip">{{ '@'.$owner->username }}</span>
                                                    <span class="mini-chip {{ $ownedCard->is_for_trade ? 'wishlist-trade-badge' : '' }}">{{ $ownedCard->is_for_sale ? 'For sale' : ($ownedCard->is_for_trade ? 'Open for trade' : 'Public listing') }}</span>
                                                </div>

                                                <strong>{{ $listedCard->title }}</strong>
                                                <p class="match-card-subtitle">{{ strtoupper($listedCard->artist) }} • {{ strtoupper($listedCard->album ?: 'Standalone release') }}</p>
                                                <p class="match-card-price">{{ $ownedCard->listing_price ? 'PHP '.number_format((float) $ownedCard->listing_price, 0) : ($ownedCard->is_for_trade ? 'Trade listing' : 'Public showcase') }}</p>

                                                <a href="{{ route('marketplace.cards.show', $listing) }}" class="wishlist-view-listing-button">View listing</a>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <div class="collection-empty collection-empty-rich">
                                <div class="collection-empty-icon" aria-hidden="true">🪄</div>
                                <h3>No live matches yet.</h3>
                                <p>You’ll see matches here when another collector lists a similar artist, album, title, or edition.</p>
                                <a href="{{ route('marketplace.index') }}" class="dashboard-add-card">
                                    Browse Marketplace
                                </a>
                            </div>
                        @endif
                    </article>
                </section>

                <div id="wishlist-modal" class="modal-overlay hidden" aria-hidden="true">
                    <div class="modal-backdrop" data-wishlist-close></div>
                    <div class="modal-box wishlist-modal-box" role="dialog" aria-modal="true" aria-labelledby="wishlistModalTitle">
                        <div class="card-topline wishlist-modal-header">
                            <div>
                                <p class="mini-label">Add wishlist item</p>
                                <h2 id="wishlistModalTitle">Track a wanted photocard</h2>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('wishlist.store') }}" class="card-create-form wishlist-modal-form">
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
                                    <small class="field-hint">High = actively searching. Medium = interested. Low = watching.</small>
                                    @error('priority') <small class="field-error">{{ $message }}</small> @enderror
                                </label>

                                <label class="field-group">
                                    <span>Target Price</span>
                                    <input type="number" name="target_price" value="{{ old('target_price') }}" min="0" step="0.01" placeholder="1200">
                                    @error('target_price') <small class="field-error">{{ $message }}</small> @enderror
                                </label>
                            </div>

                            <div class="create-form-actions wishlist-modal-actions">
                                <button type="button" id="wishlist-cancel" class="dashboard-add-card dashboard-add-card-secondary">Cancel</button>
                                <button type="submit" class="dashboard-add-card">Save to Wishlist</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="wishlistDeleteModal" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.3); align-items:center; justify-content:center; z-index:9999;">
                    <div style="background:#fff8f3; padding:2rem; border-radius:1rem; max-width:400px; width:90%; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.15);">
                        <div style="font-size:1.5rem; color:#c75e3e; margin-bottom:0.5rem;">&#x26A0;</div>
                        <h5 style="letter-spacing:1px; font-size:0.8rem; color:#c75e3e; margin-bottom:0.5rem;">CONFIRM REMOVE</h5>
                        <h3 id="wishlistDeleteMessage" style="margin:0.5rem 0; font-weight:500;">Remove this item from your wishlist?</h3>
                        <p style="color:#555; font-size:0.9rem; margin-bottom:1.5rem;">
                            This will permanently remove the card from your wishlist. This action cannot be undone.
                        </p>
                        <button id="cancelWishlistDelete" style="margin-right:1rem; padding:0.5rem 1rem; border:none; border-radius:0.5rem; background:#eee;">Cancel</button>
                        <button id="confirmWishlistDelete" style="padding:0.5rem 1rem; border:none; border-radius:0.5rem; background:#c75e3e; color:white;">Yes, remove card</button>
                    </div>
                </div>
<script>
let currentWishlistForm;

function openDeleteModal(cardName, button) {
    currentWishlistForm = button.closest('form');
    document.getElementById('wishlistDeleteMessage').innerText =
        `Remove "${cardName}" from your wishlist?`;
    document.getElementById('wishlistDeleteModal').style.display = 'flex';
}

document.getElementById('cancelWishlistDelete').onclick = function() {
    document.getElementById('wishlistDeleteModal').style.display = 'none';
    currentWishlistForm = null;
};

document.getElementById('confirmWishlistDelete').onclick = function() {
    if (currentWishlistForm) currentWishlistForm.submit();
};

document.addEventListener('DOMContentLoaded', function () {
    const wishlistModal = document.getElementById('wishlist-modal');
    const openButtons = [
        document.getElementById('add-wishlist-btn'),
        document.getElementById('empty-add-wishlist-btn'),
    ].filter(Boolean);
    const cancelButton = document.getElementById('wishlist-cancel');
    const closeTargets = document.querySelectorAll('[data-wishlist-close]');

    if (!wishlistModal) {
        return;
    }

    openButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            wishlistModal.classList.remove('hidden');
            wishlistModal.setAttribute('aria-hidden', 'false');
        });
    });

    if (cancelButton) {
        cancelButton.addEventListener('click', function () {
            wishlistModal.classList.add('hidden');
            wishlistModal.setAttribute('aria-hidden', 'true');
        });
    }

    closeTargets.forEach(function (target) {
        target.addEventListener('click', function () {
            wishlistModal.classList.add('hidden');
            wishlistModal.setAttribute('aria-hidden', 'true');
        });
    });

    @if ($errors->has('artist') || $errors->has('title') || $errors->has('album') || $errors->has('priority') || $errors->has('target_price'))
        wishlistModal.classList.remove('hidden');
        wishlistModal.setAttribute('aria-hidden', 'false');
    @endif
});
</script>
@endsection
