<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Marketplace</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $viewer = auth()->user();

            $photoUrl = $userCard->photo_path
                ? \Illuminate\Support\Facades\Storage::url($userCard->photo_path)
                : null;
        @endphp

        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <a
                    href="{{ $viewer->username ? route('profile.show', $viewer->username) : route('profile.edit') }}"
                    class="sidebar-brand sidebar-profile-link"
                >
                    <div class="sidebar-avatar"></div>

                    <div>
                        <p>{{ $viewer->name }}</p>
                        <span>{{ '@' . ($viewer->username ?: 'collector') }}</span>
                    </div>
                </a>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link is-active">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $viewer])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Marketplace Card</p>
                        <h1>{{ $userCard->card->title }}</h1>
                        <p class="dashboard-intro">
                            Listed by {{ $owner->name }} for marketplace browsing.
                        </p>
                    </div>

                    <div class="dashboard-actions">
                        <a
                            href="{{ route('marketplace.index', ['filter' => $listing->user_id === auth()->id() ? 'my_listings' : 'all']) }}"
                            class="dashboard-search-submit"
                        >
                            Back
                        </a>

                        <a
                            href="{{ route('marketplace.user', $owner) }}"
                            class="dashboard-add-card dashboard-add-card-secondary"
                        >
                            View {{ $owner->name }}'s collection
                        </a>

                        @if ($viewer->id !== $owner->id)
                            <form
                                action="{{ route('messages.listings.store', $listing) }}"
                                method="POST"
                                class="dashboard-inline-form"
                            >
                                @csrf

                                <button type="submit" class="dashboard-add-card">
                                    {{ $userCard->is_for_sale ? 'Message seller' : 'Message trader' }}
                                </button>
                            </form>
                        @endif
                    </div>

                        @if ($viewer->id !== $owner->id)
                            <form
                                action="{{ route('messages.listings.store', $listing) }}"
                                method="POST"
                                class="dashboard-inline-form"
                            >
                                @csrf

                                <button type="submit" class="dashboard-add-card">
                                    {{ $userCard->is_for_sale ? 'Message seller' : 'Message trader' }}
                                </button>
                            </form>
                        @endif
                    </div>
                </header>

                <section class="dashboard-card card-detail-shell">
                    <div
                        class="card-detail-media {{ $photoUrl ? 'collection-thumb-photo' : $userCard->card->thumbnail_style }}"
                        @if ($photoUrl)
                            style="background-image: url('{{ $photoUrl }}');"
                        @endif
                    ></div>

                    <div class="card-detail-copy">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Card details</p>
                                <h2>{{ $userCard->card->title }}</h2>
                            </div>

                            <span class="mini-chip">
                                {{ strtoupper($userCard->card->rarity) }}
                            </span>
                        </div>

                        <div class="card-detail-grid">
                            <div>
                                <span class="summary-label">Artist</span>
                                <strong>{{ $userCard->card->artist }}</strong>
                            </div>

                            <div>
                                <span class="summary-label">Album</span>
                                <strong>{{ $userCard->card->album ?: 'Standalone' }}</strong>
                            </div>

                            <div>
                                <span class="summary-label">Edition</span>
                                <strong>{{ $userCard->card->edition ?: 'Standard' }}</strong>
                            </div>

                            <div>
                                <span class="summary-label">Condition</span>
                                <strong>{{ $userCard->condition }}</strong>
                            </div>

                            <div>
                                <span class="summary-label">Visibility</span>
                                <strong>{{ $userCard->is_public ? 'Public' : 'Listed only' }}</strong>
                            </div>

                            <div>
                                <span class="summary-label">Listing</span>
                                <strong>
                                    {{ $userCard->is_for_trade ? 'Trade' : ($userCard->is_for_sale ? 'Sale' : 'Showcase') }}
                                </strong>
                            </div>
                        </div>

                        @if ($userCard->listing_price)
                            <p class="dashboard-intro">
                                Listing price: PHP {{ number_format((float) $userCard->listing_price, 0) }}
                            </p>
                        @endif

                        @if ($listing->user_id === auth()->id())
                            <div class="my-listing-actions">
                                <a
                                    href="{{ route('marketplace.edit', $listing) }}"
                                    class="my-listing-edit"
                                >
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

                        @if ($userCard->notes)
                            <div class="dashboard-card card-note-shell">
                                <p class="mini-label">Owner note</p>
                                <p>{{ $userCard->notes }}</p>
                            </div>
                        @endif
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