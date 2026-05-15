@props([
    'contextLabel' => '',
    'eyebrow' => '',
    'pageTitle' => '',
    'subtitle' => '',
    'backUrl' => '#',
    'backLabel' => 'Back',
    'imageUrl' => '',
    'imageAlt' => '',
    'rarityLabel' => '',
    'rarityClass' => '',
    'artistName' => '',
    'cardTitle' => '',
    'primaryMeta' => [],
    'secondaryMeta' => [],
    'priceTiles' => [],
    'priceSummaryLabel' => null,
    'priceSummaryValue' => null,
    'priceSummaryTone' => 'is-positive',
])

<header class="dashboard-header marketplace-header card-details-header">
    <div>
        <p class="dashboard-kicker">{{ $contextLabel }}</p>
        <p class="card-details-eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $pageTitle }}</h1>
        <p class="dashboard-intro">{{ $subtitle }}</p>
    </div>

    <div class="dashboard-actions card-details-actions">
        <a href="{{ $backUrl }}" class="card-details-back-button">{{ $backLabel }}</a>
        {{ $actions ?? '' }}
    </div>
</header>

<section class="dashboard-card card-detail-shell card-detail-shell-premium">
    <div class="card-detail-media-column">
        <div class="card-detail-media-frame rarity-{{ $rarityClass }}">
            <div class="card-detail-media card-detail-media-premium">
                <img
                    src="{{ $imageUrl }}"
                    alt="{{ $imageAlt }}"
                    class="card-detail-media-image"
                    onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                >
                <div class="card-detail-media-overlay"></div>
                <span class="card-detail-rarity-badge">{{ $rarityLabel }}</span>
            </div>
        </div>
    </div>

    <div class="card-detail-copy card-detail-copy-premium">
        <section class="card-detail-hero card-detail-fade" style="--card-detail-delay: 0ms;">
            <p class="mini-label">Artist / Group</p>
            <h2>{{ $artistName }}</h2>
            <p class="card-detail-title-display">{{ $cardTitle }}</p>
        </section>

        <div class="card-detail-divider"></div>

        @if (! empty($primaryMeta))
            <section class="card-detail-chip-grid card-detail-fade" style="--card-detail-delay: 50ms;">
                @foreach ($primaryMeta as $tile)
                    <x-chip-card :label="$tile['label']" :value="$tile['value']" />
                @endforeach
            </section>
        @endif

        @if (! empty($secondaryMeta))
            <section class="card-detail-support-grid card-detail-fade" style="--card-detail-delay: 100ms;">
                @foreach ($secondaryMeta as $tile)
                    <x-chip-card :label="$tile['label']" :value="$tile['value']" />
                @endforeach
            </section>
        @endif

        @if (! empty($priceTiles) || $priceSummaryLabel)
            <div class="card-detail-divider"></div>

            <section class="card-financial-summary card-detail-fade" style="--card-detail-delay: 150ms;">
                @if (! empty($priceTiles))
                    <div class="card-financial-grid">
                        @foreach ($priceTiles as $tile)
                            <x-chip-card :label="$tile['label']" :value="$tile['value']">
                                @if (! empty($tile['trendText']))
                                    <span class="trend {{ $tile['trendClass'] ?? 'stable' }}" @if(!empty($tile['trendTitle'])) title="{{ $tile['trendTitle'] }}" @endif>
                                        {{ $tile['trendText'] }}
                                    </span>
                                @endif
                            </x-chip-card>
                        @endforeach
                    </div>
                @endif

                @if ($priceSummaryLabel && $priceSummaryValue)
                    <div class="card-detail-profit {{ $priceSummaryTone }}">
                        <span class="summary-label">{{ $priceSummaryLabel }}</span>
                        <strong>{{ $priceSummaryValue }}</strong>
                    </div>
                @endif
            </section>
        @endif

        {{ $slot }}
    </div>
</section>
