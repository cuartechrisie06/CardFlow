@extends('layouts.app')

@section('title', 'CardFlow | Account Access')
@section('layout_mode', 'shellless')
@section('body_class', 'cardflow-body')

@section('content')
@php
            $authMode = session('auth_mode');

            if (! $authMode) {
                $authMode = $errors->register->isNotEmpty() ? 'register' : 'signin';
            }

            if ($authMode === 'signup') {
                $authMode = 'register';
            }
@endphp
        <main class="cardflow-shell">
            <section class="hero-panel">
                <div class="brand-lockup brand-lockup--logo">
                    <img src="/images/cardflow-logo.svg" alt="CardFlow" class="brand-logo">
                </div>
                <div class="brand-chip">Photocard Trading</div>

                <div class="hero-copy">
                    <p class="eyebrow">CardFlow</p>
                    <h1>A calmer home for K-pop photocard trading.</h1>
                    <p class="hero-description">
                        Manage your collection, discover trusted trades, and keep your wishlist moving with a softer, more premium feel.
                    </p>
                </div>

                <div class="feature-list">
                    <article class="feature-card">
                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 4.5 19 12l-7 7-1.4-1.4 4.6-4.6H5v-2h10.2l-4.6-4.6Z" />
                            </svg>
                        </div>
                        <div>
                            <h2>Track with clarity</h2>
                            <p>Organize every card with tags, conditions, and collection notes in one quiet workspace.</p>
                        </div>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 2a1 1 0 0 1 1 1v2.1a7 7 0 1 1-6.2 3.7 1 1 0 0 1 1.74.98A5 5 0 1 0 13 7.1V9a1 1 0 1 1-2 0V3a1 1 0 0 1 1-1Z" />
                            </svg>
                        </div>
                        <div>
                            <h2>Trade with confidence</h2>
                            <p>Connect with collectors, review offers, and keep every exchange feeling secure and straightforward.</p>
                        </div>
                    </article>

                    <article class="feature-card">
                        <div class="feature-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" role="presentation">
                                <path d="M12 21s-6.7-4.35-9.23-8.2C.92 9.97 2.1 6.25 5.46 5.2A5.38 5.38 0 0 1 12 7.06 5.38 5.38 0 0 1 18.54 5.2c3.36 1.05 4.54 4.77 2.69 7.6C18.7 16.65 12 21 12 21Zm0-11.8-.8-1a3.37 3.37 0 0 0-5.14-1.09c-2.1 1.52-1.92 3.73-.73 5.43 1.62 2.3 5.28 5.19 6.67 6.23 1.39-1.04 5.05-3.93 6.67-6.23 1.19-1.7 1.37-3.91-.73-5.43A3.37 3.37 0 0 0 12.8 8.2l-.8 1Z" />
                            </svg>
                        </div>
                        <div>
                            <h2>Stay close to wishlist matches</h2>
                            <p>Receive timely updates when a wanted card appears, without the visual noise.</p>
                        </div>
                    </article>
                </div>
            </section>

            <section class="auth-panel" aria-label="Account access panel">
                <div class="auth-card" data-auth-card data-username-check-url="{{ route('username.check', [], false) }}">
                    <div class="auth-switch" role="tablist" aria-label="Authentication mode">
                        <a
                            href="{{ route('login', [], false) }}"
                            id="tab-signin"
                            role="tab"
                            class="auth-switch-button auth-tab {{ $authMode === 'signin' ? 'is-active active' : '' }}"
                            data-auth-trigger="signin"
                            aria-controls="form-signin"
                            aria-selected="{{ $authMode === 'signin' ? 'true' : 'false' }}"
                        >Sign in</a>
                        <a
                            href="{{ route('register.create', [], false) }}"
                            id="tab-register"
                            role="tab"
                            class="auth-switch-button auth-tab {{ $authMode === 'register' ? 'is-active active' : '' }}"
                            data-auth-trigger="register"
                            aria-controls="form-register"
                            aria-selected="{{ $authMode === 'register' ? 'true' : 'false' }}"
                        >Create account</a>
                    </div>

                    <div id="form-signin" class="auth-pane {{ $authMode === 'signin' ? 'is-active' : '' }}" data-auth-pane="signin" @if ($authMode !== 'signin') hidden @endif>
                        <p class="auth-kicker">Sign In</p>
                        <h2>Welcome back</h2>
                        <p class="auth-copy">Use your account details to continue your collection journey.</p>

                        <form class="auth-form" action="/login" method="POST">
                            @csrf
                            <label class="field-group">
                                <span>Email Address</span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="hello@yourbrand.com" autocomplete="email">
                                @error('email', 'login')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                            <div class="field-row">
                                <label class="field-group">
                                    <span>Password</span>
                                    <div class="password-field-wrap">
                                        <input
                                            type="password"
                                            name="password"
                                            id="login-password"
                                            placeholder="Enter your password"
                                            autocomplete="current-password"
                                            style="padding-right: 4rem; {{ $errors->login->has('password') ? 'border-color:#c0392b;' : '' }}"
                                        >
                                        <button type="button" class="password-toggle-btn" data-password-toggle data-password-target="login-password">Show</button>
                                    </div>
                                    @error('password', 'login')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                                <a href="{{ route('password.request', [], false) }}" class="field-link">Forgot?</a>
                            </div>

                            <div class="form-meta">
                                <label class="remember-row">
                                    <input type="checkbox" name="remember" @checked(old('remember'))>
                                    <span>Remember me</span>
                                </label>
                                <span class="meta-chip">Protected</span>
                            </div>

                            <button type="submit" class="submit-button">Sign in</button>
                        </form>

                        <p class="signup-copy">
                            Need a new account?
                            <a href="{{ route('register.create', [], false) }}" class="field-link auth-inline-link auth-inline-button" data-auth-link="register">
                                Create one
                            </a>
                        </p>
                    </div>

                    <div id="form-register" class="auth-pane {{ $authMode === 'register' ? 'is-active' : '' }}" data-auth-pane="register" @if ($authMode !== 'register') hidden @endif>
                        <p class="auth-kicker">Create Account</p>
                        <h2>Start your trading hub</h2>
                        <p class="auth-copy">Set up your profile and start organizing your photocard collection in one place.</p>

                        <form class="auth-form" action="{{ route('register.store', [], false) }}" method="POST">
                            @csrf
                            <div class="field-two-up">
                                <label class="field-group">
                                    <span>Full Name</span>
                                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Chrissie Lee" autocomplete="name">
                                    @error('name', 'register')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                                <label class="field-group">
                                    <span>Username</span>
                                    <input
                                        type="text"
                                        name="username"
                                        id="register-username"
                                        value="{{ old('username') }}"
                                        placeholder="cardkeeper"
                                        autocomplete="username"
                                        style="{{ $errors->register->has('username') ? 'border-color:#c0392b;' : '' }}"
                                    >
                                    <p id="username-status" class="auth-helper-message" hidden></p>
                                    @error('username', 'register')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                            </div>

                            <label class="field-group">
                                <span>Email Address</span>
                                <input
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="hello@yourbrand.com"
                                    autocomplete="email"
                                    style="{{ $errors->register->has('email') ? 'border-color:#c0392b;' : '' }}"
                                >
                                @error('email', 'register')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                            <div class="field-two-up">
                                <label class="field-group">
                                    <span>Password</span>
                                    <div class="password-field-wrap">
                                        <input
                                            type="password"
                                            name="password"
                                            id="register-password"
                                            placeholder="Create a password"
                                            autocomplete="new-password"
                                            style="padding-right: 4rem; {{ $errors->register->has('password') ? 'border-color:#c0392b;' : '' }}"
                                        >
                                        <button type="button" class="password-toggle-btn" data-password-toggle data-password-target="register-password">Show</button>
                                    </div>
                                    <div id="password-strength-bar" class="password-strength-bar" hidden>
                                        <div id="strength-fill" class="password-strength-fill"></div>
                                    </div>
                                    <p id="strength-label" class="auth-helper-message" hidden></p>
                                    @error('password', 'register')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                                <label class="field-group">
                                    <span>Confirm</span>
                                    <div class="password-field-wrap">
                                        <input
                                            type="password"
                                            name="password_confirmation"
                                            id="register-password-confirmation"
                                            placeholder="Confirm password"
                                            autocomplete="new-password"
                                            style="padding-right: 4rem; {{ $errors->register->has('password_confirmation') ? 'border-color:#c0392b;' : '' }}"
                                        >
                                        <button type="button" class="password-toggle-btn" data-password-toggle data-password-target="register-password-confirmation">Show</button>
                                    </div>
                                    @error('password_confirmation', 'register')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                            </div>

                            <div class="form-meta form-meta-stack">
                                <label class="remember-row">
                                    <input type="checkbox" name="terms" id="agree-terms" @checked(old('terms')) required>
                                    <span>
                                        I agree to the
                                        <a href="#guidelines-modal" class="auth-terms-link" data-modal-open="guidelines-modal">Community Guidelines</a>
                                        and
                                        <a href="#privacy-modal" class="auth-terms-link" data-modal-open="privacy-modal">Privacy Terms</a>.
                                    </span>
                                </label>
                                @error('terms', 'register')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                                <span class="meta-chip">Starter profile</span>
                            </div>

                            <button type="submit" class="submit-button">Create account</button>
                        </form>

                        <p class="signup-copy">
                            Already have an account?
                            <a href="{{ route('login', [], false) }}" class="field-link auth-inline-link auth-inline-button" data-auth-link="signin">
                                Sign in
                            </a>
                        </p>
                    </div>
                </div>
            </section>
        </main>

        @php
            $guidelines = [
                [
                    'title' => 'Be honest',
                    'text' => 'Accurately describe card condition, edition, and inclusions. No misleading photos or descriptions.',
                ],
                [
                    'title' => 'Communicate clearly',
                    'text' => 'Respond to messages promptly. If you cannot complete a trade, say so early and respectfully.',
                ],
                [
                    'title' => 'No scamming',
                    'text' => 'Do not accept payment or cards without fulfilling your end of the trade. Scammers will be permanently banned.',
                ],
                [
                    'title' => 'Ship safely',
                    'text' => 'Package photocards properly with a sleeve and toploader. Share tracking information when available.',
                ],
                [
                    'title' => 'Complete trades',
                    'text' => 'Mark trades as completed after both parties confirm receipt. This builds your reputation score.',
                ],
                [
                    'title' => 'Respect everyone',
                    'text' => 'This is a community for all fans. No harassment, discrimination, or hate speech of any kind.',
                ],
            ];

            $privacyItems = [
                [
                    'title' => 'What we collect',
                    'text' => 'We collect your name, username, email, and the photocard data you add to your collection. We do not collect payment information.',
                ],
                [
                    'title' => 'How we use it',
                    'text' => 'Your data powers CardFlow features: collection tracking, marketplace listings, messaging, and wishlist matching.',
                ],
                [
                    'title' => 'What is public',
                    'text' => 'Your username, profile, public collection, and active listings are visible to other logged-in users. Your email and trade messages are private.',
                ],
                [
                    'title' => 'Your photos',
                    'text' => 'Photocard images you upload are stored for CardFlow features. We do not sell or share them with advertisers.',
                ],
                [
                    'title' => 'Data deletion',
                    'text' => 'You can request deletion of your account and associated data from your profile settings.',
                ],
                [
                    'title' => 'No ads, no selling',
                    'text' => 'CardFlow does not show ads and does not sell your data. Your information stays within the platform.',
                ],
            ];
        @endphp

        <div id="guidelines-modal" class="modal-overlay hidden" data-modal-backdrop>
            <div class="modal-box auth-policy-modal">
                <div class="auth-modal-header">
                    <div>
                        <p class="auth-modal-kicker">CardFlow</p>
                        <h2>Community Guidelines</h2>
                    </div>
                    <button type="button" class="auth-modal-close" data-modal-close="guidelines-modal" aria-label="Close guidelines">&times;</button>
                </div>

                <div class="auth-policy-list">
                    @foreach($guidelines as $item)
                        <div class="auth-policy-item">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="auth-modal-action"
                    data-modal-accept="guidelines-modal"
                >I understand - Close</button>
            </div>
        </div>

        <div id="privacy-modal" class="modal-overlay hidden" data-modal-backdrop>
            <div class="modal-box auth-policy-modal">
                <div class="auth-modal-header">
                    <div>
                        <p class="auth-modal-kicker">CardFlow</p>
                        <h2>Privacy Terms</h2>
                    </div>
                    <button type="button" class="auth-modal-close" data-modal-close="privacy-modal" aria-label="Close privacy terms">&times;</button>
                </div>

                <div class="auth-policy-list">
                    @foreach($privacyItems as $item)
                        <div class="auth-policy-item">
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['text'] }}</p>
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="auth-modal-action"
                    data-modal-accept="privacy-modal"
                >I agree - Close</button>
            </div>
        </div>
@endsection
