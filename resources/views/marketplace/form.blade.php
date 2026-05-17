<header class="dashboard-header marketplace-header">
    <div>
        <span class="dashboard-eyebrow">Marketplace</span>
        <h1>{{ $listing ? 'Edit listing' : 'Create listing' }}</h1>
        <p>
            {{ $listing
                ? 'Update the listing details, save it as a draft, or publish it.'
                : 'Choose a collection item and prepare it as a draft or active marketplace listing.' }}
        </p>
    </div>

    <div class="dashboard-actions">
        <a href="{{ route('marketplace.index', ['filter' => $listing ? 'my_listings' : 'all']) }}" class="dashboard-search-submit">
            Back to Marketplace
        </a>
    </div>
</header>

<section class="dashboard-card marketplace-create-card marketplace-create-card--full">
    @if ($errors->any())
        <div class="form-error-box">
            <strong>Please check the form:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $listing && $userCards->isEmpty())
        <div class="empty-state">
            You need to add cards to your collection before posting a marketplace listing.
        </div>

        <a href="{{ route('collection.create') }}" class="dashboard-add-card">
            Add card first
        </a>
    @else
        @php
            $cardSource = $selectedUserCard?->card;
            $currentType = old('type');
            $selectedPhotoUrl = $storagePhotoUrl($selectedUserCard?->photo_path);
            $selectedPrice = $selectedUserCard?->listing_price
                ?? $selectedUserCard?->estimated_value
                ?? $cardSource?->market_value;

            if (! $currentType && $selectedUserCard) {
                $currentType = $selectedUserCard->is_for_trade && ! $selectedUserCard->is_for_sale ? 'trade' : 'sale';
            }
        @endphp

        <form method="POST" action="{{ $formAction }}" class="marketplace-create-form" enctype="multipart/form-data">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            @if (! $listing)
                <label class="form-field marketplace-field--full">
                    <span>Collection item</span>
                    <select id="collection_item" name="user_card_id" required>
                        <option value="">Choose a card from your collection</option>
                        @foreach ($userCards as $userCard)
                            @php
                                $optionCard = $userCard->card;
                                $optionPrice = $userCard->listing_price
                                    ?? $userCard->estimated_value
                                    ?? $optionCard?->market_value;
                                $optionPhoto = $storagePhotoUrl($userCard->photo_path);
                            @endphp
                            <option
                                value="{{ $userCard->id }}"
                                data-title="{{ $optionCard?->title }}"
                                data-artist="{{ $optionCard?->artist }}"
                                data-rarity="{{ $optionCard?->rarity }}"
                                data-price="{{ $optionPrice }}"
                                data-photo="{{ $optionPhoto }}"
                                data-photo-url="{{ $optionPhoto }}"
                                data-type="{{ $userCard->is_for_sale ? 'sale' : ($userCard->is_for_trade ? 'trade' : 'sale') }}"
                                @selected(old('user_card_id', $selectedUserCard?->id) == $userCard->id)
                            >
                                {{ $userCard->card->title ?? 'Untitled Card' }} - {{ $userCard->card->artist ?? 'Unknown Group' }} - {{ $userCard->condition ?? 'No condition' }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_card_id') <small class="field-error">{{ $message }}</small> @enderror
                </label>
            @else
                <div class="listing-edit-preview">
                    <strong>{{ $selectedUserCard->card->title ?? 'Untitled Card' }}</strong>
                    <span>{{ $selectedUserCard->card->artist ?? 'Unknown Group' }}</span>
                    <span>{{ $selectedUserCard->condition ?? 'No condition' }}</span>
                </div>
            @endif

            <div class="marketplace-create-pair-grid">
                <label class="form-field">
                    <span>Title</span>
                    <input id="title" type="text" name="title" value="{{ old('title', $cardSource?->title) }}" required>
                    @error('title') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <label class="form-field">
                    <span>Artist</span>
                    <input id="artist" type="text" name="artist" value="{{ old('artist', $cardSource?->artist) }}" required>
                    @error('artist') <small class="field-error">{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="marketplace-create-pair-grid">
                <label class="form-field">
                    <span>Rarity</span>
                    <select id="rarity" name="rarity" required>
                        @foreach (['Mint', 'Rare', 'Hot', 'Official', 'Wishlist'] as $rarity)
                            <option value="{{ $rarity }}" @selected(old('rarity', $cardSource?->rarity) === $rarity)>{{ $rarity }}</option>
                        @endforeach
                    </select>
                    @error('rarity') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <label class="form-field">
                    <span>Type</span>
                    <select id="type" name="type" required>
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($currentType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('type') <small class="field-error">{{ $message }}</small> @enderror
                </label>
            </div>

            <div class="marketplace-create-pair-grid">
                <label class="form-field">
                    <span>Price</span>
                    <input
                        type="number"
                        id="listing_price"
                        name="listing_price"
                        value="{{ old('listing_price', $selectedPrice) }}"
                        min="0"
                        step="0.01"
                        placeholder="Suggested from estimated value"
                    >
                    <small class="field-help">Suggested from the card's estimated value. Adjust before publishing.</small>
                    @error('listing_price') <small class="field-error">{{ $message }}</small> @enderror
                </label>

                <label class="form-field">
                    <span>Status</span>
                    <select name="status" required>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $listing?->status ?? 'active') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status') <small class="field-error">{{ $message }}</small> @enderror
                </label>
            </div>

            <label class="form-field">
                <span>Description</span>
                <textarea name="description" rows="4" placeholder="Condition notes, packaging details, or trading preferences...">{{ old('description', $selectedUserCard?->notes) }}</textarea>
                @error('description') <small class="field-error">{{ $message }}</small> @enderror
            </label>

            <div class="marketplace-create-split">
                <div class="form-field marketplace-photo-field">
                    <span>Listing photo</span>
                    <div class="listing-photo-preview-shell">
                        <img
                            id="listing-photo-preview"
                            src="{{ $selectedPhotoUrl ?: asset('images/placeholder-card.png') }}"
                            alt="Listing photo preview"
                            class="card-photo-preview"
                            onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                        >
                    </div>
                    <label class="profile-upload-control marketplace-upload-control">
                        <input
                            id="photo_input"
                            type="file"
                            name="photo"
                            accept="image/*"
                            data-file-input
                            data-preview-target="#listing-photo-preview"
                        >
                        <span class="profile-upload-button">Choose image</span>
                        <span class="profile-upload-filename" data-file-name>
                            {{ $selectedPhotoUrl ? 'Using collection photo' : 'No image selected' }}
                        </span>
                    </label>
                    <small class="field-help">Uses the collection photo by default. Choose a new image only if this listing needs a different photo.</small>
                    @error('photo') <small class="field-error">{{ $message }}</small> @enderror
                </div>

                <section class="proof-section" style="margin-top:28px;padding:24px;background:#fdf6f0;border:2px dashed #d4b896;border-radius:16px;">
                    <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:20px;">
                        <div style="width:44px;height:44px;background:#f5e6d8;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
                            &#128737;
                        </div>
                        <div>
                            <p style="font-family:'DM Sans',sans-serif;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#8B4513;margin:0 0 4px;">
                                ANTI-SCAM VERIFICATION
                            </p>
                            <h3 style="font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:#3d2b1f;margin:0 0 4px;">
                                Proof of Possession
                            </h3>
                            <p style="font-family:'DM Sans',sans-serif;font-size:0.82rem;color:#8B6F5E;margin:0;line-height:1.5;">
                                Upload a photo of yourself holding the card with a handwritten note showing your username and today's date.
                                This protects buyers and trade partners from scams.
                            </p>
                        </div>
                    </div>

                    <div style="background:#ffffff;border-radius:12px;padding:16px;margin-bottom:20px;border:1px solid #e8d5c0;">
                        <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;font-weight:600;color:#8B4513;margin:0 0 10px;text-transform:uppercase;letter-spacing:0.05em;">
                            How to take the proof photo
                        </p>
                        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;">
                            @foreach([
                                ['ok', 'Hold the physical card clearly in frame'],
                                ['ok', 'Show a handwritten paper with @'.auth()->user()->username],
                                ['ok', 'Include today\'s date: '.now()->format('Y-m-d')],
                                ['ok', 'Use good lighting with no blur'],
                                ['no', 'No screenshots or digital images'],
                                ['no', 'No cropped or edited photos'],
                            ] as $tip)
                                <div style="display:flex;gap:8px;align-items:flex-start;">
                                    <span style="color:{{ $tip[0] === 'ok' ? '#2d6a4f' : '#c0392b' }};font-weight:700;flex-shrink:0;">
                                        {{ $tip[0] === 'ok' ? '✓' : 'x' }}
                                    </span>
                                    <span style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#8B6F5E;line-height:1.4;">
                                        {{ $tip[1] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($listing?->proof_photo)
                        @php
                            $proofStatusConfig = match($listing->proof_status) {
                                'verified' => ['label' => 'Verified', 'color' => '#2d6a4f', 'bg' => '#d4edda'],
                                'failed' => ['label' => 'Failed - reupload', 'color' => '#c0392b', 'bg' => '#f8d7da'],
                                default => ['label' => 'Pending review', 'color' => '#8B4513', 'bg' => '#f5e6d8'],
                            };
                        @endphp
                        <div style="background:#ffffff;border-radius:12px;padding:14px 16px;margin-bottom:16px;border:1px solid #e8d5c0;display:flex;gap:14px;align-items:center;">
                            <img
                                src="{{ $listing->proof_photo_url ?: asset('images/placeholder-card.png') }}"
                                alt="Current proof photo"
                                style="width:56px;height:56px;object-fit:cover;border-radius:8px;flex-shrink:0;"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                            >
                            <div style="flex:1;">
                                <p style="font-family:'DM Sans',sans-serif;font-size:0.75rem;text-transform:uppercase;letter-spacing:0.06em;color:#8B4513;margin:0 0 2px;">
                                    CURRENT PROOF
                                </p>
                                <span style="font-family:'DM Sans',sans-serif;font-size:0.75rem;font-weight:600;color:{{ $proofStatusConfig['color'] }};background:{{ $proofStatusConfig['bg'] }};padding:3px 10px;border-radius:20px;">
                                    {{ $proofStatusConfig['label'] }}
                                </span>
                            </div>
                            <a href="{{ $listing->proof_photo_url ?: '#' }}"
                               target="_blank"
                               style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#8B4513;text-decoration:none;border:1px solid #d4b896;padding:6px 12px;border-radius:20px;flex-shrink:0;">
                                View
                            </a>
                        </div>

                        @if(! $listing->proof_verified && $listing->user_id === auth()->id())
                            <form method="POST" action="{{ route('listings.verify-proof', $listing) }}" style="margin-bottom:16px;">
                                @csrf
                                <button type="submit"
                                        style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#2d6a4f;border:1px solid #a8d5b5;background:#d4edda;padding:7px 16px;border-radius:20px;cursor:pointer;">
                                    Mark proof as verified
                                </button>
                            </form>
                        @endif
                    @endif

                    <div id="proof-upload-area"
                         style="border:2px dashed #d4b896;border-radius:12px;padding:24px;text-align:center;background:#ffffff;cursor:pointer;transition:border-color 200ms;"
                         onclick="document.getElementById('proof_photo').click()"
                         ondragover="event.preventDefault();this.style.borderColor='#8B4513'"
                         ondragleave="this.style.borderColor='#d4b896'"
                         ondrop="handleProofDrop(event)">
                        <input
                            type="file"
                            name="proof_photo"
                            id="proof_photo"
                            accept="image/jpeg,image/png,image/webp"
                            style="display:none;"
                            onchange="previewProof(this)"
                        >

                        <div id="proof-upload-placeholder">
                            <div style="font-size:2rem;margin-bottom:8px;">&#128247;</div>
                            <p style="font-family:'Playfair Display',serif;font-size:0.95rem;color:#3d2b1f;margin:0 0 4px;font-weight:600;">
                                Click or drag to upload proof photo
                            </p>
                            <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#b09070;margin:0;">
                                JPEG, PNG or WebP. Max 5MB.
                            </p>
                        </div>

                        <div id="proof-preview-area" style="display:none;">
                            <img id="proof-preview-img"
                                 src=""
                                 alt="Selected proof preview"
                                 style="max-height:160px;border-radius:10px;object-fit:cover;margin-bottom:8px;">
                            <p id="proof-preview-name"
                               style="font-family:'DM Sans',sans-serif;font-size:0.8rem;color:#8B4513;margin:0;"></p>
                            <button type="button"
                                    onclick="event.stopPropagation();removeProof()"
                                    style="margin-top:8px;background:none;border:1px solid #d4b896;color:#8B4513;font-family:'DM Sans',sans-serif;font-size:0.75rem;padding:5px 14px;border-radius:20px;cursor:pointer;">
                                Remove
                            </button>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:10px;margin-top:14px;">
                        <label class="toggle-switch">
                            <input type="checkbox"
                                   name="proof_required"
                                   id="proof-optional-toggle"
                                   value="1"
                                   {{ $listing?->proof_photo ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                        <span style="font-family:'DM Sans',sans-serif;font-size:0.82rem;color:#3d2b1f;">
                            Show Verified badge on my listing
                        </span>
                    </div>

                    <p style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#b09070;margin:10px 0 0;line-height:1.5;">
                        Listings with verified proof get a trust badge and stronger buyer confidence. Verification is optional but strongly recommended.
                    </p>
                    @error('proof_photo') <small class="field-error">{{ $message }}</small> @enderror
                </section>
            </div>

            <div class="create-form-actions marketplace-form-actions">
                <button type="submit" class="dashboard-add-card">
                    {{ $submitLabel }}
                </button>
            </div>
        </form>

        @if ($listing)
            <div class="marketplace-secondary-actions">
                <form method="POST" action="{{ route('marketplace.sold', $listing) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="dashboard-add-card dashboard-add-card-secondary">
                        Mark as Sold
                    </button>
                </form>

                <form method="POST" action="{{ route('marketplace.archive', $listing) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="dashboard-search-submit">
                        Archive
                    </button>
                </form>

                <form method="POST" action="{{ route('marketplace.destroy', $listing) }}" class="delete-listing-form">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="delete-listing-button"
                        onclick="return confirm('Remove this listing from the marketplace? Your card will stay in your collection.')"
                    >
                        Delete posting
                    </button>
                </form>
            </div>
        @endif
    @endif
</section>

@push('scripts')
<script>
function previewProof(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    if (!allowedTypes.includes(file.type)) {
        alert('Please upload a JPEG, PNG, or WebP image.');
        input.value = '';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert('File too large. Maximum 5MB.');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const placeholder = document.getElementById('proof-upload-placeholder');
        const preview = document.getElementById('proof-preview-area');
        const image = document.getElementById('proof-preview-img');
        const filename = document.getElementById('proof-preview-name');
        const uploadArea = document.getElementById('proof-upload-area');

        if (placeholder) placeholder.style.display = 'none';
        if (preview) preview.style.display = 'block';
        if (image) image.src = e.target.result;
        if (filename) filename.textContent = file.name;
        if (uploadArea) uploadArea.style.borderColor = '#2d6a4f';
    };
    reader.readAsDataURL(file);
}

function removeProof() {
    const input = document.getElementById('proof_photo');
    const placeholder = document.getElementById('proof-upload-placeholder');
    const preview = document.getElementById('proof-preview-area');
    const image = document.getElementById('proof-preview-img');
    const filename = document.getElementById('proof-preview-name');
    const uploadArea = document.getElementById('proof-upload-area');

    if (input) input.value = '';
    if (placeholder) placeholder.style.display = 'block';
    if (preview) preview.style.display = 'none';
    if (image) image.src = '';
    if (filename) filename.textContent = '';
    if (uploadArea) uploadArea.style.borderColor = '#d4b896';
}

function handleProofDrop(event) {
    event.preventDefault();
    event.currentTarget.style.borderColor = '#d4b896';

    const file = event.dataTransfer.files[0];
    if (!file) return;

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Please upload a JPEG, PNG, or WebP image.');
        return;
    }

    const input = document.getElementById('proof_photo');
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    input.files = dataTransfer.files;
    previewProof(input);
}

document.addEventListener('DOMContentLoaded', function () {
    const collectionSelect = document.querySelector('#collection_item');
    const manualPhotoInput = document.querySelector('#photo_input');
    const previewPlaceholder = '{{ asset('images/placeholder-card.png') }}';

    const fields = {
        title: document.querySelector('#title'),
        artist: document.querySelector('#artist'),
        rarity: document.querySelector('#rarity'),
        price: document.querySelector('#listing_price'),
        type: document.querySelector('#type'),
        photo: document.querySelector('#listing-photo-preview'),
        photoName: document.querySelector('.marketplace-photo-field [data-file-name]'),
    };

    const applySelectedCard = function () {
        const selected = collectionSelect.options[collectionSelect.selectedIndex];

        if (!selected || !selected.value) {
            return;
        }

        if (fields.title) fields.title.value = selected.dataset.title || '';
        if (fields.artist) fields.artist.value = selected.dataset.artist || '';
        if (fields.rarity) fields.rarity.value = selected.dataset.rarity || '';
        if (fields.price && !fields.price.value) fields.price.value = selected.dataset.price || '';
        if (fields.type && selected.dataset.type) fields.type.value = selected.dataset.type;

        if (fields.photo) {
            fields.photo.src = selected.dataset.photo || selected.dataset.photoUrl || previewPlaceholder;
        }

        if (fields.photoName) {
            fields.photoName.textContent = (selected.dataset.photo || selected.dataset.photoUrl) ? 'Using collection photo' : 'No image selected';
        }
    };

    if (collectionSelect) {
        collectionSelect.addEventListener('change', applySelectedCard);
        applySelectedCard();
    }

    if (manualPhotoInput && fields.photo) {
        manualPhotoInput.addEventListener('change', function () {
            const file = this.files && this.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                fields.photo.src = event.target && event.target.result ? event.target.result : previewPlaceholder;
            };

            reader.readAsDataURL(file);
        });
    }
});
</script>
@endpush
