<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\UserCard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CollectionCardController extends Controller
{
    public function create(): View
    {
        return view('collection.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'artist' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'edition' => ['nullable', 'string', 'max:255'],
            'rarity' => ['required', 'string', 'max:255'],
            'market_value' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'acquired_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'is_for_trade' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('user-cards', 'public')
            : null;

        DB::transaction(function () use ($request, $validated, $photoPath) {
            $card = Card::query()->firstOrCreate(
                [
                    'artist' => $validated['artist'],
                    'title' => $validated['title'],
                    'album' => $validated['album'] ?? null,
                    'edition' => $validated['edition'] ?? null,
                ],
                [
                    'rarity' => $validated['rarity'],
                    'market_value' => $validated['market_value'],
                    'thumbnail_style' => 'market-thumb-one',
                    'trend_score' => 65,
                ]
            );

            UserCard::query()->create([
                'user_id' => $request->user()->id,
                'card_id' => $card->id,
                'condition' => $validated['condition'],
                'purchase_price' => $validated['purchase_price'] ?? null,
                'estimated_value' => $validated['estimated_value'] ?? $validated['market_value'],
                'acquired_at' => $validated['acquired_at'] ?? now(),
                'is_for_trade' => $request->boolean('is_for_trade'),
                'photo_path' => $photoPath,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('collection.index')
            ->with('status', 'Card added to your collection.');
    }

    public function edit(Request $request, UserCard $userCard): View
    {
        abort_unless($userCard->user_id === $request->user()->id, 403);

        $userCard->load('card');

        return view('collection.edit', [
            'userCard' => $userCard,
        ]);
    }

    public function update(Request $request, UserCard $userCard): RedirectResponse
    {
        abort_unless($userCard->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'artist' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'edition' => ['nullable', 'string', 'max:255'],
            'rarity' => ['required', 'string', 'max:255'],
            'market_value' => ['required', 'numeric', 'min:0'],
            'condition' => ['required', 'string', 'max:255'],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'acquired_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'is_for_trade' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $newPhotoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('user-cards', 'public')
            : null;

        DB::transaction(function () use ($request, $validated, $userCard, $newPhotoPath) {
            $userCard->card->update([
                'artist' => $validated['artist'],
                'title' => $validated['title'],
                'album' => $validated['album'] ?? null,
                'edition' => $validated['edition'] ?? null,
                'rarity' => $validated['rarity'],
                'market_value' => $validated['market_value'],
            ]);

            if ($newPhotoPath && $userCard->photo_path) {
                Storage::disk('public')->delete($userCard->photo_path);
            }

            $userCard->update([
                'condition' => $validated['condition'],
                'purchase_price' => $validated['purchase_price'] ?? null,
                'estimated_value' => $validated['estimated_value'] ?? $validated['market_value'],
                'acquired_at' => $validated['acquired_at'] ?? $userCard->acquired_at,
                'is_for_trade' => $request->boolean('is_for_trade'),
                'photo_path' => $newPhotoPath ?: $userCard->photo_path,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('collection.index')
            ->with('status', 'Card updated successfully.');
    }
}
