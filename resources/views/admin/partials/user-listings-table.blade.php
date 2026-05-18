@if($listings->isEmpty())
    <div class="empty-state"><p>No active listings.</p><span>This user's listings will appear here.</span></div>
@else
    <div style="border:0.5px solid rgba(92,61,46,0.12);border-radius:12px;overflow:hidden;background:rgba(255,251,246,0.94);">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:16px;padding:13px 20px;background:#ede3d5;border-bottom:0.5px solid rgba(92,61,46,0.12);">
            @foreach(['LISTING','STATUS','PRICE','ACTIONS'] as $col)<p class="stat-label" style="margin:0;font-size:11px;">{{ $col }}</p>@endforeach
        </div>
        @foreach($listings as $listing)
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:16px;padding:15px 20px;border-bottom:0.5px solid rgba(92,61,46,0.08);align-items:center;">
                <div><p style="font-weight:700;color:#3d2b1f;margin:0;">{{ $listing->card?->title }}</p><p style="font-size:0.76rem;color:#8B6F5E;margin:2px 0 0;">{{ $listing->card?->artist }}</p></div>
                <span class="mini-chip" style="justify-self:start;">{{ ucfirst($listing->status) }}</span>
                <p style="margin:0;color:#3d2b1f;font-weight:700;">PHP {{ number_format((float) ($listing->userCard?->listing_price ?? $listing->card?->market_value ?? 0), 0) }}</p>
                <form method="POST" action="{{ route('admin.listings.delete', $listing) }}" class="dashboard-inline-form">@csrf @method('DELETE')<button class="mini-chip" style="border:1px solid rgba(163,45,45,0.3);color:#A32D2D;cursor:pointer;">Remove</button></form>
            </div>
        @endforeach
    </div>
@endif
