@extends('layouts.app')

@section('title', 'CardFlow | Explorer')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
    @php
        $activeItems = $tab === 'idols' ? $idols : $groups;
        $showUnavailable = ! $kpopOk;
        $showNoMatches = $kpopOk && count($activeItems) === 0;
        $showNoMatchesSearch = $showNoMatches && $search !== '';
        $entityLabel = $tab === 'idols' ? 'idols' : 'groups';

        // Catalog mode uses query params but does not require controller/route changes.
        $catalogId = (string) request()->query('catalog');
        $catalogMode = $catalogId !== '';
        $catalogName = (string) request()->query('name', $catalogId !== '' ? $catalogId : '');
        $catalogSubtitle = 'Browse complete photocard catalogs. View all variants with community-owned counts and average trade values.';
    @endphp

    <header class="dashboard-header marketplace-header kpop-explorer-header">
        <div class="kpop-explorer-header__inner">
            <p class="dashboard-kicker">K-Pop explorer</p>
            <h1>
                @if ($catalogMode)
                    {{ $catalogName }} Catalog
                @else
                    Browse idols &amp; groups
                @endif
            </h1>
            <p class="dashboard-intro kpop-explorer-header__intro">
                @if ($catalogMode)
                    {{ $catalogSubtitle }}
                @else
                    Public <strong>kpopnet</strong> data (CC0) via unpkg — no API key, not stored in CardFlow.
                @endif
            </p>
        </div>

        @if ($catalogMode)
            <div class="dashboard-actions kpop-explorer-catalog-back">
                <a href="{{ route('explorer.index', array_filter(['search' => $search !== '' ? $search : null, 'tab' => $tab])) }}" class="kpop-explorer-catalog-back-button" aria-label="Back to Explorer">
                    ← Back to Explorer
                </a>
            </div>
        @endif
    </header>
    <div class="kpop-explorer-header__rule" aria-hidden="true"></div>

    <section class="dashboard-card kpop-explorer-panel">
        @if (! $catalogMode)
            <form method="GET" action="{{ route('explorer.index') }}" class="kpop-explorer-search kpop-explorer-search--wide" role="search">
                <label class="kpop-explorer-search__field">
                    <span class="kpop-explorer-search__icon" aria-hidden="true">🔍</span>
                    <span class="sr-only">Search by name</span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by name…"
                        autocomplete="off"
                    >
                </label>
                <input type="hidden" name="tab" value="{{ $tab }}">
                <button type="submit" class="kpop-explorer-search__submit">Search</button>
            </form>

            <nav class="kpop-explorer-tabs kpop-explorer-tabs--wide" aria-label="Explorer tabs">
                <a
                    href="{{ route('explorer.index', array_filter(['search' => $search !== '' ? $search : null, 'tab' => 'idols'])) }}"
                    class="kpop-explorer-tab {{ $tab === 'idols' ? 'is-active' : '' }}"
                >Idols</a>
                <a
                    href="{{ route('explorer.index', array_filter(['search' => $search !== '' ? $search : null, 'tab' => 'groups'])) }}"
                    class="kpop-explorer-tab {{ $tab === 'groups' ? 'is-active' : '' }}"
                >Groups</a>
            </nav>
        @else
            <nav class="kpop-explorer-catalog-subnav" aria-label="Catalog sorting">
                <a href="{{ route('explorer.catalogs.show', \Illuminate\Support\Str::slug($catalogName)) }}?sort=owned" class="kpop-explorer-pill-button is-active">Most Owned</a>
                <a href="{{ route('explorer.catalogs.show', \Illuminate\Support\Str::slug($catalogName)) }}?sort=value" class="kpop-explorer-pill-button">Highest Value</a>
                <a href="{{ route('explorer.catalogs.show', \Illuminate\Support\Str::slug($catalogName)) }}?sort=new" class="kpop-explorer-pill-button">Newest Release</a>
            </nav>
        @endif

        @if (session('status'))
            <div class="auth-status">{{ session('status') }}</div>
        @endif

        @if ($showUnavailable)
            <div class="kpop-explorer-empty collection-empty collection-empty-rich" role="status">
                <div class="collection-empty-icon" aria-hidden="true">◎</div>
                <h3>K-Pop database is currently unavailable.</h3>
                <p>Please try again later.</p>
            </div>
        @elseif ($catalogMode)
            <div class="kpop-explorer-grid kpop-explorer-grid--photocards kpop-photocard-catalog-grid" aria-label="Photocard catalog">
                @php
                    // Index page currently only has idol/group lists.
                    // This scaffold expects your existing template variables to populate $variantCards.
                    $variantCards = collect();
                @endphp

                @forelse ($variantCards as $variant)
                    <article class="kpop-photocard-card" data-variant-key="{{ $variant['version_name'] ?? $variant['edition'] ?? $variant['title'] ?? '' }}">
                        <div class="kpop-photocard-card__image">
                            @if (!empty($variant['image_url'] ?? $variant['image'] ?? null))
                                <img
                                    class="kpop-photocard-card__img"
                                    src="{{ $variant['image_url'] ?? $variant['image'] }}"
                                    alt="{{ $variant['version_name'] ?? $variant['edition'] ?? $variant['title'] ?? 'Photocard' }}"
                                    loading="lazy"
                                >
                            @else
                                <div class="kpop-photocard-card__img-placeholder" role="img" aria-label="{{ $variant['version_name'] ?? $variant['edition'] ?? $variant['title'] ?? 'Photocard' }}">
                                    <span class="kpop-photocard-card__img-placeholder-text">
                                        {{ $variant['version_name'] ?? $variant['edition'] ?? $variant['title'] ?? 'Unknown Ver.' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="kpop-photocard-card__badge" aria-label="Collection Count">
                            ● {{ (int)($variant['collection_count'] ?? $variant['owned_count'] ?? 0) }} Owned
                        </div>

                        <div class="kpop-photocard-card__content">
                            <h3 class="kpop-photocard-card__label">
                                {{ $variant['version_name'] ?? $variant['edition'] ?? $variant['title'] ?? 'Unknown Ver.' }}
                            </h3>

                            <div class="kpop-photocard-card__market">
                                <div class="kpop-photocard-card__market-label">Avg. Trade Value</div>
                                <div class="kpop-photocard-card__market-value">
                                    {{ $variant['avg_trade_value'] ?? $variant['avg_value'] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="kpop-explorer-empty kpop-explorer-empty--search collection-empty collection-empty-rich" role="status">
                        <div class="kpop-explorer-empty__icon" aria-hidden="true">✦</div>
                        <h3>No catalog variants loaded yet.</h3>
                        <p>Return to the idol/group list to select a catalog.</p>
                    </div>
                @endforelse
            </div>
        @elseif ($showNoMatchesSearch)
            <div class="kpop-explorer-empty kpop-explorer-empty--search collection-empty collection-empty-rich" role="status">
                <div class="kpop-explorer-empty__icon" aria-hidden="true">✦</div>
                <h3>No {{ $entityLabel }} found for ‘{{ $search }}’</h3>
                <p>Try a different spelling or clear the search.</p>
                <a href="{{ route('explorer.index', ['tab' => $tab]) }}" class="dashboard-add-card">Show all</a>
            </div>
        @elseif ($showNoMatches)
            <div class="kpop-explorer-empty kpop-explorer-empty--search collection-empty collection-empty-rich" role="status">
                <div class="kpop-explorer-empty__icon" aria-hidden="true">✦</div>
                <h3>No matching {{ $entityLabel }}.</h3>
                <p>Try a different search, or show the full list.</p>
                <a href="{{ route('explorer.index', ['tab' => $tab]) }}" class="dashboard-add-card">Show all</a>
            </div>
        @elseif ($tab === 'idols')
            <div class="kpop-explorer-grid kpop-explorer-grid--cards">
                @foreach ($idols as $idol)
                    <a
                        href="{{ route('explorer.index', array_filter(['tab' => $tab, 'search' => $search !== '' ? $search : null, 'catalog' => $idol['name'] ?? '', 'name' => $idol['name'] ?? ''])) }}"
                        class="kpop-explorer-card-link"
                        aria-label="Open {{ $idol['name'] ?? 'Idol' }} catalog"
                    >
                        <article class="kpop-card kpop-card--idol">
                            <div class="kpop-card__top">
                                <div class="kpop-card__avatar" aria-hidden="true">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($idol['name'] ?? '?', 0, 1)) }}
                                </div>
                                <h2 class="kpop-card__title">{{ $idol['name'] ?? 'Unknown' }}</h2>
                            </div>

                            <dl class="kpop-card__meta">
                                <div class="kpop-card__row">
                                    <dt>Birth date</dt>
                                    <dd>{{ $idol['birth_date'] ?? '—' }}</dd>
                                </div>
                                <div class="kpop-card__row">
                                    <dt>Debut</dt>
                                    <dd>{{ $idol['debut_date'] ?? '—' }}</dd>
                                </div>
                                <div class="kpop-card__row kpop-card__row--group">
                                    <dt>Group</dt>
                                    <dd>
                                        @if (($idol['group_display'] ?? '—') !== '—')
                                            <span class="kpop-card__group-pill">{{ $idol['group_display'] }}</span>
                                        @else
                                            <span class="kpop-card__value-muted">—</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>

                            <span class="kpop-card__source">kpopnet · CC0</span>
                        </article>
                    </a>
                @endforeach
            </div>
        @else
            <div class="kpop-explorer-grid kpop-explorer-grid--cards">
                @foreach ($groups as $group)
                    <a
                        href="{{ route('explorer.index', array_filter(['tab' => $tab, 'search' => $search !== '' ? $search : null, 'catalog' => $group['name'] ?? '', 'name' => $group['name'] ?? ''])) }}"
                        class="kpop-explorer-card-link"
                        aria-label="Open {{ $group['name'] ?? 'Group' }} catalog"
                    >
                        <article class="kpop-card kpop-card--group">
                            <div class="kpop-card__top">
                                <div class="kpop-card__avatar kpop-card__avatar--group" aria-hidden="true">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($group['name'] ?? '?', 0, 1)) }}
                                </div>
                                <h2 class="kpop-card__title">{{ $group['name'] ?? 'Unknown' }}</h2>
                            </div>

                            <dl class="kpop-card__meta">
                                <div class="kpop-card__row">
                                    <dt>Debut</dt>
                                    <dd>{{ $group['debut_date'] ?? '—' }}</dd>
                                </div>
                                @if (isset($group['member_count']) && $group['member_count'] !== null)
                                    <div class="kpop-card__row">
                                        <dt>Members</dt>
                                        <dd>{{ number_format((int) $group['member_count']) }}</dd>
                                    </div>
                                @endif
                            </dl>

                            <span class="kpop-card__source">kpopnet · CC0</span>
                        </article>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection
