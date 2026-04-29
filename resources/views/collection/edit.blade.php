<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CardFlow | Edit Card</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-body">
        @php
            $user = auth()->user();
            $username = $user->username ?: 'collector';
        @endphp
        <main class="dashboard-shell">
            <aside class="dashboard-sidebar">
                <div class="sidebar-brand">
                    <div class="sidebar-avatar"></div>
                    <div>
                        <p>{{ $user->name }}</p>
                        <span>{{ '@'.$username }}</span>
                    </div>
                </div>

                <nav class="sidebar-nav" aria-label="Primary">
                    <a href="{{ route('dashboard') }}" class="sidebar-link">Dashboard</a>
                    <a href="{{ route('collection.index') }}" class="sidebar-link is-active">My Collection</a>
                    <a href="{{ route('marketplace.index') }}" class="sidebar-link">Marketplace</a>
                    <a href="{{ route('wishlist.index') }}" class="sidebar-link">Wishlist</a>
                    <a href="#" class="sidebar-link">Messages</a>
                    <a href="{{ route('explorer.index') }}" class="sidebar-link">Explorer</a>
                    <a href="{{ route('stats.index') }}" class="sidebar-link">Stats</a>
                </nav>

                @include('partials.sidebar-collector', ['user' => $user])
            </aside>

            <section class="dashboard-main">
                <header class="dashboard-header collection-header">
                    <div>
                        <p class="dashboard-kicker">My Collection</p>
                        <h1>Edit card</h1>
                        <p class="dashboard-intro">Update this photocard’s details while keeping your collection organized.</p>
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
                            <label class="field-group">
                                <span>Artist / Group</span>
                                <input type="text" name="artist" value="{{ old('artist', $userCard->card->artist) }}" placeholder="Le Sserafim">
                                @error('artist') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Card Title</span>
                                <input type="text" name="title" value="{{ old('title', $userCard->card->title) }}" placeholder="Chaewon - Easy">
                                @error('title') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Album</span>
                                <input type="text" name="album" value="{{ old('album', $userCard->card->album) }}" placeholder="Easy">
                                @error('album') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Edition</span>
                                <input type="text" name="edition" value="{{ old('edition', $userCard->card->edition) }}" placeholder="Broadcast drop">
                                @error('edition') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Rarity</span>
                                <select name="rarity" class="field-select">
                                    @foreach (['Mint', 'Rare', 'Hot', 'Official', 'Wishlist'] as $rarity)
                                        <option value="{{ $rarity }}" @selected(old('rarity', $userCard->card->rarity) === $rarity)>{{ $rarity }}</option>
                                    @endforeach
                                </select>
                                @error('rarity') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Photocard Photo</span>
                                <input type="file" name="photo" class="field-file" accept="image/*" capture="environment">
                                <small class="field-help">Upload a replacement photo or keep the current one.</small>
                                @error('photo') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Market Value</span>
                                <input type="number" name="market_value" value="{{ old('market_value', $userCard->card->market_value) }}" min="0" step="0.01" placeholder="1450">
                                @error('market_value') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Estimated Value</span>
                                <input type="number" name="estimated_value" value="{{ old('estimated_value', $userCard->estimated_value) }}" min="0" step="0.01" placeholder="1450">
                                @error('estimated_value') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Purchase Price</span>
                                <input type="number" name="purchase_price" value="{{ old('purchase_price', $userCard->purchase_price) }}" min="0" step="0.01" placeholder="1200">
                                @error('purchase_price') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Condition</span>
                                <select name="condition" class="field-select">
                                    @foreach (['Mint', 'Near mint', 'Good'] as $condition)
                                        <option value="{{ $condition }}" @selected(old('condition', $userCard->condition) === $condition)>{{ $condition }}</option>
                                    @endforeach
                                </select>
                                @error('condition') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group">
                                <span>Acquired At</span>
                                <input type="date" name="acquired_at" value="{{ old('acquired_at', optional($userCard->acquired_at)->format('Y-m-d')) }}">
                                @error('acquired_at') <small class="field-error">{{ $message }}</small> @enderror
                            </label>

                            <label class="field-group field-group-wide">
                                <span>Notes</span>
                                <textarea name="notes" rows="4" placeholder="Condition details, source, trade notes...">{{ old('notes', $userCard->notes) }}</textarea>
                                @error('notes') <small class="field-error">{{ $message }}</small> @enderror
                            </label>
                        </div>

                        <label class="remember-row create-checkbox">
                            <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $userCard->is_public))>
                            <span>Show this card on your public marketplace profile</span>
                        </label>

                        <label class="remember-row create-checkbox">
                            <input type="checkbox" name="is_for_trade" value="1" @checked(old('is_for_trade', $userCard->is_for_trade))>
                            <span>Mark this card as available for trade</span>
                        </label>

                        <label class="remember-row create-checkbox">
                            <input type="checkbox" name="is_for_sale" value="1" @checked(old('is_for_sale', $userCard->is_for_sale))>
                            <span>Mark this card as available for sale</span>
                        </label>

                        <label class="field-group">
                            <span>Listing Price</span>
                            <input type="number" name="listing_price" value="{{ old('listing_price', $userCard->listing_price) }}" min="0" step="0.01" placeholder="1500">
                            @error('listing_price') <small class="field-error">{{ $message }}</small> @enderror
                        </label>

                        <div class="create-form-actions">
                            <a href="{{ route('collection.index') }}" class="dashboard-add-card dashboard-add-card-secondary">Cancel</a>
                            <button type="submit" class="dashboard-add-card">Save changes</button>
                        </div>
                    </form>
                </section>
            </section>
        </main>
    </body>
</html>
