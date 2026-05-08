@extends('layouts.app')

@section('title', 'CardFlow | Collection Item Details')
@section('body_class', 'dashboard-body card-details-page')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header marketplace-header card-details-header">
                <div>
                    <p class="dashboard-kicker">Collection Item</p>
                    <p class="card-details-eyebrow">Collection showcase</p>
                    <h1>{{ $card->title ?? 'Collection Item Details' }}</h1>
                    <p class="dashboard-intro">A closer look at this collection item from your personal collection.</p>
                </div>

                <div class="dashboard-actions card-details-actions">
                    <a href="{{ route('collection.index') }}" class="card-details-back-button">← Back to Collection</a>
                </div>
            </header>

            <section class="dashboard-card card-detail-shell card-detail-shell-premium">
                <div class="card-detail-media-column">
                    <div class="card-detail-media-frame rarity-{{ \Illuminate\Support\Str::slug($rarityLabel) }}">
                        <div class="card-detail-media card-detail-media-premium">
                            <img
                                src="{{ $imagePath }}"
                                alt="{{ $card->title }}"
                                class="card-detail-media-image"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                            >
                            <div class="card-detail-media-overlay"></div>
                            <span class="card-detail-rarity-badge">Rare · {{ $rarityLabel }}</span>
                        </div>
                    </div>
                </div>

                <div class="card-detail-copy card-detail-copy-premium">
                    <section class="card-detail-hero card-detail-fade" style="--card-detail-delay: 0ms;">
                        <p class="mini-label">Artist / Group</p>
                        <h2>{{ $card->artist ?? 'Not Available' }}</h2>
                        <p class="card-detail-title-display">{{ $card->title ?? 'Not Available' }}</p>
                    </section>

                    <div class="card-detail-divider"></div>

                    <section class="card-detail-chip-grid card-detail-fade" style="--card-detail-delay: 50ms;">
                        <article class="card-detail-chip">
                            <span class="summary-label">Album</span>
                            <strong>{{ $card->album ?: 'Standalone' }}</strong>
                        </article>
                        <article class="card-detail-chip">
                            <span class="summary-label">Edition</span>
                            <strong>{{ $card->edition ?: 'Standard' }}</strong>
                        </article>
                        <article class="card-detail-chip">
                            <span class="summary-label">Rarity</span>
                            <strong>{{ $rarityLabel }}</strong>
                        </article>
                    </section>

                    <section class="card-detail-support-grid card-detail-fade" style="--card-detail-delay: 100ms;">
                        <article class="card-detail-chip">
                            <span class="summary-label">Condition</span>
                            <strong>{{ $userCard->condition ?? 'Not Available' }}</strong>
                        </article>
                        <article class="card-detail-chip">
                            <span class="summary-label">Acquired</span>
                            <strong>{{ $userCard->acquired_at ? \Carbon\Carbon::parse($userCard->acquired_at)->format('M d, Y') : 'Not Available' }}</strong>
                        </article>
                        <article class="card-detail-chip">
                            <span class="summary-label">Collection</span>
                            <strong>Personal binder</strong>
                        </article>
                    </section>

                    <div class="card-detail-divider"></div>

                    <section class="card-financial-summary card-detail-fade" style="--card-detail-delay: 150ms;">
                        <div class="card-financial-grid">
                            <article class="card-financial-chip">
                                <span class="summary-label">Market Value</span>
                                <strong>PHP {{ number_format($marketValue, 2) }}</strong>
                                @if($userCard->price_trend === 'rising')
                                    <span class="trend rising">▲ Rising</span>
                                @elseif($userCard->price_trend === 'falling')
                                    <span class="trend falling">▼ Falling</span>
                                @else
                                    <span class="trend stable">● Stable</span>
                                @endif
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

                    <section class="dashboard-card card-note-shell card-detail-fade" style="--card-detail-delay: 200ms;">
                        <p class="mini-label">Notes</p>
                        <p>{{ $userCard->notes ?: 'No notes added yet.' }}</p>
                    </section>
                </div>
            </section>

    <a href="{{ route('collection.edit', $userCard) }}" class="card-detail-fab" aria-label="Edit card" title="Edit card">✏</a>
@endsection

