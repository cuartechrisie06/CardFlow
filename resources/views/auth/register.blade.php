@extends('layouts.app')

@section('title', 'CardFlow | Create account')
@section('layout_mode', 'shellless')
@section('body_class', 'cardflow-body')

@section('content')
<main class="cardflow-shell">
    <section class="auth-panel auth-panel--full" aria-label="Create account">
        <div class="auth-card auth-card--register">
            <p class="auth-kicker">Create Account</p>
            <h2>Start your trading hub</h2>
            <p class="auth-copy">Join CardFlow and start organizing your photocard collection in one place.</p>

            @if ($errors->register->any())
                <div class="auth-status auth-status--error">
                    Please fix the highlighted fields and try again.
                </div>
            @endif

            <form class="auth-form" method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="field-two-up">
                    <label class="field-group" for="name">
                        <span>Full Name</span>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Chrissie Lee" autocomplete="name" required autofocus>
                        @error('name', 'register')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="field-group" for="username">
                        <span>Username</span>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" placeholder="cardkeeper" autocomplete="username" required>
                        @error('username', 'register')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </label>
                </div>

                <label class="field-group" for="email">
                    <span>Email Address</span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="hello@yourbrand.com" autocomplete="email" required>
                    @error('email', 'register')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </label>

                <div class="field-two-up">
                    <label class="field-group" for="password">
                        <span>Password</span>
                        <input id="password" type="password" name="password" placeholder="Create a password" autocomplete="new-password" required>
                        @error('password', 'register')
                            <small class="field-error">{{ $message }}</small>
                        @enderror
                    </label>

                    <label class="field-group" for="password_confirmation">
                        <span>Confirm</span>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Confirm password" autocomplete="new-password" required>
                    </label>
                </div>

                <div class="form-meta form-meta-stack">
                    <label class="remember-row" for="terms">
                        <input id="terms" type="checkbox" name="terms" @checked(old('terms')) required>
                        <span>I agree to the community guidelines and privacy terms.</span>
                    </label>
                    @error('terms', 'register')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                    <span class="meta-chip">Starter profile</span>
                </div>

                <button type="submit" class="submit-button">Create account</button>
            </form>

            <p class="signup-copy">Already have an account? <a href="{{ route('login') }}" class="field-link auth-inline-link">Sign in</a></p>
        </div>
    </section>
</main>
@endsection
