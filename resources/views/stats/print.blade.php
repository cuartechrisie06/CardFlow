<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Collection Stats Summary</title>
    @vite(['resources/css/app.css'])
</head>

<body class="stats-print-body">
    <div class="stats-print-toolbar">
        <a href="{{ route('stats.index') }}" class="stats-print-back-button">
            Back to Stats
        </a>

        <button onclick="window.print()" class="stats-print-button">
            Print / Save as PDF
        </button>
    </div>

    <main class="stats-print-report">
        <div class="stats-print-eyebrow">Collection Insights</div>

        <h1 class="stats-print-title">Collection Stats Summary</h1>

        <div class="stats-print-muted">
            Generated for {{ $user->name ?? $user->username ?? 'User' }}
            on {{ $generatedAt->format('F d, Y h:i A') }}
        </div>

        <section class="stats-print-grid">
            <div class="stats-print-card">
                <div class="stats-print-label">Total Value</div>
                <div class="stats-print-value">PHP {{ number_format($metrics['total_value'] ?? 0) }}</div>
                <p>Estimated collection value</p>
            </div>

            <div class="stats-print-card">
                <div class="stats-print-label">Completion Rate</div>
                <div class="stats-print-value">{{ $metrics['completion_rate'] ?? 0 }}%</div>
                <p>{{ $metrics['trade_total'] ?? 0 }} total trades tracked</p>
            </div>

            <div class="stats-print-card">
                <div class="stats-print-label">Successful Trades</div>
                <div class="stats-print-value">{{ $metrics['successful_trades'] ?? 0 }}</div>
                <p>Completed this week</p>
            </div>

            <div class="stats-print-card">
                <div class="stats-print-label">Average Trade Score</div>
                <div class="stats-print-value">{{ number_format($metrics['average_trade_score'] ?? 0, 2) }}</div>
                <p>Derived from trade outcomes</p>
            </div>
        </section>
    </main>
</body>
</html>