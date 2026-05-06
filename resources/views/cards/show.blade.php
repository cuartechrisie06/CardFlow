<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CardFlow | Card Details</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dashboard-body">
    @php
        $user = auth()->user();
        $username = $user->username ?: 'collector';
        $formatMoney = fn (float|int $value) => 'PHP '.number_format((float) $value, 0);
        $card = $userCard->card;

        $imagePath = $userCard->photo_path
            ? asset('storage/' . $userCard->photo_path)
            : asset('storage/cards/' . ($card->thumbnail_style ?? ''));

        $collectorName = auth()->user()->name ?? auth()->user()->username ?? 'Collector';
    @endphp

    <main class="dashboard-shell">
        <!-- Sidebar Section -->
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
                <a href="{{ route('marketplace.index') }}" class="sidebar-link ">Marketplace</a>
                <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                <a href="{{ route('messages.index') }}" class="sidebar-link">Messages</a>
                <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
            </nav>
        </aside>
        

        <!-- Main Content Section -->
        <section class="dashboard-CardDetails/main">
            <div class="content">
                <div class="card-details-container">
                    <!-- Card Image Section -->
                    <div class="card-image">
                        <img src="{{ $imagePath }}" alt="{{ $card->title }}">
                    </div>

                    <!-- Card Information Section -->
                    <div class="card-info">

                        <p class="dashboard-kicker">Card Details</p>
                        
                        <a href="{{ route('collection.index') }}" class="back-btn">← Back to Collection</a>

                        <div class="info-row">
                            <div class="info-label">Artist / Group</div>
                            <div class="info-value">{{ $card->artist ?? 'Not Available' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Card Title</div>
                            <div class="info-value">{{ $card->title ?? 'Not Available' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Album</div>
                            <div class="info-value">{{ $card->album ?? 'Not Available' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Edition</div>
                            <div class="info-value">{{ $card->edition ?? 'Not Available' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Rarity</div>
                            <div class="info-value">{{ $card->rarity ?? 'Not Available' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Market Value</div>
                            <div class="info-value">PHP {{ number_format((float) ($card->market_value ?? 0), 2) }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Purchase Price</div>
                            <div class="info-value">PHP {{ number_format((float) ($userCard->purchase_price ?? 0), 2) }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Estimated Value</div>
                            <div class="info-value">PHP {{ number_format((float) ($userCard->estimated_value ?? 0), 2) }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Condition</div>
                            <div class="info-value">{{ $userCard->condition ?? 'Not Available' }}</div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Acquired At</div>
                            <div class="info-value">
                                {{ $userCard->acquired_at ? \Carbon\Carbon::parse($userCard->acquired_at)->format('m/d/Y') : 'Not Available' }}
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-label">Notes</div>
                            <div class="info-value">{{ $userCard->notes ?? 'No notes added.' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <a href="{{ route('collection.edit', $userCard) }}" class="edit-btn">✎</a>
</body>
</html>