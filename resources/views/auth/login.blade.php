<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Account Access</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="cardflow-body">
        @php
            $authMode = session('auth_mode');

            if (! $authMode) {
                $authMode = $errors->register->isNotEmpty() ? 'signup' : 'signin';
            }
        @endphp
        <main class="cardflow-shell">
            <section class="hero-panel">
                <div class="brand-lockup">
                    <div class="brand-mark" aria-hidden="true"></div>
                    <span class="brand-name">CARDFLOW</span>
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
                <div class="auth-card" data-auth-card>
                    <div class="auth-switch" role="tablist" aria-label="Authentication mode">
                        <button type="button" class="auth-switch-button {{ $authMode === 'signin' ? 'is-active' : '' }}" data-auth-trigger="signin" aria-selected="{{ $authMode === 'signin' ? 'true' : 'false' }}">Sign in</button>
                        <button type="button" class="auth-switch-button {{ $authMode === 'signup' ? 'is-active' : '' }}" data-auth-trigger="signup" aria-selected="{{ $authMode === 'signup' ? 'true' : 'false' }}">Create account</button>
                    </div>

                    @if (session('status'))
                        <div class="auth-status">{{ session('status') }}</div>
                    @endif

                    <div class="auth-pane {{ $authMode === 'signin' ? 'is-active' : '' }}" data-auth-pane="signin" @if ($authMode !== 'signin') hidden @endif>
                        <p class="auth-kicker">Sign In</p>
                        <h2>Welcome back</h2>
                        <p class="auth-copy">Use your account details to continue your collection journey.</p>

                        <form class="auth-form" action="{{ url('/login') }}" method="POST">
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
                                    <input type="password" name="password" placeholder="Enter your password" autocomplete="current-password">
                                    @error('password', 'login')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                                <a href="#" class="field-link">Forgot?</a>
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

                        <p class="signup-copy">Need a new account? <a href="#" data-auth-link="signup">Create one</a></p>
                    </div>

                    <div class="auth-pane {{ $authMode === 'signup' ? 'is-active' : '' }}" data-auth-pane="signup" @if ($authMode !== 'signup') hidden @endif>
                        <p class="auth-kicker">Create Account</p>
                        <h2>Start your trading hub</h2>
                        <p class="auth-copy">Set up your profile and start organizing your photocard collection in one place.</p>

                        <form class="auth-form" action="{{ route('register') }}" method="POST">
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
                                    <input type="text" name="username" value="{{ old('username') }}" placeholder="cardkeeper" autocomplete="username">
                                    @error('username', 'register')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                            </div>

                            <label class="field-group">
                                <span>Email Address</span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="hello@yourbrand.com" autocomplete="email">
                                @error('email', 'register')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                            </label>

                            <div class="field-two-up">
                                <label class="field-group">
                                    <span>Password</span>
                                    <input type="password" name="password" placeholder="Create a password" autocomplete="new-password">
                                    @error('password', 'register')
                                        <small class="field-error">{{ $message }}</small>
                                    @enderror
                                </label>
                                <label class="field-group">
                                    <span>Confirm</span>
                                    <input type="password" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password">
                                </label>
                            </div>

                            <div class="form-meta form-meta-stack">
                                <label class="remember-row">
                                    <input type="checkbox" name="terms" @checked(old('terms'))>
                                    <span>I agree to the community guidelines and privacy terms.</span>
                                </label>
                                @error('terms', 'register')
                                    <small class="field-error">{{ $message }}</small>
                                @enderror
                                <span class="meta-chip">Starter profile</span>
                            </div>

                            <button type="submit" class="submit-button">Create account</button>
                        </form>

                        <p class="signup-copy">Already have an account? <a href="#" data-auth-link="signin">Sign in</a></p>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
