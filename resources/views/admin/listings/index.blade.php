@extends('layouts.admin')

@section('title', 'Admin Listings')

@section('content')
@php
    $statusBadge = function ($listing) {
        if ($listing->proof_status === 'pending' && $listing->proof_photo) {
            return ['Flagged', '#A32D2D', 'rgba(163,45,45,0.08)'];
        }

        return match ($listing->status) {
            'active' => ['Active', '#2d6a4f', 'rgba(45,106,79,0.1)'],
            'sold' => ['Sold', '#8B6F5E', 'rgba(139,111,94,0.12)'],
            default => [ucfirst($listing->status), '#8B4513', 'rgba(243,230,219,0.84)'],
        };
    };
@endphp

<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>Listings</h1>
        <p class="dashboard-intro">Review marketplace listings, proof status, and seller activity.</p>
    </div>
    <form method="GET" action="{{ route('admin.listings') }}" class="dashboard-actions">
        <label class="dashboard-search">
            <span class="sr-only">Search listings</span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search listings...">
        </label>
    </form>
</header>

<section class="dashboard-card" style="padding:0;overflow:hidden;border-radius:12px;border:0.5px solid rgba(92,61,46,0.12);background:rgba(255,251,246,0.94);">
    <div style="display:grid;grid-template-columns:2fr 1fr 0.8fr 0.8fr 0.9fr auto;gap:16px;padding:13px 20px;background:#ede3d5;border-bottom:0.5px solid rgba(92,61,46,0.12);">
        @foreach(['LISTING', 'SELLER', 'STATUS', 'PROOF', 'PRICE', 'ACTIONS'] as $col)
            <p class="stat-label" style="margin:0;font-size:11px;color:rgba(143,90,62,0.72);{{ $col === 'PRICE' ? 'text-align:right;' : '' }}">{{ $col }}</p>
        @endforeach
    </div>

    @forelse($listings as $listing)
        @php
            [$label, $color, $bg] = $statusBadge($listing);
        @endphp
        <div style="display:grid;grid-template-columns:2fr 1fr 0.8fr 0.8fr 0.9fr auto;gap:16px;padding:15px 20px;border-bottom:0.5px solid rgba(92,61,46,0.08);align-items:center;background:rgba(255,251,246,0.72);">
            <div>
                <p style="font-size:0.9rem;font-weight:700;color:#3d2b1f;margin:0;">{{ $listing->card?->title ?? 'Listing' }}</p>
                <p style="font-size:0.76rem;color:#8B6F5E;margin:2px 0 0;">{{ $listing->card?->edition ?: ($listing->card?->album ?? 'Marketplace listing') }}</p>
            </div>
            <p style="font-size:0.82rem;color:#8B4513;margin:0;">{{ '@'.($listing->user?->username ?? 'collector') }}</p>
            <span class="mini-chip" style="justify-self:start;color:{{ $color }};background:{{ $bg }};">{{ $label }}</span>
            <span class="mini-chip" style="justify-self:start;">{{ $listing->proof_verified ? 'Verified' : ($listing->proof_photo ? 'Pending' : 'None') }}</span>
            <p style="font-size:0.86rem;font-weight:700;color:#3d2b1f;margin:0;text-align:right;">PHP {{ number_format((float) ($listing->userCard?->listing_price ?? $listing->card?->market_value ?? 0), 0) }}</p>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <a href="{{ route('marketplace.cards.show', $listing) }}" class="mini-chip" style="border:1px solid rgba(139,69,19,0.28);color:#8B4513;text-decoration:none;">View</a>
                <form method="POST" action="{{ route('admin.listings.delete', $listing) }}" class="dashboard-inline-form" onsubmit="return confirm('Delete this listing?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="mini-chip" style="border:1px solid rgba(163,45,45,0.3);color:#A32D2D;cursor:pointer;">Delete</button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty-state" style="min-height:160px;">
            <p>No listings yet.</p>
            <span>Marketplace listings will appear here.</span>
        </div>
    @endforelse
</section>

<div style="margin-top:16px;">{{ $listings->links() }}</div>
@endsection
