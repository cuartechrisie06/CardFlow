@extends('layouts.admin')

@section('title', 'Proof Queue')

@section('content')
<header class="dashboard-header"><div><p class="dashboard-kicker">Admin Panel</p><h1>Proof queue</h1><p class="dashboard-intro">Review ownership proof for marketplace listings.</p></div></header>
<section class="dashboard-card">
    <div class="card-topline"><div><p class="mini-label">Proof of Ownership</p><h2>Unverified listings</h2></div><span class="mini-chip" style="color:{{ $listings->count() ? '#8B4513' : '#2d6a4f' }};">{{ $listings->count() }} unverified</span></div>
    @if($listings->isEmpty())
        <div class="empty-state"><p>All listings have proof of ownership verified.</p></div>
    @else
        <div style="border:0.5px solid rgba(92,61,46,0.12);border-radius:12px;overflow:hidden;background:rgba(255,251,246,0.94);">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:16px;padding:13px 20px;background:#ede3d5;"><p class="stat-label">LISTING</p><p class="stat-label">SELLER</p><p class="stat-label">LISTED</p><p class="stat-label">PROOF</p><p class="stat-label">ACTIONS</p></div>
            @foreach($listings as $listing)
                <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:16px;padding:15px 20px;border-bottom:0.5px solid rgba(92,61,46,0.08);align-items:center;">
                    <div><p style="font-weight:700;color:#3d2b1f;margin:0;">{{ $listing->card?->title }}</p><p style="font-size:.76rem;color:#8B6F5E;margin:0;">{{ $listing->card?->edition ?: $listing->card?->album }}</p></div>
                    <p style="color:#8B4513;margin:0;">{{ '@'.($listing->user?->username ?? 'collector') }}</p><p style="color:#8B6F5E;margin:0;">{{ $listing->created_at->diffForHumans() }}</p><span class="mini-chip">No proof</span>
                    <div style="display:flex;gap:8px;"><form method="POST" action="{{ route('admin.moderation.proof.verify',$listing) }}">@csrf<button class="mini-chip" style="border:1px solid rgba(45,106,79,.24);color:#2d6a4f;">Mark verified</button></form><form method="POST" action="{{ route('admin.moderation.proof.request',$listing) }}">@csrf<button class="mini-chip" style="border:1px solid rgba(139,69,19,.18);">Request proof</button></form><form method="POST" action="{{ route('admin.listings.delete',$listing) }}">@csrf @method('DELETE')<button class="mini-chip" style="border:1px solid rgba(163,45,45,.3);color:#A32D2D;">Remove listing</button></form></div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
