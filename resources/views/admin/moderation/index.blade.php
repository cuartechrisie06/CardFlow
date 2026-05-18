@extends('layouts.admin')

@section('title', 'Admin Moderation')

@section('content')
<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>Moderation</h1>
        <p class="dashboard-intro">Review flagged listings, proof uploads, and resolved actions.</p>
    </div>
    <div class="dashboard-actions">
        <span class="mini-chip">{{ $pendingCount > 0 ? $pendingCount.' pending' : 'All clear' }}</span>
    </div>
</header>

<section class="dashboard-card">
    <div class="card-topline">
        <div>
            <p class="mini-label">Flagged Listings</p>
            <h2>Reports queue</h2>
        </div>
        <span class="mini-chip">Live queue</span>
    </div>

    @if($reports->isEmpty())
        <div class="empty-state" style="min-height:160px;background:linear-gradient(180deg, rgba(247,242,237,0.95), rgba(243,237,230,0.9));border:1px solid rgba(237,227,217,0.95);border-radius:1.35rem;">
            <p>No reports in the queue.</p>
            <span>Flagged listings and user reports will appear here.</span>
        </div>
    @else
        <div class="wishlist-match-list">
            @foreach($reports as $report)
                <div class="wishlist-match-card" style="grid-template-columns:48px minmax(0,1fr) auto auto auto;">
                    <img
                        src="{{ $report->card?->photo_url ?: asset('images/placeholder-card.png') }}"
                        alt="{{ $report->card?->title ?? 'Listing' }}"
                        style="width:48px;height:48px;"
                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                    >
                    <div>
                        <strong>{{ $report->card?->title ?? 'Listing' }}</strong>
                        <span>{{ '@'.($report->user?->username ?? 'collector') }} reported this</span>
                    </div>
                    <span class="mini-chip">Proof review</span>
                    <span style="color:#8B6F5E;font-size:0.78rem;">{{ $report->created_at->diffForHumans() }}</span>
                    <div style="display:flex;gap:8px;">
                        <form method="POST" action="{{ route('admin.listings.verify-proof', $report) }}" class="dashboard-inline-form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="mini-chip" style="border:1px solid rgba(45,106,79,0.24);color:#2d6a4f;background:rgba(45,106,79,0.08);cursor:pointer;">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.listings.delete', $report) }}" class="dashboard-inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="mini-chip" style="border:1px solid rgba(139,69,19,0.18);cursor:pointer;">Dismiss</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>

<section class="dashboard-card">
    <div class="card-topline">
        <div>
            <p class="mini-label">Proof of Ownership</p>
            <h2>Unverified listings</h2>
        </div>
        <a href="{{ route('admin.moderation.proof') }}" class="mini-chip" style="text-decoration:none;">Open queue</a>
    </div>
    <div class="empty-state" style="min-height:120px;background:linear-gradient(180deg, rgba(247,242,237,0.95), rgba(243,237,230,0.9));border:1px solid rgba(237,227,217,0.95);border-radius:1.35rem;">
        <p>Review proof queue separately.</p>
        <span>Unverified listings and ownership requests are tracked in the proof queue.</span>
    </div>
</section>

<section class="dashboard-card">
    <div class="card-topline">
        <div>
            <p class="mini-label">Moderation Log</p>
            <h2>Resolved actions</h2>
        </div>
        <span class="mini-chip">Latest</span>
    </div>

    @if($resolved->isEmpty())
        <div class="empty-state" style="min-height:140px;background:linear-gradient(180deg, rgba(247,242,237,0.95), rgba(243,237,230,0.9));border:1px solid rgba(237,227,217,0.95);border-radius:1.35rem;">
            <p>No resolved actions yet.</p>
            <span>Moderation decisions will appear here.</span>
        </div>
    @else
        <div style="border:0.5px solid rgba(92,61,46,0.12);border-radius:12px;overflow:hidden;background:rgba(255,251,246,0.94);">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:16px;padding:13px 20px;background:#ede3d5;border-bottom:0.5px solid rgba(92,61,46,0.12);">
                @foreach(['LISTING', 'ACTION', 'RESOLVED BY', 'DATE'] as $col)
                    <p class="stat-label" style="margin:0;font-size:11px;color:rgba(143,90,62,0.72);">{{ $col }}</p>
                @endforeach
            </div>
            @foreach($resolved as $item)
                <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:16px;padding:15px 20px;border-bottom:0.5px solid rgba(92,61,46,0.08);align-items:center;">
                    <p style="font-size:0.86rem;font-weight:700;color:#3d2b1f;margin:0;">{{ $item->card?->title ?? 'Listing' }}</p>
                    <span class="mini-chip" style="justify-self:start;">{{ ucfirst($item->proof_status ?? 'resolved') }}</span>
                    <p style="font-size:0.82rem;color:#8B4513;margin:0;">Admin</p>
                    <p style="font-size:0.82rem;color:#8B6F5E;margin:0;">{{ $item->updated_at->format('M d, Y') }}</p>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection
