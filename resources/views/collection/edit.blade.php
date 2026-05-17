@extends('layouts.app')

@section('title', 'CardFlow | Edit Card')
@section('body_class', 'dashboard-body')

@section('topbar')
@endsection

@section('content')
<header class="dashboard-header collection-header">
    <div>
        <p class="dashboard-kicker">My Collection</p>
        <h1>Edit card</h1>
        <p class="dashboard-intro">Update this photocard's details while keeping your collection organized.</p>
    </div>

    <a href="{{ route('collection.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Back to collection</a>
</header>

<section class="dashboard-card collection-card-shell">
    @if ($errors->any())
        <div class="auth-status">
            Please correct the highlighted fields and try again.
        </div>
    @endif

    <form method="POST" action="{{ route('collection.update', $userCard) }}" class="card-create-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card-form-grid">
            <div class="form-group">
                <label class="form-label" for="artist">Artist / Group</label>
                <input id="artist" type="text" name="artist" class="form-input" value="{{ old('artist', $userCard->card->artist) }}" placeholder="Le Sserafim">
                @error('artist') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Card Title</label>
                <input id="title" type="text" name="title" class="form-input" value="{{ old('title', $userCard->card->title) }}" placeholder="Chaewon - Easy">
                @error('title') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="album">Album</label>
                <input id="album" type="text" name="album" class="form-input" value="{{ old('album', $userCard->card->album) }}" placeholder="Easy">
                @error('album') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="edition">Edition</label>
                <input id="edition" type="text" name="edition" class="form-input" value="{{ old('edition', $userCard->card->edition) }}" placeholder="Broadcast drop">
                @error('edition') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="rarity">Rarity</label>
                <select id="rarity" name="rarity" class="form-input">
                    @foreach (['Mint', 'Rare', 'Hot', 'Official', 'Wishlist'] as $rarity)
                        <option value="{{ $rarity }}" @selected(old('rarity', $userCard->card->rarity) === $rarity)>{{ $rarity }}</option>
                    @endforeach
                </select>
                @error('rarity') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="condition">Condition</label>
                <select id="condition" name="condition" class="form-input">
                    @foreach (['Mint', 'Near mint', 'Good'] as $condition)
                        <option value="{{ $condition }}" @selected(old('condition', $userCard->condition) === $condition)>{{ $condition }}</option>
                    @endforeach
                </select>
                @error('condition') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="market_value">Market Value</label>
                <input id="market_value" type="number" name="market_value" class="form-input" value="{{ old('market_value', $userCard->card->market_value) }}" min="0" step="0.01" placeholder="1450">
                @error('market_value') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="purchase_price">Purchase Price</label>
                <input id="purchase_price" type="number" name="purchase_price" class="form-input" value="{{ old('purchase_price', $userCard->purchase_price) }}" min="0" step="0.01" placeholder="1200">
                @error('purchase_price') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="estimated_value">Estimated Value</label>
                <input id="estimated_value" type="number" name="estimated_value" class="form-input" value="{{ old('estimated_value', $userCard->estimated_value) }}" min="0" step="0.01" placeholder="1450">
                @error('estimated_value') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="acquired_at">Acquired Date</label>
                <input id="acquired_at" type="date" name="acquired_at" class="form-input" value="{{ old('acquired_at', optional($userCard->acquired_at)->format('Y-m-d')) }}">
                @error('acquired_at') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group form-group-wide">
                <label class="form-label" for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="4" class="form-input" placeholder="Condition details, source, trade notes...">{{ old('notes', $userCard->notes) }}</textarea>
                @error('notes') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group form-group-wide">
                <label class="form-label" for="photo">Photocard Photo</label>
                <div class="card-photo-preview-shell">
                    <img
                        src="{{ $storagePhotoUrl($userCard->photo_path) ?: asset('images/placeholder-card.png') }}"
                        alt="Photocard preview"
                        class="card-photo-preview"
                        data-photo-preview
                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder-card.png') }}';"
                    >
                </div>
                <input id="photo" type="file" name="photo" class="form-input form-input-file" accept="image/*" capture="environment">
                <small class="field-help">Upload a replacement photo or keep the current one.</small>
                @error('photo') <small class="field-error">{{ $message }}</small> @enderror
            </div>

            <div class="form-group form-group-wide collection-marketplace-section">
                <p class="form-label">Marketplace</p>
                <h3 class="form-section-title">List this card right away?</h3>
                <p class="form-section-subtitle">You can keep it private now and list it later from your collection.</p>

                <label class="marketplace-toggle-row">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $userCard->is_public))>
                    <span class="marketplace-toggle-ui" aria-hidden="true"></span>
                    <span>Show this card on your public marketplace profile</span>
                </label>

                <label class="marketplace-toggle-row">
                    <input type="checkbox" name="is_for_trade" value="1" @checked(old('is_for_trade', $userCard->is_for_trade))>
                    <span class="marketplace-toggle-ui" aria-hidden="true"></span>
                    <span>Mark this card as available for trade</span>
                </label>

                <label class="marketplace-toggle-row">
                    <input type="checkbox" name="is_for_sale" value="1" @checked(old('is_for_sale', $userCard->is_for_sale))>
                    <span class="marketplace-toggle-ui" aria-hidden="true"></span>
                    <span>Mark this card as available for sale</span>
                </label>

                <div class="form-group" style="margin-top: 1rem;">
                    <label class="form-label" for="listing_price">Listing Price</label>
                    <input id="listing_price" type="number" name="listing_price" class="form-input" value="{{ old('listing_price', $userCard->listing_price) }}" min="0" step="0.01" placeholder="1500">
                    <small class="field-help">Set a price when the card is available for sale.</small>
                    @error('listing_price') <small class="field-error">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>

        <div class="create-form-actions">
            <a href="{{ route('collection.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Cancel</a>
            <button type="submit" class="dashboard-add-card">Save changes</button>
        </div>
    </form>
</section>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.querySelector('input[name="photo"]');
    const preview = document.querySelector('[data-photo-preview]');

    if (!input || !preview) {
        return;
    }

    input.addEventListener('change', function (event) {
        const file = event.target.files && event.target.files[0];

        if (!file) {
            preview.src = '{{ $storagePhotoUrl($userCard->photo_path) ?: asset('images/placeholder-card.png') }}';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (loadEvent) {
            preview.src = loadEvent.target?.result || '{{ asset('images/placeholder-card.png') }}';
        };

        reader.readAsDataURL(file);
    });
});
</script>
@endpush
@endsection
