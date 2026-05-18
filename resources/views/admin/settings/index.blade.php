@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<header class="dashboard-header">
    <div>
        <p class="dashboard-kicker">Admin Panel</p>
        <h1>Settings</h1>
        <p class="dashboard-intro">Manage platform configuration and admin preferences.</p>
    </div>
</header>

<form method="POST" action="{{ route('admin.settings.update') }}" style="display:grid;gap:1rem;">
    @csrf

    <section class="dashboard-card">
        <div class="card-topline">
            <div>
                <p class="mini-label">Platform</p>
                <h2>General settings</h2>
            </div>
            @if(($settings['maintenance_mode'] ?? '0') === '1')
                <span class="mini-chip">Site currently in maintenance mode</span>
            @endif
        </div>

        <div class="card-form-grid">
            <label class="field-group">
                <span>Site name</span>
                <input type="text" name="site_name" value="{{ old('site_name', $settings['site_name']) }}">
            </label>
            <label class="field-group" style="display:flex;align-items:center;gap:10px;margin-top:1.8rem;">
                <input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode', $settings['maintenance_mode']) === '1')>
                <span style="margin:0;">Maintenance mode</span>
            </label>
        </div>
    </section>

    <section class="dashboard-card">
        <div class="card-topline">
            <div>
                <p class="mini-label">Listings</p>
                <h2>Marketplace rules</h2>
            </div>
        </div>

        <div class="card-form-grid">
            <label class="field-group" style="display:flex;align-items:center;gap:10px;margin-top:1.8rem;">
                <input type="checkbox" name="require_proof" value="1" @checked(old('require_proof', $settings['require_proof']) === '1')>
                <span style="margin:0;">Require proof of ownership</span>
            </label>
            <label class="field-group">
                <span>Max price limit (PHP)</span>
                <input type="number" name="max_price_limit" min="0" step="1" value="{{ old('max_price_limit', $settings['max_price_limit']) }}">
            </label>
            <label class="field-group">
                <span>Auto-expire listings after N days</span>
                <input type="number" name="auto_expire_days" min="0" step="1" value="{{ old('auto_expire_days', $settings['auto_expire_days']) }}">
            </label>
        </div>
    </section>

    <section class="dashboard-card">
        <div class="card-topline">
            <div>
                <p class="mini-label">Moderation</p>
                <h2>Review preferences</h2>
            </div>
        </div>

        <div class="card-form-grid">
            <label class="field-group">
                <span>Auto-flag listings above price</span>
                <input type="number" name="auto_flag_price" min="0" step="1" value="{{ old('auto_flag_price', $settings['auto_flag_price']) }}">
            </label>
            <label class="field-group" style="display:flex;align-items:center;gap:10px;margin-top:1.8rem;">
                <input type="checkbox" name="notify_admin_reports" value="1" @checked(old('notify_admin_reports', $settings['notify_admin_reports']) === '1')>
                <span style="margin:0;">Notify admin on new report</span>
            </label>
            <label class="field-group">
                <span>Admin notification email</span>
                <input type="email" name="admin_notification_email" value="{{ old('admin_notification_email', $settings['admin_notification_email']) }}">
            </label>
        </div>
    </section>

    <section class="dashboard-card">
        <div class="card-topline">
            <div>
                <p class="mini-label">Admin Account</p>
                <h2>Profile and password</h2>
            </div>
        </div>

        <div class="card-form-grid">
            <label class="field-group">
                <span>Display name</span>
                <input type="text" name="display_name" value="{{ old('display_name', auth()->user()->name) }}">
            </label>
            <label class="field-group">
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}">
            </label>
            <label class="field-group">
                <span>New password</span>
                <input type="password" name="password" autocomplete="new-password">
            </label>
            <label class="field-group">
                <span>Confirm password</span>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </label>
        </div>
    </section>

    <section class="dashboard-card">
        <div class="card-topline">
            <div>
                <p class="mini-label">Announcements</p>
                <h2>Sitewide banner</h2>
            </div>
        </div>
        <div class="card-form-grid">
            <label class="field-group" style="grid-column:1 / -1;">
                <span>Banner message</span>
                <textarea name="announcement_message" rows="4" style="width:100%;border:1px solid rgba(137,104,78,0.12);border-radius:1rem;background:rgba(255,252,248,0.84);padding:1rem;">{{ old('announcement_message', $settings['announcement_message']) }}</textarea>
            </label>
            <label class="field-group" style="display:flex;align-items:center;gap:10px;">
                <input type="checkbox" name="announcement_active" value="1" @checked(old('announcement_active', $settings['announcement_active']) === '1')>
                <span style="margin:0;">Active</span>
            </label>
            <label class="field-group" style="display:flex;align-items:center;gap:10px;">
                <input type="checkbox" name="announcement_dismissible" value="1" @checked(old('announcement_dismissible', $settings['announcement_dismissible']) === '1')>
                <span style="margin:0;">Dismissible</span>
            </label>
        </div>
    </section>

    <div style="display:flex;justify-content:flex-end;">
        <button type="submit" class="dashboard-add-card" style="border:none;cursor:pointer;">Save settings</button>
    </div>
</form>
@endsection
