@extends('layouts.app')

@section('title', 'CardFlow | ' . $profileUser->name)
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header marketplace-header">
                    <div>
                        <p class="dashboard-kicker">Marketplace Listings</p>
                        <h1>{{ $profileUser->name }}</h1>
                        <p class="dashboard-intro">Active marketplace listings visible from this collector's public profile.</p>
                    </div>

                    <a href="{{ route('marketplace.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Back to marketplace</a>
                </header>

                <section class="dashboard-card featured-marketplace-card">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Public listings</p>
                            <h2>{{ '@'.$profileUser->username }}</h2>
                        </div>
                        <span class="mini-chip">{{ $publicCards->total() }} visible listings</span>
                    </div>

                    <div class="marketplace-grid">
                        @forelse ($publicCards as $item)
                            @php
                                $card = $item->card;
                                $ownedCard = $item->userCard;
                                $photoUrl = $storagePhotoUrl($ownedCard->photo_path);
                            @endphp
                            <article class="marketplace-item">
                                <a href="{{ route('marketplace.cards.show', $item) }}" class="marketplace-item-link">
                                    <div class="marketplace-thumb card-media-ratio {{ $card->thumbnail_style }}">
                                        <img
                                            src="{{ $photoUrl ?: asset('images/placeholder-card.png') }}"
                                            alt="{{ $card->title }}"
                                            class="card-media-image"
                                            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                        >
                                    </div>
                                    <div class="marketplace-meta">
                                        <h3>{{ $card->title }}</h3>
                                        <p>{{ strtoupper($card->artist) }}</p>
                                        <p>{{ $card->album ?: 'Standalone release' }}</p>
                                        <div class="marketplace-meta-footer">
                                            <span>{{ $ownedCard->is_for_trade ? 'Trade listing' : ($ownedCard->is_for_sale ? 'For sale' : 'Public showcase') }}</span>
                                            <span class="marketplace-link">View listing</span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        @empty
                            <div class="collection-empty">This collector has no public listings yet.</div>
                        @endforelse
                    </div>
                </section>
@endsection
