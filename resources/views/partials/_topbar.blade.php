<header class="dashboard-header">
    <div>
        @hasSection('page_kicker')
            <p class="dashboard-kicker">@yield('page_kicker')</p>
        @endif

        @hasSection('page_title')
            <h1>@yield('page_title')</h1>
        @endif

        @hasSection('page_intro')
            <p class="dashboard-intro">@yield('page_intro')</p>
        @endif
    </div>

    @hasSection('page_actions')
        <div class="dashboard-actions">
            @yield('page_actions')
        </div>
    @endif
</header>
