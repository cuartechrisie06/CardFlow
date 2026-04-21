<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Dashboard</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>CardFlow</p>
                        <span>Photocard Trading</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link is-active">Dashboard</a>
                    <a href="#" class="sidebar-link">My Collection</a>
                    <a href="#" class="sidebar-link">Marketplace</a>
                    <a href="#" class="sidebar-link">Alerts</a>
                    <a href="#" class="sidebar-link">Messages</a>
                    <a href="#" class="sidebar-link">Explore</a>
                    <a href="#" class="sidebar-link">Insights</a>
                </nav>

                <div class="sidebar-collector">
                    <span class="collector-label">Collector</span>
                    <div class="collector-card">
                        <div class="collector-avatar">C</div>
                        <div>
                            <p>Chrissie</p>
                            <span>Collector</span>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header">
                    <div>
                        <p class="dashboard-kicker">Dashboard</p>
                        <h1>Welcome back to your collection</h1>
                        <p class="dashboard-intro">
                            Personally curated insights, trade leads, and collection status updates all in one active wishlist workspace.
                        </p>
                    </div>

                    <div class="dashboard-actions">
                        <label class="dashboard-search">
                            <span class="sr-only">Search cards</span>
                            <input type="search" placeholder="Search cards, users, sets...">
                        </label>
                        <a href="#" class="dashboard-add-card">+ Add card</a>
                    </div>
                </header>

                <section class="stats-grid" aria-label="Collection stats">
                    <article class="stat-card">
                        <span class="stat-label">Total cards</span>
                        <div class="stat-value">247</div>
                        <div class="stat-note">+12 this week</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Collection value</span>
                        <div class="stat-value">₱18,450</div>
                        <div class="stat-note">+₱920 last week</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Active trades</span>
                        <div class="stat-value">8</div>
                        <div class="stat-note">3 awaiting reply</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Wishlist matches</span>
                        <div class="stat-value">14</div>
                        <div class="stat-note">5 high priority</div>
                    </article>
                </section>

                <section class="dashboard-grid">
                    <article class="dashboard-card card-chart card-wide">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Collection pulse</p>
                                <h2>Estimated value trend</h2>
                            </div>
                            <span class="mini-chip">Last 6 months</span>
                        </div>

                        <div class="line-chart" aria-hidden="true">
                            <div class="chart-months">
                                <span>Jan</span>
                                <span>Feb</span>
                                <span>Mar</span>
                                <span>Apr</span>
                                <span>May</span>
                                <span>Jun</span>
                            </div>
                            <svg viewBox="0 0 420 150" role="presentation">
                                <path d="M20 102 C70 92, 115 96, 160 80 S255 58, 310 72 S380 54, 400 44" />
                                <circle cx="20" cy="102" r="4" />
                                <circle cx="95" cy="92" r="4" />
                                <circle cx="170" cy="79" r="4" />
                                <circle cx="245" cy="60" r="4" />
                                <circle cx="320" cy="69" r="4" />
                                <circle cx="400" cy="44" r="4" />
                            </svg>
                        </div>

                        <div class="chart-summary">
                            <div>
                                <span class="summary-label">Peak month</span>
                                <strong>May</strong>
                            </div>
                            <div>
                                <span class="summary-label">Growth</span>
                                <strong>+18%</strong>
                            </div>
                            <div>
                                <span class="summary-label">Stability</span>
                                <strong>Low risk</strong>
                            </div>
                        </div>
                    </article>

                    <article class="dashboard-card card-status">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Trade mix</p>
                                <h2>Status distribution</h2>
                            </div>
                            <span class="mini-chip">This month</span>
                        </div>

                        <div class="status-total">
                            <span class="summary-label">Trades</span>
                            <strong>28</strong>
                        </div>

                        <div class="status-list">
                            <div class="status-row">
                                <span>Completed</span>
                                <div class="status-bar"><i style="width: 42%"></i></div>
                                <strong>42%</strong>
                            </div>
                            <div class="status-row">
                                <span>Pending</span>
                                <div class="status-bar"><i style="width: 24%"></i></div>
                                <strong>24%</strong>
                            </div>
                            <div class="status-row">
                                <span>New offers</span>
                                <div class="status-bar"><i style="width: 15%"></i></div>
                                <strong>15%</strong>
                            </div>
                            <div class="status-row">
                                <span>Cancelled</span>
                                <div class="status-bar"><i style="width: 11%"></i></div>
                                <strong>11%</strong>
                            </div>
                        </div>
                    </article>

                    <article class="dashboard-card card-activity">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Wishlist activity</p>
                                <h2>Match momentum</h2>
                            </div>
                            <span class="mini-chip">Top categories</span>
                        </div>

                        <div class="activity-chart-panel">
                            <div class="bar-chart" aria-hidden="true">
                                <div class="bar-chart-item"><i style="height: 58%"></i><span>IVE</span></div>
                                <div class="bar-chart-item"><i style="height: 72%"></i><span>Aespa</span></div>
                                <div class="bar-chart-item"><i style="height: 50%"></i><span>NJ</span></div>
                                <div class="bar-chart-item"><i style="height: 82%"></i><span>Le S</span></div>
                                <div class="bar-chart-item"><i style="height: 64%"></i><span>Twice</span></div>
                                <div class="bar-chart-item"><i style="height: 42%"></i><span>Itzy</span></div>
                            </div>
                        </div>

                        <div class="metric-strip">
                            <div>
                                <span class="summary-label">Strongest</span>
                                <strong>Le Sserafim</strong>
                            </div>
                            <div>
                                <span class="summary-label">Fresh matches</span>
                                <strong>6 today</strong>
                            </div>
                            <div>
                                <span class="summary-label">Avg. price</span>
                                <strong>₱120</strong>
                            </div>
                        </div>
                    </article>

                    <article class="dashboard-card card-feed">
                        <div class="card-topline">
                            <div>
                                <p class="mini-label">Recent activity</p>
                                <h2>Collection rhythm</h2>
                            </div>
                            <span class="mini-chip">Live feed</span>
                        </div>

                        <div class="feed-panel">
                            <ul class="activity-list">
                                <li>
                                    <strong>@kpop_collector wants to trade with you</strong>
                                    <span>2 minutes ago</span>
                                </li>
                                <li>
                                    <strong>You added 1 new card to your collection</strong>
                                    <span>18 minutes ago</span>
                                </li>
                                <li>
                                    <strong>Wishlist found Winter - Armageddon</strong>
                                    <span>Today, 9:24 AM</span>
                                </li>
                                <li>
                                    <strong>@aespa_stan completed a trade</strong>
                                    <span>Yesterday</span>
                                </li>
                            </ul>
                        </div>

                        <div class="feed-footer">
                            <div>
                                <span class="summary-label">Daily actions</span>
                                <strong>19</strong>
                            </div>
                            <div>
                                <span class="summary-label">Reply rate</span>
                                <strong>83%</strong>
                            </div>
                        </div>
                    </article>
                </section>

                <section class="dashboard-card market-card">
                    <div class="card-topline">
                        <div>
                            <p class="mini-label">Hot cards</p>
                            <h2>Trending in your circle</h2>
                        </div>
                        <span class="mini-chip">Updated hourly</span>
                    </div>

                    <div class="market-grid">
                        <article class="market-item">
                            <div class="market-thumb market-thumb-one"></div>
                            <div class="market-meta">
                                <h3>Mina - Fancy era</h3>
                                <p>Official set</p>
                                <div><span class="mini-chip">Mint</span><strong>₱1,450</strong></div>
                            </div>
                        </article>
                        <article class="market-item">
                            <div class="market-thumb market-thumb-two"></div>
                            <div class="market-meta">
                                <h3>Wonyoung - IVE Switch</h3>
                                <p>Lucky draw</p>
                                <div><span class="mini-chip">Rare</span><strong>₱2,200</strong></div>
                            </div>
                        </article>
                        <article class="market-item">
                            <div class="market-thumb market-thumb-three"></div>
                            <div class="market-meta">
                                <h3>Yujin - Frame card</h3>
                                <p>Seasonal release</p>
                                <div><span class="mini-chip">Hot</span><strong>₱990</strong></div>
                            </div>
                        </article>
                    </div>
                </section>
            </section>
        </main>
    </body>
</html>
