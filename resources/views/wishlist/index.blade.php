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
                        <a href="#wishlist-add-form" class="dashboard-add-card">+ Add to Wishlist</a>
                    </div>
                </header>

                @if (session('status'))
                    <div class="auth-status">{{ session('status') }}</div>
                @endif

                <section class="wishlist-layout">
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
                                    $topMatch = $matches->first();
                                @endphp
                                <div class="wishlist-row">
                                    <div>
                                        <strong>{{ $item->card->title }}</strong>
                                        <p>{{ strtoupper($item->card->artist) }} • {{ strtoupper($item->card->album ?? 'WISHLIST') }}</p>
                                    </div>
                                    <div class="wishlist-row-actions">
                                        <span class="mini-chip">{{ $priorityLabel($item->priority) }}</span>
                                        <span class="wishlist-match-chip">{{ $matches->isNotEmpty() ? $matches->count().' matches' : 'No match yet' }}</span>
                                        <form action="{{ route('wishlist.destroy', $item) }}" method="POST" class="wishlist-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="wishlist-remove-button" onclick="openDeleteModal('{{ $item->title ?? 'this card' }}', this)">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @if ($topMatch)
                                    @php
                                        $topListing = $topMatch['listing'];
                                    @endphp
                                    <div class="wishlist-row-match-preview">
                                        <span>{{ $topListing->card->title }}</span>
                                        <a href="{{ route('marketplace.cards.show', $topListing) }}" class="wishlist-inline-link">View listing from {{ '@'.$topListing->user->username }}</a>
                                    </div>
                                @endif
                            @empty
                                <div class="collection-empty collection-empty-rich">
                                    <div class="collection-empty-icon" aria-hidden="true">⭐</div>
                                    <h3>Your wishlist is empty.</h3>
                                    <p>Add cards you're looking for.</p>
                                    <a href="#wishlist-add-form" class="dashboard-add-card">
                                        + Add to Wishlist
                                    </a>
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
                            <span class="mini-chip">{{ $activeMatches->isNotEmpty() ? 'Live matches' : 'Waiting' }}</span>
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
                                        <div class="wishlist-match-card">
                                            <div class="wishlist-match-meta">
                                                <span class="mini-chip">{{ '@'.$owner->username }}</span>
                                                <span class="mini-chip">{{ $ownedCard->is_for_sale ? 'For sale' : ($ownedCard->is_for_trade ? 'Open for trade' : 'Public listing') }}</span>
                                            </div>
                                            <div class="wishlist-match-thumb card-media-ratio {{ $listedCard->thumbnail_style }}">
                                                <img
                                                    src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                                    alt="{{ $listedCard->title }}"
                                                    class="card-media-image"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                                >
                                            </div>
                                            <div class="wishlist-match-copy">
                                                <strong>{{ $listedCard->title }}</strong>
                                                <p>{{ $listedCard->artist }} • {{ $listedCard->album ?: 'Standalone release' }}</p>
                                                <p>{{ $ownedCard->listing_price ? 'PHP '.number_format((float) $ownedCard->listing_price, 0) : ($ownedCard->is_for_trade ? 'Trade listing' : 'Public showcase') }}</p>
                                            </div>
                                            <a href="{{ route('marketplace.cards.show', $listing) }}" class="marketplace-link">View listing</a>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @else
                            <div class="collection-empty">No active matches yet. We’ll surface real marketplace listings here when they appear.</div>
                        @endif
                    </article>
                </section>

                <section id="wishlist-add-form" class="dashboard-card wishlist-add-shell">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Add wishlist item</p>
                            <h2>Track a wanted photocard</h2>
                        </div>
                        <span class="mini-chip">Saved to your account</span>
                    </div>

                    <form method="POST" action="{{ route('wishlist.store') }}" class="card-create-form">
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
                                @error('priority') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Target Price</span>
                                <input type="number" name="target_price" value="{{ old('target_price') }}" min="0" step="0.01" placeholder="1200">
                                @error('target_price') <small class="field-error">{{ $message }}</small> @enderror
                            </label>
                        </div>

                        <div class="create-form-actions">
                            <button type="submit" class="dashboard-add-card">Save wishlist item</button>
                        </div>
                    </form>
                </section>
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
    currentWishlistForm = button.closest('form'); // save the form
    document.getElementById('wishlistDeleteMessage').innerText = 
        `Remove "${cardName}" from your wishlist?`;
    document.getElementById('wishlistDeleteModal').style.display = 'flex';
}

// Cancel button
document.getElementById('cancelWishlistDelete').onclick = function() {
    document.getElementById('wishlistDeleteModal').style.display = 'none';
    currentWishlistForm = null;
};

// Confirm button
document.getElementById('confirmWishlistDelete').onclick = function() {
    if(currentWishlistForm) currentWishlistForm.submit(); // submit the form
};
</script>
@endsection

