@extends('layouts.app')

@section('title', 'Collection Item Details')
@section('layout_mode', 'shellless')
@section('body_class', 'legacy-card-details-page')

@section('content')
<div class="app-container">

    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon"></div>
            <div>
                <div class="brand-title">CardFlow</div>
                <div class="brand-subtitle">Photocard Trading</div>
            </div>
        </div>

        <ul class="menu">
            <li>Dashboard</li>
            <li class="active">My Collection</li>
            <li>Marketplace</li>
            <li>Wishlist</li>
            <li>Messages</li>
            <li>Explorer</li>
            <li>Stats</li>
        </ul>

        <div class="collector">
            <div class="collector-avatar">C</div>
            <div>
                <small>COLLECTOR</small>
                <span>Chrisie</span>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Collection Item Details</h1>
            <a href="{{ route('collection.index') }}" class="back-btn">← Back to Collection</a>
        </div>

        <section class="details-card">
            <div class="image-box">
                <img
                    src="{{ asset('images/spotify-card.png') }}"
                    alt="Card Image"
                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                >
            </div>

            <div class="info-list">
                <div class="info-row">
                    <div class="info-label">Artist / Group</div>
                    <div class="info-value">BTS</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Card Title</div>
                    <div class="info-value">JIMIN</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Album</div>
                    <div class="info-value">2020</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Edition</div>
                    <div class="info-value">V4</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Rarity</div>
                    <div class="info-value">Mint</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Market Value</div>
                    <div class="info-value">PHP 100.00</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Purchase Price</div>
                    <div class="info-value">PHP 100.00</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Estimated Value</div>
                    <div class="info-value">PHP 100.00</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Condition</div>
                    <div class="info-value">Good</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Acquired At</div>
                    <div class="info-value">01/07/2022</div>
                </div>

                <div class="info-row">
                    <div class="info-label">Notes</div>
                    <div class="info-value">Condition details, source, trade notes...</div>
                </div>

                <div class="details-card">
    <div class="image-box">
        <!-- Display card image -->
        <img
            src="{{ asset('storage/cards/' . ($card->thumbnail_style ?: 'default.jpg')) }}"
            alt="{{ $card->title }}"
            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
        >
    </div>

    <div class="info-list">
        <div class="info-row">
            <div class="info-label">Artist / Group</div>
            <div class="info-value">{{ $card->artist }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Card Title</div>
            <div class="info-value">{{ $card->title }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Edition</div>
            <div class="info-value">{{ $card->edition }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Album</div>
            <div class="info-value">{{ $card->album }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Rarity</div>
            <div class="info-value">{{ $card->rarity }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Market Value</div>
            <div class="info-value">PHP {{ number_format($card->market_value, 2) }}</div>
        </div>

        <div class="info-row">
            <div class="info-label">Released On</div>
            <div class="info-value">{{ $card->released_on->format('F j, Y') }}</div>
        </div>

        <!-- You can display any other attributes you need here -->
    </div>
</div>
            </div>
        </section>
    </main>

</div>

<button class="edit-btn">✎</button>
@endsection


