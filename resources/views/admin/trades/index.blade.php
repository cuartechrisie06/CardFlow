@extends('layouts.admin')

@section('title', 'Admin Trades')

@section('content')
@php
    $tradeStatus = fn ($status) => match ($status) {
        'completed' => ['Completed', '#2d6a4f', 'rgba(45,106,79,0.1)'],
        'pending', 'accepted' => [ucfirst($status), '#8B4513', 'rgba(243,230,219,0.84)'],
        'declined', 'cancelled' => [ucfirst($status), '#A32D2D', 'rgba(163,45,45,0.08)'],
        default => [ucfirst((string) $status), '#8B6F5E', 'rgba(139,111,94,0.12)'],
    };
@endphp

<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>Trades</h1>
        <p class="dashboard-intro">Trade activity between collectors across CardFlow.</p>
    </div>
</header>

@if($trades->isEmpty())
    <section class="dashboard-card">
        <div class="empty-state" style="min-height:180px;background:linear-gradient(180deg, rgba(247,242,237,0.95), rgba(243,237,230,0.9));border:1px solid rgba(237,227,217,0.95);border-radius:1.35rem;">
            <p style="font-family:'Playfair Display',serif;font-size:1.2rem;color:#3d2b1f;">No trades yet</p>
            <span>Trade activity between users will appear here.</span>
        </div>
    </section>
@else
    <section class="dashboard-card" style="padding:0;overflow:hidden;border-radius:12px;border:0.5px solid rgba(92,61,46,0.12);background:rgba(255,251,246,0.94);">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 0.8fr 0.9fr auto;gap:16px;padding:13px 20px;background:#ede3d5;border-bottom:0.5px solid rgba(92,61,46,0.12);">
            @foreach(['TRADE', 'OFFERER', 'RECEIVER', 'STATUS', 'DATE', 'ACTIONS'] as $col)
                <p class="stat-label" style="margin:0;font-size:11px;color:rgba(143,90,62,0.72);">{{ $col }}</p>
            @endforeach
        </div>

        @foreach($trades as $trade)
            @php
                [$label, $color, $bg] = $tradeStatus($trade->status);
            @endphp
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 0.8fr 0.9fr auto;gap:16px;padding:15px 20px;border-bottom:0.5px solid rgba(92,61,46,0.08);align-items:center;background:rgba(255,251,246,0.72);">
                <div>
                    <p style="font-size:0.9rem;font-weight:700;color:#3d2b1f;margin:0;">{{ $trade->offeredCard?->title ?? 'Offered card' }}</p>
                    <p style="font-size:0.76rem;color:#8B6F5E;margin:2px 0 0;">for {{ $trade->listing?->card?->title ?? 'listing card' }}</p>
                </div>
                <p style="font-size:0.82rem;color:#8B4513;margin:0;">{{ '@'.($trade->sender?->username ?? 'sender') }}</p>
                <p style="font-size:0.82rem;color:#8B4513;margin:0;">{{ '@'.($trade->receiver?->username ?? 'receiver') }}</p>
                <span class="mini-chip" style="justify-self:start;color:{{ $color }};background:{{ $bg }};">{{ $label }}</span>
                <p style="font-size:0.82rem;color:#8B6F5E;margin:0;">{{ $trade->created_at->format('M d, Y') }}</p>
                <a href="{{ route('admin.trades') }}" class="mini-chip" style="border:1px solid rgba(139,69,19,0.28);color:#8B4513;text-decoration:none;">View</a>
            </div>
        @endforeach
    </section>
@endif
@endsection
