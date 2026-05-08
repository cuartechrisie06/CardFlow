@extends('layouts.app')

@section('title', 'CardFlow | Edit Profile')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header">
                    <div>
                        <p class="dashboard-kicker">Profile</p>
                        <h1>Edit profile</h1>
                    </div>
                </header>

                <section class="dashboard-card collection-card-shell">
                    @if (session('status'))
                        <div class="auth-status">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('profile.update') }}" class="card-create-form" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-form-grid">
                            <label class="field-group field-group-wide">
                                <span>Avatar</span>
                                <div class="profile-edit-avatar-row">
                                    @if ($user->avatar_url)
                                        <div
                                            class="profile-avatar profile-avatar-photo profile-avatar-preview"
                                            style="background-image: url('{{ $user->avatar_url }}');"
                                        ></div>
                                    @else
                                        <div class="profile-avatar profile-avatar-fallback profile-avatar-preview">
                                            {{ $user->initials }}
                                        </div>
                                    @endif

                                    <input type="file" name="avatar" accept="image/*">
                                </div>
                                @error('avatar') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Full Name</span>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}">
                                @error('name') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Username</span>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}">
                                @error('username') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group field-group-wide">
                                <span>Email Address</span>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}">
                                @error('email') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Location</span>
                                <input type="text" name="location" value="{{ old('location', $user->location) }}">
                                @error('location') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Website</span>
                                <input type="url" name="website" value="{{ old('website', $user->website) }}" placeholder="https://your-site.com">
                                @error('website') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group field-group-wide">
                                <span>Bio</span>
                                <textarea name="bio" rows="5" placeholder="Tell other collectors a bit about yourself...">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio') <small class="field-error">{{ $message }}</small> @enderror
                            </label>
                        </div>

                        <div class="create-form-actions">
                            <button type="submit" class="dashboard-add-card">Save changes</button>
                        </div>
                    </form>
                </section>
@endsection

