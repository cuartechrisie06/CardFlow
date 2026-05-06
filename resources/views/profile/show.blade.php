<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | {{ $profileUser->name }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $user->name }}</p>
                        <span>{{ '@'.$username }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header">
                    <div>
                        <p class="dashboard-kicker">Profile</p>
                        <h1>{{ $profileUser->name }}</h1>
                        <p class="dashboard-intro">{{ '@'.($profileUser->username ?: 'collector') }}</p>
                    </div>

                    @if ($user->is($profileUser))
                        <div class="dashboard-actions">
                            <a href="{{ route('profile.edit') }}" class="dashboard-add-card">Edit profile</a>
                        </div>
                    @endif
                </header>

                <section class="stats-grid">
                    <article class="stat-card">
                        <span class="stat-label">Collection cards</span>
                        <div class="stat-value">{{ $profileUser->user_cards_count }}</div>
                        <div class="stat-note">saved in CardFlow</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Marketplace listings</span>
                        <div class="stat-value">{{ $profileUser->marketplace_listings_count }}</div>
                        <div class="stat-note">currently visible</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Wishlist items</span>
                        <div class="stat-value">{{ $profileUser->wishlist_items_count }}</div>
                        <div class="stat-note">tracked wants</div>
                    </article>
                </section>
                <section class="dashboard-card profile-listings-section">
                        <div class="section-heading-row">
                            <div>
                                <p class="dashboard-kicker">Marketplace listings</p>
                                <h2>{{ auth()->id() === $user->id ? 'My posted listings' : $user->name . "'s posted listings" }}</h2>
                                <p class="dashboard-intro">
                                    Cards currently visible in the marketplace.
                                </p>
                            </div>

                            @if (auth()->id() === $user->id)
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

                                    $photoUrl = $userCard?->photo_path
                                        ? \Illuminate\Support\Facades\Storage::url($userCard->photo_path)
                                        : null;

                                    $listingType = $userCard?->is_for_trade
                                        ? 'Trade'
                                        : ($userCard?->is_for_sale ? 'Sale' : 'Showcase');
                                @endphp

                                <article class="profile-listing-card">
                                    <a href="{{ route('marketplace.cards.show', $listing) }}" class="profile-listing-link">
                                        <div
                                            class="profile-listing-thumb {{ $photoUrl ? 'collection-thumb-photo' : ($card->thumbnail_style ?? '') }}"
                                            @if ($photoUrl)
                                                style="background-image: url('{{ $photoUrl }}');"
                                            @endif
                                        ></div>

                                        <div class="profile-listing-body">
                                            <span class="mini-chip">{{ $listingType }}</span>

                                            <h3>{{ $card->title ?? 'Untitled card' }}</h3>

                                            <p>{{ $card->artist ?? 'Unknown artist' }}</p>

                                            <div class="profile-listing-meta">
                                                <span>Condition: {{ $userCard->condition ?? 'N/A' }}</span>

                                                @if ($userCard?->listing_price)
                                                    <span>PHP {{ number_format((float) $userCard->listing_price, 0) }}</span>
                                                @else
                                                    <span>No price set</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>

                                    @if (auth()->id() === $listing->user_id)
                                        <div class="my-listing-actions">
                                            <a href="{{ route('marketplace.edit', $listing) }}" class="my-listing-edit">
                                                Edit
                                            </a>

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
                                <div class="empty-state">
                                    No marketplace listings posted yet.
                                </div>
                            @endforelse
                        </div>
                    </section>
            </section>
        </main>
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
    </body>
</html>
