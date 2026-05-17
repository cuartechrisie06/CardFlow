@extends('admin.layout')

@section('title', 'Admin Listings')

@section('content')
<div style="max-width:1200px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <div>
            <p style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#c8956c;margin:0 0 4px;">Admin Panel</p>
            <h1 style="font-family:'Playfair Display',serif;color:#f5e6d8;margin:0;">Listings</h1>
        </div>
        <a href="{{ route('admin.index') }}" class="dashboard-search-submit" style="background:#2a1f1a;color:#f5e6d8;border-color:#3d2b1f;">Back to Admin</a>
    </div>

    <div style="background:#2a1f1a;border:1px solid #3d2b1f;border-radius:16px;overflow:hidden;">
        @foreach($listings as $listing)
            <div style="display:grid;grid-template-columns:1fr auto;gap:16px;align-items:center;padding:16px 20px;border-bottom:1px solid #3d2b1f;">
                <div>
                    <p style="font-family:'DM Sans',sans-serif;font-weight:700;color:#f5e6d8;margin:0;">{{ $listing->card?->title ?? 'Listing' }}</p>
                    <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#8B6F5E;margin:4px 0 0;">
                        {{ '@'.($listing->user?->username ?? 'collector') }} - {{ ucfirst($listing->status) }} - Proof: {{ $listing->proof_status ?: 'none' }}
                    </p>
                </div>
                <div style="display:flex;gap:8px;">
                    @if($listing->proof_photo && ! $listing->proof_verified)
                        <form method="POST" action="{{ route('admin.listings.verify-proof', $listing) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="dashboard-search-submit" style="background:#1a3d2a;color:#a8d5b5;border-color:#2d6a4f;">Verify Proof</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.listings.delete', $listing) }}" onsubmit="return confirm('Delete this listing?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dashboard-search-submit" style="background:#3d1515;color:#e88;border-color:#5c1515;">Delete</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div style="margin-top:16px;">{{ $listings->links() }}</div>
</div>
@endsection
