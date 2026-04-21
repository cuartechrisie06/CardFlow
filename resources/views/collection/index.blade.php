<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | My Collection</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
            $formatMoney = fn (float|int|null $value) => 'PHP '.number_format((float) $value, 0);
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $user->name }}</p>
                        <span>{{ '@'.$username }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link is-active">My Collection</a>
                    <a href="#" class="sidebar-link">Marketplace</a>
                    <a href="#" class="sidebar-link">Wishlist</a>
                    <a href="#" class="sidebar-link">Messages</a>
                    <a href="#" class="sidebar-link">Explorer</a>
                    <a href="#" class="sidebar-link">Insights</a>
                </nav>

                <div class="sidebar-collector">
                    <span class="collector-label">Collector</span>
                    <div class="collector-card">
                        <div class="collector-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                        <div class="collector-details">
                            <p title="{{ $user->name }}">{{ $user->name }}</p>
                            <span title="{{ $user->email }}">{{ $user->email }}</span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="logout-button">Log out</button>
                    </form>
                </div>
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header collection-header">
                    <div>
                        <p class="dashboard-kicker">My Collection</p>
                        <h1>My collection</h1>
                    </div>

                    <a href="{{ route('collection.create') }}" class="dashboard-add-card">+ Upload card</a>
                </header>

                <section class="dashboard-card collection-card-shell">
                    @if (session('status'))
                        <div class="auth-status">{{ session('status') }}</div>
                    @endif

                    <form method="GET" action="{{ route('collection.index') }}" class="collection-toolbar">
                        <label class="collection-search">
                            <span class="sr-only">Search collection</span>
                            <input type="search" name="q" value="{{ $filters['search'] }}" placeholder="Search by idol, group, or album...">
                        </label>

                        <div class="collection-filters">
                            @foreach ($filters['items'] as $value => $label)
                                <button type="submit" name="filter" value="{{ $value }}" class="collection-filter {{ $filters['active'] === $value ? 'is-active' : '' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </form>

                    <div class="collection-grid">
                        @forelse ($collectionCards as $item)
                            @php
                                $card = $item->card;
                                $badge = $card->edition ?: $card->rarity;
                                $accent = $card->rarity;
                                $photoUrl = $item->photo_path ? \Illuminate\Support\Facades\Storage::url($item->photo_path) : null;
                            @endphp
                            <a href="{{ route('collection.edit', $item) }}" class="collection-item-link">
                            <article class="collection-item">
                                <div class="collection-thumb {{ $photoUrl ? 'collection-thumb-photo' : $card->thumbnail_style }}" @if ($photoUrl) style="background-image: url('{{ $photoUrl }}');" @endif>
                                    <span class="collection-pill collection-pill-left">{{ $item->condition }}</span>
                                    <span class="collection-pill collection-pill-right">{{ $badge }}</span>
                                </div>
                                <div class="collection-meta">
                                    <h3>{{ $card->title }}</h3>
                                    <p>{{ strtoupper($card->artist) }}</p>
                                    <p>{{ $card->album ?: 'Standalone release' }}</p>
                                    <p>{{ $item->condition }}</p>
                                    <div class="collection-meta-footer">
                                        <span class="mini-chip">{{ $accent }}</span>
                                        <strong>1 copy</strong>
                                    </div>
                                </div>
                            </article>
                            </a>
                        @empty
                            <div class="collection-empty">
                                No cards found for this filter yet.
                            </div>
                        @endforelse
                    </div>

                    <div class="collection-footer">
                        <p>Showing {{ $collectionCards->firstItem() ?? 0 }} to {{ $collectionCards->lastItem() ?? 0 }} of {{ $collectionCount }} cards</p>
                        <div class="collection-pagination">
                            @if ($collectionCards->onFirstPage())
                                <span class="page-button is-disabled">&lsaquo;</span>
                            @else
                                <a href="{{ $collectionCards->previousPageUrl() }}" class="page-button">&lsaquo;</a>
                            @endif

                            @foreach ($collectionCards->getUrlRange(1, $collectionCards->lastPage()) as $page => $url)
                                <a href="{{ $url }}" class="page-button {{ $collectionCards->currentPage() === $page ? 'is-active' : '' }}">{{ $page }}</a>
                            @endforeach

                            @if ($collectionCards->hasMorePages())
                                <a href="{{ $collectionCards->nextPageUrl() }}" class="page-button">&rsaquo;</a>
                            @else
                                <span class="page-button is-disabled">&rsaquo;</span>
                            @endif
                        </div>
                    </div>
                </section>
            </section>
        </main>
    </body>
</html>
