@extends('layouts.app')

@section('title', 'CardFlow | My Collection')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header collection-header">
                    <div>
                        <p class="dashboard-kicker">My Collection</p>
                        <h1>My collection</h1>
                    </div>

                    <a href="{{ route('collection.create') }}" class="dashboard-add-card">+ Add collection item</a>
                </header>

                <section class="dashboard-card collection-card-shell">
                    @if ($hasAnyCollectionCards)
                        <form method="GET" action="{{ route('collection.index') }}" class="collection-toolbar">
                            <label class="collection-search">
                                <span class="sr-only">Search collection</span>
                                <input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Search by idol, group, or album...">
                            </label>

                            <div class="collection-filters">
                                @foreach ($filters['items'] as $value => $label)
                                    <button type="submit" name="filter" value="{{ $value }}" class="collection-filter {{ $filters['active'] === $value ? 'is-active' : '' }}">
                                        {{ $label }}
                                    </button>
                                @endforeach
                            </div>
                        </form>
                    @endif

                    <div class="collection-grid">
                        @forelse ($collectionCards as $item)
                            @php
                                $card = $item->card;
                                $badge = $card->edition ?: $card->rarity;
                                $accent = $card->rarity;
                                $photoUrl = $storagePhotoUrl($item->photo_path);

                                $visibility = collect([
                                    $item->is_public ? 'Public' : 'Private',
                                    $item->is_for_trade ? 'Trade' : null,
                                    $item->is_for_sale ? 'Sale' : null,
                                ])->filter()->implode(' • ');
                            @endphp

                            <article class="collection-item collection-item-with-actions">
                                <a href="{{ route('collection.show', $item) }}" class="collection-item-link">
                                    <div class="collection-thumb card-media-ratio {{ $card->thumbnail_style }}">
                                        <img
                                            src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                            alt="{{ $card->title }}"
                                            class="card-media-image"
                                            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                        >
                                        <span class="collection-pill collection-pill-left">{{ $item->condition }}</span>
                                        <span class="collection-pill collection-pill-right">{{ $badge }}</span>
                                    </div>

                                    <div class="collection-meta">
                                        <h3>{{ $card->title }}</h3>
                                        <p>{{ strtoupper($card->artist) }}</p>
                                        <p>{{ $card->album ?: 'Standalone release' }}</p>
                                        <p>{{ $item->condition }}</p>
                                        <p>{{ $visibility }}</p>

                                        <div class="collection-meta-footer">
                                            <span class="mini-chip">{{ $accent }}</span>
                                            <strong>1 copy</strong>
                                        </div>
                                    </div>
                                </a>

                                <div class="collection-delete-form">
                                    <button
                                        type="button"
                                        class="collection-delete-btn js-open-collection-delete-modal"
                                        data-delete-url="{{ route('collection.destroy', $item) }}"
                                        aria-label="Open card actions for {{ $card->title }}"
                                        title="Card actions"
                                    >
                                        <span aria-hidden="true">...</span>
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="collection-empty collection-empty-rich">
                                <div class="collection-empty-icon" aria-hidden="true">🧺</div>
                                <h3>Start with your favorite card.</h3>
                                <p>Add one photocard now; you can mark it private, trade-only, or public later.</p>
                                <a href="{{ route('collection.create') }}" class="dashboard-add-card">
                                    + Add Card
                                </a>
                            </div>
                        @endforelse
                    </div>

                    @if ($hasAnyCollectionCards && $collectionCount > 0)
                        <div class="collection-footer">
                            <p>Showing {{ $collectionCards->firstItem() ?? 0 }} to {{ $collectionCards->lastItem() ?? 0 }} of {{ $collectionCount }} cards</p>
                            <div class="collection-pagination">
                                @if ($collectionCards->onFirstPage())
                                    <span class="page-button is-disabled">&lsaquo;</span>
                                @else
                                    <a href="{{ $collectionCards->previousPageUrl() }}" class="page-button">&lsaquo;</a>
                                @endif

                                @foreach ($collectionCards->getUrlRange(1, $collectionCards->lastPage()) as $page => $url)
                                    <a href="{{ $url }}" class="page-button {{ $collectionCards->currentPage() === $page ? 'is-active' : '' }}">{{ $page }}</a>
                                @endforeach

                                @if ($collectionCards->hasMorePages())
                                    <a href="{{ $collectionCards->nextPageUrl() }}" class="page-button">&rsaquo;</a>
                                @else
                                    <span class="page-button is-disabled">&rsaquo;</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </section>
        <div class="delete-modal" id="collectionDeleteModal" aria-hidden="true">
    <div class="delete-modal-backdrop js-close-collection-delete-modal"></div>

    <div
        class="delete-modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="collectionDeleteTitle"
    >
        <div class="delete-modal-icon">!</div>

        <div>
            <p class="delete-modal-eyebrow">Confirm delete</p>
            <h2 id="collectionDeleteTitle">Delete this collection item?</h2>
            <p class="delete-modal-text">
                This will permanently remove the collection item from your collection. This action cannot be undone.
            </p>
        </div>

        <form method="POST" id="collectionDeleteForm">
            @csrf
            @method('DELETE')

            <div class="delete-modal-actions">
                <button type="button" class="delete-modal-cancel js-close-collection-delete-modal">
                    Cancel
                </button>

                <button type="submit" class="delete-modal-confirm">
                    Yes, delete card
                </button>
            </div>
        </form>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('collectionDeleteModal');
            const form = document.getElementById('collectionDeleteForm');
            const openButtons = document.querySelectorAll('.js-open-collection-delete-modal');
            const closeButtons = document.querySelectorAll('.js-close-collection-delete-modal');

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
