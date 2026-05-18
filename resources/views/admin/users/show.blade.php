@extends('layouts.admin')

@section('title', 'Admin User Profile')

@section('content')
<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel › Users</p>
        <h1>{{ $user->name }}</h1>
    </div>
    @unless($user->is_admin)
        <div class="dashboard-actions">
            @if($user->suspended_at)
                <form method="POST" action="{{ route('admin.users.restore', $user) }}">@csrf<button class="mini-chip" style="border:1px solid rgba(45,106,79,0.24);color:#2d6a4f;background:rgba(45,106,79,0.08);cursor:pointer;">Restore user</button></form>
            @else
                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">@csrf<button class="mini-chip" style="border:1px solid rgba(163,45,45,0.3);color:#A32D2D;cursor:pointer;">Suspend user</button></form>
            @endif
        </div>
    @endunless
</header>

<section class="dashboard-card">
    <div style="display:flex;gap:1rem;align-items:flex-start;">
        <div style="width:72px;height:72px;border-radius:999px;background:#8B4513;color:#fff;display:grid;place-items:center;font-weight:800;font-size:1.25rem;">@initials($user->name)</div>
        <div>
            <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;color:#3d2b1f;margin:0;">{{ $user->name }}</h2>
            <p style="color:#8B4513;margin:0.25rem 0;">{{ '@'.$user->username }} · {{ $user->email }}</p>
            <p style="color:#8B6F5E;margin:0;">Joined {{ $user->created_at->format('M d, Y') }} · Last active {{ $user->updated_at->diffForHumans() }}</p>
            <div style="display:flex;gap:0.5rem;margin-top:0.6rem;">
                <span class="mini-chip">{{ $user->is_admin ? 'Admin' : 'User' }}</span>
                <span class="mini-chip" style="color:{{ $user->suspended_at ? '#A32D2D' : '#2d6a4f' }};background:{{ $user->suspended_at ? 'rgba(163,45,45,0.08)' : 'rgba(45,106,79,0.1)' }};">{{ $user->suspended_at ? 'Suspended' : 'Active' }}</span>
            </div>
        </div>
    </div>
    <div class="stats-grid" style="margin-top:1rem;">
        @foreach([['TOTAL CARDS',$user->userCards->count()],['TOTAL LISTINGS',$user->marketplaceListings->count()],['TRADES',$tradeCount],['MEMBER FOR',$user->created_at->diffForHumans()]] as [$label,$value])
            <article class="stat-card" style="box-shadow:none;"><span class="stat-label">{{ $label }}</span><div class="stat-value" style="font-size:1.45rem;">{{ $value }}</div></article>
        @endforeach
    </div>
</section>

<section class="dashboard-card market-card">
    <div class="card-topline"><div><p class="mini-label">My Collection</p><h2>Collection</h2></div><span class="mini-chip">{{ $user->userCards->count() }} cards</span></div>
    @if($user->userCards->isEmpty())
        <div class="empty-state"><p>No cards in collection.</p><span>This user's cards will appear here.</span></div>
    @else
        <div class="market-grid">
            @foreach($user->userCards->take(6) as $owned)
                <article class="market-item"><div class="hot-card-wrapper"><img class="hot-card-image" src="{{ $owned->card?->photo_url ?: asset('images/placeholder-card.png') }}"></div><div class="market-meta"><h3>{{ $owned->card?->title }}</h3><p>{{ $owned->card?->artist }}</p></div></article>
            @endforeach
        </div>
    @endif
</section>

<section class="dashboard-card">
    <div class="card-topline"><div><p class="mini-label">Listings</p><h2>Active listings</h2></div></div>
    @include('admin.partials.user-listings-table', ['listings' => $user->marketplaceListings])
</section>

<section class="dashboard-card">
    <div class="card-topline"><div><p class="mini-label">Admin Notes</p><h2>Internal notes</h2></div><span class="mini-chip">Never shown to user</span></div>
    <form method="POST" action="{{ route('admin.users.note', $user) }}" class="field-group">
        @csrf
        <textarea name="note" rows="5" style="width:100%;border:1px solid rgba(137,104,78,0.12);border-radius:1rem;background:rgba(255,252,248,0.84);padding:1rem;">{{ old('note', $note?->note) }}</textarea>
        <button class="dashboard-search-submit" style="margin-top:0.75rem;">Save note</button>
    </form>
    <ul class="activity-list">
        @forelse($actions as $action)<li><strong>{{ $action->description }}</strong><span>{{ $action->created_at->diffForHumans() }}</span></li>@empty<li><strong>No user actions yet</strong><span>Actions targeting this user will appear here.</span></li>@endforelse
    </ul>
</section>
@endsection
