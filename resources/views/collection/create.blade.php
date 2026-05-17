@extends('layouts.app')

@section('title', 'CardFlow | Add Card')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header collection-header">
                    <div>
                        <p class="dashboard-kicker">My Collection</p>
                        <h1>Add a new card</h1>
                        <p class="dashboard-intro">Create a card entry and add it directly to your personal collection.</p>
                    </div>

                    <a href="{{ route('collection.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Back to collection</a>
                </header>

                <section class="dashboard-card collection-card-shell">
                    @if ($errors->any())
                        <div class="auth-status">
                            Please correct the highlighted fields and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('collection.store') }}" class="card-create-form" enctype="multipart/form-data">
                        @csrf

                        <div class="card-form-grid">
                            <div class="form-group">
                                <label class="form-label" for="artist">Artist / Group</label>
                                <input id="artist" type="text" name="artist" class="form-input" value="{{ old('artist') }}" placeholder="Le Sserafim">
                                @error('artist') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="title">Card Title</label>
                                <input id="title" type="text" name="title" class="form-input" value="{{ old('title') }}" placeholder="Chaewon - Easy">
                                @error('title') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="album">Album</label>
                                <input id="album" type="text" name="album" class="form-input" value="{{ old('album') }}" placeholder="Easy">
                                @error('album') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="edition">Edition</label>
                                <input id="edition" type="text" name="edition" class="form-input" value="{{ old('edition') }}" placeholder="Broadcast drop">
                                @error('edition') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="rarity">Rarity</label>
                                <select id="rarity" name="rarity" class="form-input">
                                    @foreach (['Mint', 'Rare', 'Hot', 'Official', 'Wishlist'] as $rarity)
                                        <option value="{{ $rarity }}" @selected(old('rarity', 'Mint') === $rarity)>{{ $rarity }}</option>
                                    @endforeach
                                </select>
                                @error('rarity') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="condition">Condition</label>
                                <select id="condition" name="condition" class="form-input">
                                    @foreach (['Mint', 'Near mint', 'Good'] as $condition)
                                        <option value="{{ $condition }}" @selected(old('condition', 'Mint') === $condition)>{{ $condition }}</option>
                                    @endforeach
                                </select>
                                @error('condition') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="market_value">Market Value</label>
                                <input id="market_value" type="number" name="market_value" class="form-input" value="{{ old('market_value') }}" min="0" step="0.01" placeholder="1450">
                                @error('market_value') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="purchase_price">Purchase Price</label>
                                <input id="purchase_price" type="number" name="purchase_price" class="form-input" value="{{ old('purchase_price') }}" min="0" step="0.01" placeholder="1200">
                                @error('purchase_price') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="estimated_value">Estimated Value</label>
                                <input id="estimated_value" type="number" name="estimated_value" class="form-input" value="{{ old('estimated_value') }}" min="0" step="0.01" placeholder="1450">
                                @error('estimated_value') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="acquired_at">Acquired Date</label>
                                <input id="acquired_at" type="date" name="acquired_at" class="form-input" value="{{ old('acquired_at') }}">
                                @error('acquired_at') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group form-group-wide">
                                <label class="form-label" for="notes">Notes</label>
                                <textarea id="notes" name="notes" rows="4" class="form-input" placeholder="Condition details, source, trade notes...">{{ old('notes') }}</textarea>
                                @error('notes') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group form-group-wide">
                                <label class="form-label" for="photo">Photocard Photo</label>
                                <div class="card-photo-preview-shell">
                                    <img
                                        src="{{ asset('images/placeholder-card.png') }}"
                                        alt="Photocard preview"
                                        class="card-photo-preview"
                                        data-photo-preview
                                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                                    >
                                </div>
                                <input id="photo" type="file" name="photo" class="form-input form-input-file" accept="image/*" capture="environment">
                                <small class="field-help">Upload a photo from your device or take one with your camera.</small>
                                @error('photo') <small class="field-error">{{ $message }}</small> @enderror
                            </div>

                            <div class="form-group form-group-wide collection-marketplace-section">
                                <p class="form-label">Marketplace</p>
                                <h3 class="form-section-title">List this card right away?</h3>
                                <p class="form-section-subtitle">You can keep it private now and list it later from your collection.</p>

                                <label class="marketplace-toggle-row">
                                    <input
                                        type="checkbox"
                                        id="list-on-marketplace"
                                        name="list_on_marketplace"
                                        value="1"
                                        @checked(old('list_on_marketplace'))
                                    >
                                    <span class="marketplace-toggle-ui" aria-hidden="true"></span>
                                    <span>Yes, list on marketplace</span>
                                </label>

                                <div id="marketplace-fields" class="marketplace-fields {{ old('list_on_marketplace') ? '' : 'hidden' }}">
                                    <div class="form-group">
                                        <label class="form-label">Listing type</label>
                                        <div class="marketplace-pill-row">
                                            <label class="marketplace-radio-pill">
                                                <input type="radio" name="listing_type" value="sale" @checked(old('listing_type', 'sale') === 'sale')>
                                                <span>For sale</span>
                                            </label>
                                            <label class="marketplace-radio-pill">
                                                <input type="radio" name="listing_type" value="trade" @checked(old('listing_type') === 'trade')>
                                                <span>For trade</span>
                                            </label>
                                            <label class="marketplace-radio-pill">
                                                <input type="radio" name="listing_type" value="both" @checked(old('listing_type') === 'both')>
                                                <span>Sale or trade</span>
                                            </label>
                                        </div>
                                        @error('listing_type') <small class="field-error">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="listing_price">Asking price (PHP)</label>
                                        <input id="listing_price" type="number" name="listing_price" class="form-input" value="{{ old('listing_price') }}" min="0" step="0.01" placeholder="1500">
                                        <small class="field-help">If you choose trade only, this can stay blank.</small>
                                        @error('listing_price') <small class="field-error">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label" for="listing_description">Listing description</label>
                                        <textarea id="listing_description" name="listing_description" rows="3" class="form-input" placeholder="Condition notes, inclusions, or trade preferences...">{{ old('listing_description') }}</textarea>
                                        @error('listing_description') <small class="field-error">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Publish as</label>
                                        <div class="marketplace-pill-row">
                                            <label class="marketplace-radio-pill">
                                                <input type="radio" name="listing_status" value="active" @checked(old('listing_status', 'active') === 'active')>
                                                <span>Active now</span>
                                            </label>
                                            <label class="marketplace-radio-pill">
                                                <input type="radio" name="listing_status" value="draft" @checked(old('listing_status') === 'draft')>
                                                <span>Save as draft</span>
                                            </label>
                                        </div>
                                        @error('listing_status') <small class="field-error">{{ $message }}</small> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="create-form-actions">
                            <a href="{{ route('collection.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Cancel</a>
                            <button type="submit" class="dashboard-add-card">Save card</button>
                        </div>
                    </form>
                </section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[name="photo"]');
    const preview = document.querySelector('[data-photo-preview]');
    const marketplaceToggle = document.getElementById('list-on-marketplace');

    if (!input || !preview) {
        if (marketplaceToggle) {
            toggleMarketplaceFields(marketplaceToggle);
        }
        return;
    }

    input.addEventListener('change', function (event) {
        const file = event.target.files && event.target.files[0];

        if (!file) {
            preview.src = '{{ asset('images/placeholder-card.png') }}';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (loadEvent) {
            preview.src = loadEvent.target?.result || '{{ asset('images/placeholder-card.png') }}';
        };

        reader.readAsDataURL(file);
    });

    if (marketplaceToggle) {
        toggleMarketplaceFields(marketplaceToggle);
    }
});

function toggleMarketplaceFields(checkbox) {
    const fields = document.getElementById('marketplace-fields');

    if (!fields) {
        return;
    }

    if (checkbox.checked) {
        fields.classList.remove('hidden');
    } else {
        fields.classList.add('hidden');
    }
}
</script>
@endpush
@endsection
