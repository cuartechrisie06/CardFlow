@extends('layouts.app')

@section('title', 'Collection Stats Summary')
@section('layout_mode', 'shellless')
@section('body_class', 'stats-print-body')

@php
    $totalValue = (float) ($metrics['total_value'] ?? 0);
    $totalSpent = (float) ($metrics['total_spent'] ?? 0);
    $netChange = (float) ($metrics['net_change'] ?? 0);
    $isPositive = $netChange >= 0;
    $completionRate = (int) ($metrics['completion_rate'] ?? 0);
    $totalCards = (int) ($metrics['total_cards'] ?? 0);
    $tradeTotal = (int) ($metrics['trade_total'] ?? 0);
@endphp

@section('content')
    <div class="stats-print-toolbar no-print">
        <a href="{{ route('stats.index') }}" class="stats-print-back-button">
            Back to Stats
        </a>

        <button onclick="window.print()" class="stats-print-button">
            Print / Save as PDF
        </button>
    </div>

    <main class="stats-print-report">
        <section class="stats-print-hero">
            <div>
                <div class="stats-print-eyebrow">Collection Stats</div>
                <h1 class="stats-print-title">Collection Stats Summary</h1>
                <p class="stats-print-muted">
                    Generated for {{ $user->name ?? $user->username ?? 'User' }}
                    on {{ $generatedAt->format('F d, Y h:i A') }}
                </p>
            </div>

            <div class="stats-print-seal">
                <span>{{ $totalCards }}</span>
                <small>cards tracked</small>
            </div>
        </section>

        <section class="stats-print-snapshot">
            <div>
                <span>Collector</span>
                <strong>{{ $user->name ?? $user->username ?? 'User' }}</strong>
            </div>
            <div>
                <span>Active listings</span>
                <strong>{{ $metrics['active_listings'] ?? 0 }}</strong>
            </div>
            <div>
                <span>Trades tracked</span>
                <strong>{{ $tradeTotal }}</strong>
            </div>
            <div>
                <span>Generated</span>
                <strong>{{ $generatedAt->format('M d, Y') }}</strong>
            </div>
        </section>

        <section class="stats-print-feature">
            <div>
                <span class="stats-print-label">Portfolio Value</span>
                <strong>PHP {{ number_format($totalValue) }}</strong>
                <p>Estimated value across your current collection.</p>
            </div>
            <div class="stats-print-progress">
                <span>Trade completion</span>
                <em>{{ $completionRate }}%</em>
                <i><b style="width: {{ min(100, max(0, $completionRate)) }}%"></b></i>
            </div>
        </section>

        <section class="stats-print-grid">
            <div class="stats-print-card stats-print-card-accent">
                <div class="stats-print-label">Total Spent</div>
                <div class="stats-print-value">PHP {{ number_format($totalSpent) }}</div>
                <p>Recorded purchase cost</p>
            </div>

            <div class="stats-print-card">
                <div class="stats-print-label">Net Gain / Loss</div>
                <div class="stats-print-value {{ $isPositive ? 'is-positive' : 'is-negative' }}">
                    {{ $isPositive ? '+' : '-' }}PHP {{ number_format(abs($netChange)) }}
                </div>
                <p>Estimated value minus total spent</p>
            </div>

            <div class="stats-print-card">
                <div class="stats-print-label">Successful Trades</div>
                <div class="stats-print-value">{{ $metrics['completed_trades'] ?? 0 }}</div>
                <p>{{ $metrics['successful_trades'] ?? 0 }} completed this week</p>
            </div>

            <div class="stats-print-card">
                <div class="stats-print-label">Average Card Value</div>
                <div class="stats-print-value">PHP {{ number_format($metrics['average_card_value'] ?? 0) }}</div>
                <p>Based on {{ $totalCards }} collection cards</p>
            </div>
        </section>

        <section class="stats-print-insights">
            <div>
                <span class="stats-print-label">Trade Score</span>
                <strong>{{ number_format($metrics['average_trade_score'] ?? 0, 2) }} / 5.00</strong>
                <p>Derived from completed trade outcomes.</p>
            </div>
            <div>
                <span class="stats-print-label">Trade Volume</span>
                <strong>{{ $metrics['trade_total'] ?? 0 }}</strong>
                <p>Total sent and received trade requests tracked.</p>
            </div>
        </section>

        <section class="stats-print-note">
            <div>
                <span class="stats-print-label">Summary</span>
                <p>
                    This report uses your current collection values, active marketplace listings,
                    and completed trade records at the time it was generated.
                </p>
            </div>
        </section>
    </main>
@endsection
