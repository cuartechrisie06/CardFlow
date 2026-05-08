@extends('layouts.app')

@section('title', 'CardFlow | Account Settings')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header">
                    <div>
                        <p class="dashboard-kicker">Settings</p>
                        <h1>Account settings</h1>
                        <p class="dashboard-intro">Review your account details and current CardFlow sign-in information.</p>
                    </div>
                </header>

                <section class="stats-grid">
                    <article class="stat-card">
                        <span class="stat-label">Account email</span>
                        <div class="stat-value">{{ $user->email }}</div>
                        <div class="stat-note">used to sign in</div>
                    </article>
                    <article class="stat-card">
                        <span class="stat-label">Username</span>
                        <div class="stat-value">{{ '@'.$username }}</div>
                        <div class="stat-note">visible across CardFlow</div>
                    </article>
                </section>
@endsection

