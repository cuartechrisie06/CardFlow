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
                    <form method="POST" action="{{ route('profile.update') }}" class="card-create-form" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-form-grid">
                            <div class="field-group field-group-wide">
                                <span>Avatar</span>
                                <div class="profile-edit-avatar-row">
                                    <div style="position:relative; width:72px; height:72px;">
                                        @if($user->avatar)
                                            <img
                                                id="avatar-preview-img"
                                                src="{{ asset('storage/avatars/' . $user->avatar) }}"
                                                alt="Avatar"
                                                style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:2px solid #e8d5c0;"
                                            >
                                        @else
                                            <img
                                                id="avatar-preview-img"
                                                src=""
                                                alt="Avatar"
                                                style="display:none; width:72px; height:72px; border-radius:50%; object-fit:cover;"
                                            >
                                        @endif

                                        <div
                                            id="avatar-initials-preview"
                                            style="width:72px; height:72px; border-radius:50%; background:#e8c9a0; display:{{ $user->avatar ? 'none' : 'flex' }}; align-items:center; justify-content:center; font-family:'Playfair Display',serif; font-size:1.4rem; font-weight:700; color:#8B4513;"
                                        >
                                            @initials($user->name)
                                        </div>
                                    </div>

                                    <div class="profile-upload-control">
                                        <input
                                            type="file"
                                            name="avatar"
                                            id="avatar-input"
                                            accept="image/jpeg,image/png,image/webp"
                                            class="hidden"
                                            data-file-input
                                            data-preview-target="#avatar-preview-img"
                                            data-preview-initials="#avatar-initials-preview"
                                            onchange="handleAvatarSelect(this)"
                                        >
                                        <label for="avatar-input" class="profile-upload-button profile-upload-btn btn-brown cursor-pointer">Choose Image</label>
                                        <span id="avatar-filename-label" style="font-family:'DM Sans',sans-serif;font-size:0.8rem;color:#b09070;">NO IMAGE SELECTED</span>
                                    </div>
                                </div>
                                @if($user->avatar)
                                    <div style="margin-top: 8px;">
                                        <button type="button"
                                                id="remove-avatar-btn"
                                                onclick="confirmRemoveAvatar()"
                                                style="background: none; border: 1px solid #e8c9a0; color: #8B4513; font-family: 'DM Sans',sans-serif; font-size: 0.75rem; padding: 6px 14px; border-radius: 20px; cursor: pointer;">
                                            ✕ Remove photo
                                        </button>
                                        <input type="hidden" name="remove_avatar" id="remove-avatar-input" value="0">
                                    </div>
                                @endif
                                @error('avatar') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

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

@push('scripts')
<script>
function handleAvatarSelect(input) {
    const label = document.getElementById('avatar-filename-label');

    if (input.files && input.files[0]) {
        const file = input.files[0];

        if (label) {
            label.textContent = file.name;
            label.style.color = '#3d2b1f';
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('avatar-preview-img');
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }

            const initials = document.getElementById('avatar-initials-preview');
            if (initials) {
                initials.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);

        const removeBtn = document.getElementById('remove-avatar-btn');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }

        const removeInput = document.getElementById('remove-avatar-input');
        if (removeInput) {
            removeInput.value = '0';
        }
    }
}

function confirmRemoveAvatar() {
    if (confirm('Remove your profile photo? Your initials will be shown instead.')) {
        const removeInput = document.getElementById('remove-avatar-input');
        if (removeInput) {
            removeInput.value = '1';
        }

        const preview = document.getElementById('avatar-preview-img');
        const initials = document.getElementById('avatar-initials-preview');

        if (preview) preview.style.display = 'none';
        if (initials) initials.style.display = 'flex';

        const removeBtn = document.getElementById('remove-avatar-btn');
        if (removeBtn) {
            removeBtn.style.display = 'none';
        }

        const label = document.getElementById('avatar-filename-label');
        if (label) {
            label.textContent = 'Photo will be removed on save';
            label.style.color = '#c0392b';
        }
    }
}
</script>
@endpush
