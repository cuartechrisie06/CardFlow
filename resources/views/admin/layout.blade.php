<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') - CardFlow</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            display: flex;
            margin: 0;
            background: #1a1210;
            color: #f5e6d8;
            font-family: 'DM Sans', sans-serif;
        }
        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            width: 240px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            background: #2a1f1a;
            border-right: 1px solid #3d2b1f;
        }
        .admin-main {
            flex: 1;
            min-height: 100vh;
            margin-left: 240px;
            padding: 32px;
        }
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 2px 8px;
            padding: 10px 20px;
            border-radius: 8px;
            color: #8B6F5E;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .admin-nav-link:hover,
        .admin-nav-link.active {
            background: rgba(139,69,19,0.2);
            color: #f5e6d8;
        }
        .admin-nav-link.active {
            border-left: 2px solid #8B4513;
        }
    </style>
</head>
<body>
    <aside class="admin-sidebar">
        <div style="padding:24px 20px 16px;border-bottom:1px solid #3d2b1f;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:36px;height:36px;background:#8B4513;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:800;color:#fff;">
                    CF
                </div>
                <div>
                    <p style="font-size:0.6rem;text-transform:uppercase;letter-spacing:0.1em;color:#c8956c;margin:0;">CARDFLOW</p>
                    <p style="font-family:'Playfair Display',serif;font-size:0.95rem;font-weight:700;color:#f5e6d8;margin:0;">Admin Panel</p>
                </div>
            </div>
        </div>

        <nav style="flex:1;padding:16px 0;">
            <a href="{{ route('admin.index') }}" class="admin-nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.users') }}" class="admin-nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('admin.listings') }}" class="admin-nav-link {{ request()->routeIs('admin.listings*') ? 'active' : '' }}">Listings</a>
            <a href="{{ route('admin.trades') }}" class="admin-nav-link {{ request()->routeIs('admin.trades*') ? 'active' : '' }}">Trades</a>
            <div style="border-top:1px solid #3d2b1f;margin:12px 8px;"></div>
            <a href="{{ route('dashboard') }}" target="_blank" class="admin-nav-link">View Site</a>
        </nav>

        <div style="padding:16px 20px;border-top:1px solid #3d2b1f;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <div style="width:34px;height:34px;border-radius:50%;background:#3d2b1f;display:flex;align-items:center;justify-content:center;font-size:0.85rem;font-weight:700;color:#c8956c;">
                    @initials(auth()->user()->name)
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:0.82rem;font-weight:700;color:#f5e6d8;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ auth()->user()->name }}</p>
                    <p style="font-size:0.7rem;color:#c8956c;margin:0;">Administrator</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" style="width:100%;padding:8px;background:rgba(192,57,43,0.15);border:1px solid rgba(192,57,43,0.3);border-radius:8px;color:#e88;font-family:'DM Sans',sans-serif;font-size:0.78rem;cursor:pointer;">
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        @if(session('status') || session('success'))
            <div style="background:#1a3d2a;border:1px solid #2d6a4f;border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#a8d5b5;font-size:0.85rem;">
                {{ session('status') ?: session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="background:#3d1515;border:1px solid #c0392b;border-radius:10px;padding:12px 16px;margin-bottom:20px;color:#f8d7da;font-size:0.85rem;">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
