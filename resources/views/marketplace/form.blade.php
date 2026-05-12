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

<section class="dashboard-card marketplace-create-card">
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

            if (! $currentType && $selectedUserCard) {
                $currentType = $selectedUserCard->is_for_sale ? 'sale' : 'trade';
            }
        @endphp

        <form method="POST" action="{{ $formAction }}" class="marketplace-create-form" enctype="multipart/form-data">
            @csrf
            @if ($formMethod !== 'POST')
                @method($formMethod)
            @endif

            @if (! $listing)
                <label class="form-field">
                    <span>Collection item</span>
                    <select name="user_card_id" required>
                        <option value="">Choose a card from your collection</option>
                        @foreach ($userCards as $userCard)
                            <option value="{{ $userCard->id }}" @selected(old('user_card_id') == $userCard->id)>
                                {{ $userCard->card->title ?? 'Untitled Card' }} - {{ $userCard->card->artist ?? 'Unknown Group' }} - {{ $userCard->condition ?? 'No condition' }}
                            </option>
                        @endforeach
                    </select>
                </label>
            @else
                <div class="listing-edit-preview">
                    <strong>{{ $selectedUserCard->card->title ?? 'Untitled Card' }}</strong>
                    <span>{{ $selectedUserCard->card->artist ?? 'Unknown Group' }}</span>
                    <span>{{ $selectedUserCard->condition ?? 'No condition' }}</span>
                </div>
            @endif

            <div class="card-form-grid marketplace-form-grid">
                <label class="form-field">
                    <span>Title</span>
                    <input type="text" name="title" value="{{ old('title', $cardSource?->title) }}" required>
                </label>

                <label class="form-field">
                    <span>Artist</span>
                    <input type="text" name="artist" value="{{ old('artist', $cardSource?->artist) }}" required>
                </label>

                <label class="form-field">
                    <span>Rarity</span>
                    <input type="text" name="rarity" value="{{ old('rarity', $cardSource?->rarity) }}" required>
                </label>

                <label class="form-field">
                    <span>Type</span>
                    <select name="type" required>
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected($currentType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="form-field">
                    <span>Price</span>
                    <input
                        type="number"
                        name="listing_price"
                        value="{{ old('listing_price', $selectedUserCard?->listing_price) }}"
                        min="0"
                        step="0.01"
                        placeholder="Required for sale listings"
                    >
                </label>

                <label class="form-field">
                    <span>Status</span>
                    <select name="status" required>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $listing?->status ?? 'draft') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <label class="form-field">
                <span>Description</span>
                <textarea name="description" rows="4" placeholder="Condition notes, packaging details, or trading preferences...">{{ old('description', $selectedUserCard?->notes) }}</textarea>
            </label>

            <label class="form-field">
                <span>Photo</span>
                <input type="file" name="photo" accept="image/*">
            </label>

            <label class="form-field">
                <span>Proof of Possession</span>
                <input type="file" name="proof_image" accept="image/*">
            </label>

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
