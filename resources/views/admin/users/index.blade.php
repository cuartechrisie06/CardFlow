@extends('layouts.admin')

@section('title', 'Admin Users')

@section('content')
<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>Users</h1>
        <p class="dashboard-intro">Manage collector accounts, admin access, and platform membership.</p>
    </div>
    <div class="dashboard-actions">
        <a href="{{ route('admin.users') }}" class="dashboard-add-card">+ Invite user</a>
    </div>
</header>

<section class="dashboard-card" style="padding:0;overflow:hidden;border-radius:12px;border:0.5px solid rgba(92,61,46,0.12);background:rgba(255,251,246,0.94);">
    <div style="display:grid;grid-template-columns:2fr 1fr 0.7fr 0.8fr 0.8fr auto;gap:16px;padding:13px 20px;background:#ede3d5;border-bottom:0.5px solid rgba(92,61,46,0.12);">
        @foreach(['USER', 'JOINED', 'CARDS', 'LISTINGS', 'ROLE', 'ACTIONS'] as $col)
            <p class="stat-label" style="margin:0;font-size:11px;color:rgba(143,90,62,0.72);">{{ $col }}</p>
        @endforeach
    </div>

    @forelse($users as $user)
        <div onclick="if(event.target.closest('form,button,a')) return; window.location='{{ route('admin.users.show', $user) }}';" style="display:grid;grid-template-columns:2fr 1fr 0.7fr 0.8fr 0.8fr auto;gap:16px;padding:15px 20px;border-bottom:0.5px solid rgba(92,61,46,0.08);align-items:center;background:rgba(255,251,246,0.72);cursor:pointer;">
            <div>
                <p style="font-size:0.9rem;font-weight:700;color:#3d2b1f;margin:0;">
                    {{ $user->name }}
                    @if($user->is_admin)
                        <span class="mini-chip" style="font-size:0.62rem;color:#3d2b1f;margin-left:6px;">ADMIN</span>
                    @endif
                </p>
                <p style="font-size:0.76rem;color:#8B6F5E;margin:2px 0 0;">
                    {{ '@'.$user->username }} · {{ $user->email }}
                </p>
            </div>

            <p style="font-size:0.82rem;color:#8B6F5E;margin:0;">{{ $user->created_at->format('M d, Y') }}</p>
            <p style="font-size:0.9rem;color:#3d2b1f;margin:0;">{{ $user->user_cards_count }}</p>
            <p style="font-size:0.9rem;color:#3d2b1f;margin:0;">{{ $user->marketplace_listings_count }}</p>
            <span class="mini-chip" style="justify-self:start;">{{ $user->is_admin ? 'Admin' : 'User' }}</span>

            <div style="display:flex;gap:8px;justify-content:flex-end;">
                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="dashboard-inline-form">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="mini-chip" style="border:1px solid rgba(139,69,19,0.28);color:#8B4513;cursor:pointer;">
                            {{ $user->is_admin ? 'Demote' : 'Promote' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.delete', $user) }}" class="dashboard-inline-form" onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mini-chip" style="border:1px solid rgba(163,45,45,0.3);color:#A32D2D;cursor:pointer;">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="empty-state" style="min-height:160px;">
            <p>No users yet.</p>
            <span>Registered collectors will appear here.</span>
        </div>
    @endforelse
</section>

<div style="margin-top:16px;">
    {{ $users->links() }}
</div>
@endsection
