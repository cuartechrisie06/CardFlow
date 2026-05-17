@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
<div style="max-width:1200px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:28px;">
        <div>
            <p style="font-family:'DM Sans',sans-serif;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#c8956c;margin:0 0 4px;">
                ADMIN PANEL
            </p>
            <h1 style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:#f5e6d8;margin:0;">
                CardFlow Admin
            </h1>
        </div>
        <a href="{{ route('dashboard') }}" class="dashboard-search-submit" style="background:#2a1f1a;color:#f5e6d8;border-color:#3d2b1f;">View app</a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        @foreach([
            ['label' => 'TOTAL USERS', 'value' => $stats['total_users'], 'note' => $stats['new_users_today'].' new today'],
            ['label' => 'TOTAL CARDS', 'value' => $stats['total_cards'], 'note' => $stats['catalog_cards'].' catalog cards'],
            ['label' => 'ACTIVE LISTINGS', 'value' => $stats['active_listings'], 'note' => $stats['total_listings'].' total'],
            ['label' => 'TRADES COMPLETED', 'value' => $stats['completed_trades'], 'note' => $stats['total_trades'].' total'],
        ] as $stat)
            <div style="background:#2a1f1a;border:1px solid #3d2b1f;border-radius:14px;padding:18px 20px;">
                <p style="font-family:'DM Sans',sans-serif;font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;color:#c8956c;margin:0 0 4px;">
                    {{ $stat['label'] }}
                </p>
                <p style="font-family:'Playfair Display',serif;font-size:1.8rem;font-weight:700;color:#f5e6d8;margin:0;">
                    {{ $stat['value'] }}
                </p>
                <p style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#8B6F5E;margin:4px 0 0;">
                    {{ $stat['note'] }}
                </p>
            </div>
        @endforeach
    </div>

    @if($pendingProofs->count() > 0)
        <div style="background:#fff8e5;border:1px solid #e8c36d;border-radius:14px;padding:20px;margin-bottom:24px;">
            <p style="font-family:'DM Sans',sans-serif;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#8B4513;margin:0 0 8px;">
                PENDING PROOF VERIFICATIONS
            </p>
            <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;color:#3d2b1f;margin:0 0 14px;">
                {{ $pendingProofs->count() }} listing{{ $pendingProofs->count() === 1 ? '' : 's' }} need proof review
            </h3>
            @foreach($pendingProofs as $listing)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-top:1px solid #f2d999;">
                    <img src="{{ $listing->proof_photo_url ?: asset('images/placeholder-card.png') }}"
                         alt="Proof photo"
                         style="width:48px;height:48px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                    <div style="flex:1;min-width:0;">
                        <p style="font-family:'DM Sans',sans-serif;font-size:0.85rem;font-weight:600;color:#3d2b1f;margin:0;">
                            {{ $listing->card?->title ?? 'Listing' }}
                        </p>
                        <p style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#8B6F5E;margin:0;">
                            by {{ '@'.($listing->user?->username ?? 'collector') }}
                        </p>
                    </div>
                    <div style="display:flex;gap:8px;">
                        <a href="{{ $listing->proof_photo_url ?: '#' }}" target="_blank" style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#8B4513;border:1px solid #d4b896;padding:5px 12px;border-radius:20px;text-decoration:none;">
                            View
                        </a>
                        <form method="POST" action="{{ route('admin.listings.verify-proof', $listing) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" style="font-family:'DM Sans',sans-serif;font-size:0.75rem;background:#2d6a4f;color:#ffffff;border:none;padding:6px 12px;border-radius:20px;cursor:pointer;">
                                Verify
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;">
        @foreach([
            ['href' => route('admin.users'), 'label' => 'Manage Users', 'desc' => $stats['total_users'].' total'],
            ['href' => route('admin.listings'), 'label' => 'Manage Listings', 'desc' => $stats['total_listings'].' total'],
            ['href' => route('admin.trades'), 'label' => 'View Trades', 'desc' => $stats['total_trades'].' total'],
            ['href' => route('admin.reports'), 'label' => 'Reports', 'desc' => 'Moderation queue'],
        ] as $nav)
            <a href="{{ $nav['href'] }}" style="background:#2a1f1a;border:1px solid #3d2b1f;border-radius:14px;padding:20px;text-decoration:none;display:block;">
                <p style="font-family:'Playfair Display',serif;font-size:1rem;font-weight:700;color:#f5e6d8;margin:0 0 2px;">
                    {{ $nav['label'] }}
                </p>
                <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#8B6F5E;margin:0;">
                    {{ $nav['desc'] }}
                </p>
            </a>
        @endforeach
    </div>

    <div style="background:#2a1f1a;border:1px solid #3d2b1f;border-radius:16px;padding:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;color:#f5e6d8;margin:0;">Recent Users</h3>
            <a href="{{ route('admin.users') }}" style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#c8956c;text-decoration:none;">View all</a>
        </div>
        @foreach($recentUsers as $user)
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #3d2b1f;">
                <div style="width:36px;height:36px;border-radius:50%;background:#3d2b1f;display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:0.85rem;font-weight:700;color:#c8956c;flex-shrink:0;">
                    @initials($user->name)
                </div>
                <div style="flex:1;">
                    <p style="font-family:'DM Sans',sans-serif;font-size:0.85rem;font-weight:600;color:#f5e6d8;margin:0;">
                        {{ $user->name }}
                        @if($user->is_admin)
                            <span style="font-size:0.65rem;background:#f5e6d8;color:#8B4513;padding:2px 8px;border-radius:10px;margin-left:4px;">ADMIN</span>
                        @endif
                    </p>
                    <p style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#8B6F5E;margin:0;">
                        {{ '@'.$user->username }} - {{ $user->created_at->diffForHumans() }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
