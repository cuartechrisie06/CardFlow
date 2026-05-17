@extends('admin.layout')

@section('title', 'Admin Trades')

@section('content')
<div style="max-width:1200px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
        <div>
            <p style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#c8956c;margin:0 0 4px;">Admin Panel</p>
            <h1 style="font-family:'Playfair Display',serif;color:#f5e6d8;margin:0;">Trades</h1>
        </div>
        <a href="{{ route('admin.index') }}" class="dashboard-search-submit" style="background:#2a1f1a;color:#f5e6d8;border-color:#3d2b1f;">Back to Admin</a>
    </div>

    <div style="background:#2a1f1a;border:1px solid #3d2b1f;border-radius:16px;overflow:hidden;">
        @foreach($trades as $trade)
            <div style="padding:16px 20px;border-bottom:1px solid #3d2b1f;">
                <p style="font-family:'DM Sans',sans-serif;font-weight:700;color:#f5e6d8;margin:0;">
                    {{ $trade->offeredCard?->title ?? 'Offered card' }} for {{ $trade->listing?->card?->title ?? 'listing card' }}
                </p>
                <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#8B6F5E;margin:4px 0 0;">
                    {{ '@'.($trade->sender?->username ?? 'sender') }} to {{ '@'.($trade->receiver?->username ?? 'receiver') }} - {{ ucfirst($trade->status) }}
                </p>
            </div>
        @endforeach
    </div>
    <div style="margin-top:16px;">{{ $trades->links() }}</div>
</div>
@endsection
