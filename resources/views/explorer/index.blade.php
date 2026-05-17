@extends('layouts.app')

@section('title', 'CardFlow | Explorer')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
    @php
        $catalogId = (string) request()->query('catalog');
        $catalogMode = $catalogId !== '';
        $catalogName = (string) request()->query('name', $catalogId !== '' ? $catalogId : '');
        $catalogSubtitle = 'Browse complete photocard catalogs. View all variants with community-owned counts and average trade values.';
        $isIdolTab = $tab === 'idols';
        $entityLabel = $isIdolTab ? 'idols' : 'groups';
        $activeFilter = (string) request()->query('filter', 'all');
        $quickFilters = [
            'all' => 'All',
            'girl_groups' => 'Girl Groups',
            'boy_groups' => 'Boy Groups',
            'solo' => 'Solo',
            '4th_gen' => '4th Gen',
            '3rd_gen' => '3rd Gen',
        ];
        $displayResults = $results->getCollection()->filter(function ($item) use ($activeFilter, $isIdolTab) {
            if ($activeFilter === 'all') {
                return true;
            }

            $gender = strtolower((string) ($item->gender ?? ''));
            $debutYear = $item->debut_date ? (int) $item->debut_date->format('Y') : null;
            $isSolo = $isIdolTab
                ? trim((string) ($item->group_name ?? '')) === ''
                : ((int) ($item->member_count ?? 0)) <= 1;

            return match ($activeFilter) {
                'girl_groups' => str_starts_with($gender, 'f'),
                'boy_groups' => str_starts_with($gender, 'm'),
                'solo' => $isSolo,
                '4th_gen' => $debutYear !== null && $debutYear >= 2018,
                '3rd_gen' => $debutYear !== null && $debutYear >= 2012 && $debutYear <= 2017,
                default => true,
            };
        })->values();
        $hasResults = $displayResults->isNotEmpty();
    @endphp

    <header class="dashboard-header marketplace-header kpop-explorer-header">
        <div class="kpop-explorer-header__inner">
            <p class="dashboard-kicker">K-Pop explorer</p>
            <h1>
                @if ($catalogMode)
                    {{ $catalogName }} Catalog
                @else
                    Photocard catalog browser
                @endif
            </h1>
            <p class="dashboard-intro kpop-explorer-header__intro">
                @if ($catalogMode)
                    {{ $catalogSubtitle }}
                @else
                    Browse complete photocard catalogs by group or idol. View community-owned counts, average trade values, and active listings.
                @endif
            </p>
        </div>

        @if ($catalogMode)
            <div class="dashboard-actions kpop-explorer-catalog-back">
                <a href="{{ route('explorer.index', array_filter(['search' => $search !== '' ? $search : null, 'tab' => $tab])) }}" class="kpop-explorer-catalog-back-button" aria-label="Back to Explorer">
                    Back to Explorer
                </a>
            </div>
        @endif
    </header>
    <div class="kpop-explorer-header__rule" aria-hidden="true"></div>

    <section class="dashboard-card kpop-explorer-panel explorer-panel">
        <div class="explorer-content">
        @if (! $catalogMode)
            <form method="GET" action="{{ route('explorer.index') }}" class="kpop-explorer-search kpop-explorer-search--wide explorer-search-bar" role="search">
                <label class="kpop-explorer-search__field">
                    <span class="sr-only">Search by name</span>
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search idol, group, company..."
                        autocomplete="off"
                    >
                </label>
                <input type="hidden" name="tab" value="{{ $tab }}">
                <button type="submit" class="kpop-explorer-search__submit">Search</button>
            </form>

            <div class="explorer-toolbar">
                <nav class="kpop-explorer-tabs kpop-explorer-tabs--wide" aria-label="Explorer tabs">
                    <a
                        href="{{ route('explorer.index', array_filter(['search' => $search !== '' ? $search : null, 'tab' => 'idols', 'filter' => $activeFilter !== 'all' ? $activeFilter : null])) }}"
                        class="kpop-explorer-tab {{ $tab === 'idols' ? 'is-active' : '' }}"
                    >Idols</a>
                    <a
                        href="{{ route('explorer.index', array_filter(['search' => $search !== '' ? $search : null, 'tab' => 'groups', 'filter' => $activeFilter !== 'all' ? $activeFilter : null])) }}"
                        class="kpop-explorer-tab {{ $tab === 'groups' ? 'is-active' : '' }}"
                    >Groups</a>
                </nav>

                <div class="explorer-results-meta">
                    Showing {{ number_format($displayResults->count()) }} of {{ number_format($results->total()) }} {{ $entityLabel }}
                </div>
            </div>

            <div class="filter-chips-row explorer-filters">
                <span class="filter-chips-label">Filter:</span>
                <nav class="explorer-filter-chips" aria-label="Quick filters">
                    @foreach ($quickFilters as $value => $label)
                        <a
                            href="{{ route('explorer.index', array_filter([
                                'tab' => $tab,
                                'search' => $search !== '' ? $search : null,
                                'filter' => $value !== 'all' ? $value : null,
                            ])) }}"
                            class="filter-chip {{ $activeFilter === $value ? 'active' : '' }}"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif
        </div>

        @if (! $hasResults)
            <div class="kpop-explorer-empty collection-empty collection-empty-rich" role="status">
                <div class="kpop-explorer-empty__icon" aria-hidden="true">✦</div>
                <h3>
                    {{ $search !== '' ? "No {$entityLabel} found for '{$search}'" : "No {$entityLabel} found." }}
                </h3>
                <p>Try a different name, group, company, or country.</p>
                <a href="{{ route('explorer.index', ['tab' => $tab]) }}" class="dashboard-add-card">Clear search</a>
            </div>
        @else
            <div class="explorer-grid-wrapper">
            <div class="kpop-explorer-grid kpop-explorer-grid--catalog">
                @foreach ($displayResults as $item)
                    @php
                        $catalogName = $isIdolTab ? (string) $item->stage_name : (string) $item->name;
                        $cardQuery = \App\Models\Card::query()->where('artist', 'like', "%{$catalogName}%");
                        $ownedCount = (clone $cardQuery)->count();
                        $avgValue = (float) ((clone $cardQuery)->avg('market_value') ?? 0);
                        $listedCount = \App\Models\MarketplaceListing::query()
                            ->activeVisible()
                            ->whereHas('card', fn ($query) => $query->where('artist', 'like', "%{$catalogName}%"))
                            ->count();
                        $modalId = 'explorer-profile-' . $item->id;
                    @endphp

                    @if ($isIdolTab)
                        <article
                            class="explorer-catalog-card explorer-catalog-card--clickable"
                            role="button"
                            tabindex="0"
                            data-explorer-modal-open="{{ $modalId }}"
                        >
                            <span class="explorer-db-badge">CardFlow DB</span>

                            <div class="explorer-card-identity">
                                <div class="kpop-card__avatar" aria-hidden="true">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->stage_name ?? '?', 0, 1)) }}
                                </div>

                                <div class="explorer-card-nameblock">
                                    <h2>{{ $item->stage_name ?? 'Unknown' }}</h2>
                                    <p>{{ $item->full_name ?: 'Full name unavailable' }}</p>

                                    @if ($item->group_name)
                                        <span class="kpop-card__group-pill">{{ $item->group_name }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="explorer-stat-row">
                                <div class="explorer-stat-chip">
                                    <span class="explorer-stat-label">Owned in app</span>
                                    <strong class="explorer-stat-value">{{ number_format($ownedCount) }}</strong>
                                </div>
                                <div class="explorer-stat-chip">
                                    <span class="explorer-stat-label">Avg value</span>
                                    <strong class="explorer-stat-value">PHP {{ number_format($avgValue, 0) }}</strong>
                                </div>
                                <div class="explorer-stat-chip">
                                    <span class="explorer-stat-label">Listed</span>
                                    <strong class="explorer-stat-value">{{ number_format($listedCount) }}</strong>
                                </div>
                            </div>

                            <div class="explorer-card-actions">
                                <a onclick="event.stopPropagation()" href="{{ route('collection.index', ['q' => $item->stage_name]) }}">View Cards</a>
                                <a onclick="event.stopPropagation()" href="{{ route('marketplace.index', ['q' => $item->stage_name]) }}">Browse Listings</a>
                            </div>
                        </article>

                        <div id="{{ $modalId }}" class="explorer-modal" hidden>
                            <div class="explorer-modal-backdrop" data-explorer-modal-close></div>

                            <article class="explorer-modal-card">
                                <button type="button" class="explorer-modal-close" data-explorer-modal-close aria-label="Close idol profile">&times;</button>

                                <div class="explorer-modal-header">
                                    <div class="explorer-modal-avatar">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($item->stage_name ?? '?', 0, 1)) }}
                                    </div>

                                    <div>
                                        <p class="mini-label">Idol profile</p>
                                        <h2>{{ $item->stage_name ?? 'Unknown' }}</h2>
                                        <p>{{ $item->full_name ?: 'Full name unavailable' }}</p>
                                    </div>
                                </div>

                                <dl class="explorer-modal-grid">
                                    <div><dt>Korean name</dt><dd>{{ $item->korean_name ?: '-' }}</dd></div>
                                    <div><dt>Group</dt><dd>{{ $item->group_name ?: '-' }}</dd></div>
                                    <div><dt>Company</dt><dd>{{ $item->company ?: '-' }}</dd></div>
                                    <div><dt>Country</dt><dd>{{ $item->country ?: '-' }}</dd></div>
                                    <div><dt>Height</dt><dd>{{ $item->height ? $item->height . ' cm' : '-' }}</dd></div>
                                    <div><dt>Debut</dt><dd>{{ $item->debut_date?->format('Y-m-d') ?: '-' }}</dd></div>
                                    <div><dt>Birth date</dt><dd>{{ $item->birth_date?->format('Y-m-d') ?: '-' }}</dd></div>
                                </dl>

                                <div class="explorer-stat-row">
                                    <div class="explorer-stat-chip">
                                        <span class="explorer-stat-label">Owned</span>
                                        <strong class="explorer-stat-value">{{ number_format($ownedCount) }}</strong>
                                    </div>
                                    <div class="explorer-stat-chip">
                                        <span class="explorer-stat-label">Avg value</span>
                                        <strong class="explorer-stat-value">PHP {{ number_format($avgValue, 0) }}</strong>
                                    </div>
                                    <div class="explorer-stat-chip">
                                        <span class="explorer-stat-label">Listed</span>
                                        <strong class="explorer-stat-value">{{ number_format($listedCount) }}</strong>
                                    </div>
                                </div>

                                <a href="{{ route('collection.index', ['q' => $item->stage_name]) }}" class="dashboard-add-card">
                                    Browse their cards
                                </a>
                            </article>
                        </div>
                    @else
                        <article class="explorer-catalog-card">
                            <span class="explorer-db-badge">CardFlow DB</span>

                            <div class="explorer-card-nameblock explorer-card-nameblock--group">
                                <h2>{{ $item->name ?? 'Unknown' }}</h2>
                                <p>{{ $item->company ?: 'Company unavailable' }}</p>
                            </div>

                            <dl class="explorer-card-meta">
                                <div><dt>Debut</dt><dd>{{ $item->debut_date?->format('Y-m-d') ?: '-' }}</dd></div>
                                <div><dt>Members</dt><dd>{{ $item->member_count !== null ? number_format((int) $item->member_count) : '-' }}</dd></div>
                            </dl>

                            <div class="explorer-stat-row">
                                <div class="explorer-stat-chip">
                                    <span class="explorer-stat-label">Owned in app</span>
                                    <strong class="explorer-stat-value">{{ number_format($ownedCount) }}</strong>
                                </div>
                                <div class="explorer-stat-chip">
                                    <span class="explorer-stat-label">Avg value</span>
                                    <strong class="explorer-stat-value">PHP {{ number_format($avgValue, 0) }}</strong>
                                </div>
                                <div class="explorer-stat-chip">
                                    <span class="explorer-stat-label">Listed</span>
                                    <strong class="explorer-stat-value">{{ number_format($listedCount) }}</strong>
                                </div>
                            </div>

                            <div class="explorer-card-actions">
                                <a href="{{ route('collection.index', ['q' => $item->name]) }}">Browse {{ $item->name }} Cards</a>
                            </div>
                        </article>
                    @endif
                @endforeach
            </div>
            </div>

            <div class="explorer-pagination">
                {{ $results->appends(request()->query())->links() }}
            </div>
        @endif
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const openModal = function (modalId) {
        const modal = document.getElementById(modalId);

        if (!modal) {
            return;
        }

        modal.hidden = false;
        document.body.classList.add('has-open-modal');
    };

    const closeModal = function (modal) {
        modal.hidden = true;
        document.body.classList.remove('has-open-modal');
    };

    document.querySelectorAll('[data-explorer-modal-open]').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            openModal(trigger.dataset.explorerModalOpen);
        });

        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openModal(trigger.dataset.explorerModalOpen);
            }
        });
    });

    document.querySelectorAll('.explorer-modal').forEach(function (modal) {
        modal.querySelectorAll('[data-explorer-modal-close]').forEach(function (closeTrigger) {
            closeTrigger.addEventListener('click', function () {
                closeModal(modal);
            });
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('.explorer-modal:not([hidden])').forEach(closeModal);
    });
});
</script>
@endpush
