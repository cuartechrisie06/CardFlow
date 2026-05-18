<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\Card;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $query = Card::query();

        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($nested) use ($search) {
                $nested->where('title', 'like', "%{$search}%")
                    ->orWhere('artist', 'like', "%{$search}%")
                    ->orWhere('album', 'like', "%{$search}%");
            });
        }

        if ($type = $request->query('type')) {
            $query->where('variant_type', $type);
        }

        return view('admin.catalog.index', [
            'cards' => $query->latest()->paginate(12),
            'activeType' => $type,
        ]);
    }

    public function create(): View
    {
        return view('admin.catalog.create', ['card' => new Card()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $card = Card::query()->create($this->validatedData($request));
        AdminAction::log('add_catalog_card', $card, $card->title, 'Added catalog card '.$card->title, 'catalog_card');

        return redirect()->route('admin.catalog.index')->with('status', 'Catalog card saved.');
    }

    public function edit(Card $card): View
    {
        return view('admin.catalog.edit', compact('card'));
    }

    public function update(Request $request, Card $card): RedirectResponse
    {
        $card->update($this->validatedData($request, $card));
        AdminAction::log('edit_catalog_card', $card, $card->title, 'Edited catalog card '.$card->title, 'catalog_card');

        return redirect()->route('admin.catalog.index')->with('status', 'Catalog card updated.');
    }

    public function destroy(Card $card): RedirectResponse
    {
        AdminAction::log('delete_catalog_card', $card, $card->title, 'Archived catalog card '.$card->title, 'catalog_card');
        $card->delete();

        return back()->with('status', 'Catalog card archived.');
    }

    private function validatedData(Request $request, ?Card $card = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'album' => ['nullable', 'string', 'max:255'],
            'variant_type' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $validated['slug'] = Str::slug($validated['artist'].' '.$validated['title']);
        $validated['edition'] = $validated['variant_type'];
        $validated['rarity'] = $card?->rarity ?? 'Official';
        $validated['market_value'] = $card?->market_value ?? 0;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('catalog-cards', 'public');
        } elseif ($card) {
            unset($validated['photo']);
        }

        return $validated;
    }
}
