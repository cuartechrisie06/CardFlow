@extends('layouts.app')

@section('title', 'CardFlow | Collection Item Details')
@section('body_class', 'dashboard-body card-details-page')

@section('topbar')
@endsection

@section('content')
<x-photocard-detail
    context-label="Collection Item"
    eyebrow="Collection Showcase"
    :page-title="$card->title ?? 'Collection Item Details'"
    subtitle="A closer look at this collection item from your personal collection."
    :back-url="route('collection.index')"
    back-label="Back to Collection"
    :image-url="$imagePath"
    :image-alt="$card->title ?? 'Collection item image'"
    :rarity-label="$rarityLabel"
    :rarity-class="\Illuminate\Support\Str::slug($rarityLabel)"
    :artist-name="$card->artist ?? 'Not Available'"
    :card-title="$card->title ?? 'Not Available'"
    :primary-meta="[
        ['label' => 'Album', 'value' => $card->album ?: 'Standalone'],
        ['label' => 'Edition', 'value' => $card->edition ?: 'Standard'],
        ['label' => 'Rarity', 'value' => $rarityLabel],
    ]"
    :secondary-meta="[
        ['label' => 'Condition', 'value' => $userCard->condition ?? 'Not Available'],
        ['label' => 'Acquired', 'value' => $userCard->acquired_at ? \Carbon\Carbon::parse($userCard->acquired_at)->format('M d, Y') : 'Not Available'],
        ['label' => 'Collection', 'value' => 'Personal binder'],
    ]"
    :price-tiles="[
        [
            'label' => 'Market Value',
            'value' => 'PHP '.number_format($marketValue, 2),
            'trendText' => $userCard->price_trend === 'rising' ? 'Rising' : ($userCard->price_trend === 'falling' ? 'Falling' : 'Stable'),
            'trendClass' => $userCard->price_trend,
            'trendTitle' => 'Compared with your purchase price. Add pricing history later for richer trend tracking.',
        ],
        ['label' => 'Purchase Price', 'value' => 'PHP '.number_format($purchasePrice, 2)],
        ['label' => 'Estimated Value', 'value' => 'PHP '.number_format($estimatedValue, 2)],
    ]"
    price-summary-label="Unrealized gain"
    :price-summary-value="($isPositiveDelta ? '+' : '-') . 'PHP ' . number_format(abs($valueDelta), 2)"
    :price-summary-tone="$isPositiveDelta ? 'is-positive' : 'is-negative'"
>
    <x-slot name="actions">
        <a href="{{ route('collection.edit', $userCard) }}" class="card-title-edit-icon" aria-label="Edit card" title="Edit card">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4 12.5-12.5z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            </svg>
        </a>
    </x-slot>

    <div class="card-detail-secondary-actions card-detail-fade" style="--card-detail-delay: 175ms;">
        <a href="{{ route('collection.edit', $userCard) }}" class="dashboard-add-card dashboard-add-card-secondary">
            Edit card
        </a>
        <a href="{{ route('marketplace.create', ['user_card_id' => $userCard->id]) }}" class="dashboard-add-card">
            Create listing from this card
        </a>
        <form method="POST" action="{{ route('collection.traded', $userCard) }}" class="dashboard-inline-form">
            @csrf
            @method('PATCH')
            <button
                type="submit"
                class="dashboard-search-submit"
                onclick="return confirm('Mark this card as traded and remove any active marketplace listing?')"
            >
                Mark as traded
            </button>
        </form>
    </div>

    <section class="dashboard-card card-note-shell card-detail-fade" style="--card-detail-delay: 200ms;">
        <p class="mini-label">Notes</p>
        <p>{{ $userCard->notes ?: 'No notes added yet.' }}</p>
    </section>
</x-photocard-detail>
@endsection
