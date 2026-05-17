@extends('admin.layout')

@section('title', 'Users')

@section('content')
<div style="margin-bottom:24px;">
    <p style="font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#c8956c;margin:0 0 4px;">
        ADMIN PANEL
    </p>
    <h1 style="font-family:'Playfair Display',serif;font-size:1.8rem;color:#f5e6d8;margin:0;">
        Users
    </h1>
</div>

<div style="background:#2a1f1a;border:1px solid #3d2b1f;border-radius:16px;overflow:hidden;">
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:16px;padding:12px 20px;border-bottom:1px solid #3d2b1f;background:#221815;">
        @foreach(['USER', 'JOINED', 'CARDS', 'LISTINGS', 'ROLE', 'ACTIONS'] as $col)
            <p style="font-size:0.62rem;text-transform:uppercase;letter-spacing:0.08em;color:#c8956c;margin:0;font-weight:700;">
                {{ $col }}
            </p>
        @endforeach
    </div>

    @foreach($users as $user)
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:16px;padding:14px 20px;border-bottom:1px solid #3d2b1f;align-items:center;{{ $loop->even ? 'background:#251b16;' : '' }}">
            <div>
                <p style="font-size:0.88rem;font-weight:700;color:#f5e6d8;margin:0;">
                    {{ $user->name }}
                    @if($user->is_admin)
                        <span style="font-size:0.62rem;background:#3d1f0a;color:#c8956c;padding:2px 8px;border-radius:10px;margin-left:4px;">ADMIN</span>
                    @endif
                </p>
                <p style="font-size:0.75rem;color:#8B6F5E;margin:0;">
                    {{ '@'.$user->username }} - {{ $user->email }}
                </p>
            </div>

            <p style="font-size:0.8rem;color:#8B6F5E;margin:0;">{{ $user->created_at->format('M d, Y') }}</p>
            <p style="font-size:0.88rem;color:#f5e6d8;margin:0;">{{ $user->user_cards_count }}</p>
            <p style="font-size:0.88rem;color:#f5e6d8;margin:0;">{{ $user->marketplace_listings_count }}</p>
            <span style="font-size:0.72rem;font-weight:700;color:{{ $user->is_admin ? '#c8956c' : '#8B6F5E' }};text-transform:uppercase;">
                {{ $user->is_admin ? 'Admin' : 'User' }}
            </span>

            <div style="display:flex;gap:6px;">
                @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                title="{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}"
                                style="background:#3d2b1f;border:1px solid #5c3a1e;color:#c8956c;border-radius:8px;padding:5px 10px;font-size:0.72rem;cursor:pointer;">
                            {{ $user->is_admin ? 'Down' : 'Up' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.delete', $user) }}" onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background:#3d1515;border:1px solid #5c1515;color:#e88;border-radius:8px;padding:5px 10px;font-size:0.72rem;cursor:pointer;">
                            Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>

<div style="margin-top:16px;">
    {{ $users->links() }}
</div>
@endsection
