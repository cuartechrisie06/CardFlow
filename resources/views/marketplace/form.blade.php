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

                <section class="proof-upload-panel">
                    <div class="proof-upload-copy">
                        <p class="mini-label">Proof of Possession</p>
                        <h3>Proof of Possession</h3>
                        <p>
                            Hold your photocard next to a paper with your username
                            <strong>{{ '@'.auth()->user()->username }}</strong> and today's date
                            <strong>{{ now()->format('Y-m-d') }}</strong>. Take a clear photo in good lighting.
                        </p>
                        <p class="proof-upload-note">
                            Listings with verified proof get a "Verified Seller" badge.
                        </p>
                    </div>

                    @if ($listing?->proof_photo)
                        <div class="proof-upload-preview">
                            <img
                                src="{{ $storagePhotoUrl($listing->proof_photo) ?: asset('images/placeholder-card.png') }}"
                                alt="Current proof photo"
                                class="card-photo-preview"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                            >
                        </div>
                    @endif

                    <div class="form-field proof-upload-field">
                        <span>Upload proof photo</span>
                        <label class="profile-upload-control marketplace-upload-control marketplace-upload-control--proof">
                            <input type="file" name="proof_photo" accept="image/*" data-file-input>
                            <span class="profile-upload-button">Choose proof</span>
                            <span class="profile-upload-filename" data-file-name>{{ $listing?->proof_photo ? 'Current proof saved' : 'No proof selected' }}</span>
                        </label>
                        @error('proof_photo') <small class="field-error">{{ $message }}</small> @enderror
                    </div>
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
