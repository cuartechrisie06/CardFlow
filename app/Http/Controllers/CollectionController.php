<?php

namespace App\Http\Controllers;

use App\Models\UserCard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $search = trim((string) $request->string('q'));
        $filter = (string) $request->string('filter', 'all');

        $collectionQuery = UserCard::query()
            ->with('card')
            ->where('user_id', $user->id)
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('card', function ($cardQuery) use ($search) {
                    $cardQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('artist', 'like', "%{$search}%")
                        ->orWhere('album', 'like', "%{$search}%")
                        ->orWhere('edition', 'like', "%{$search}%");
                });
            })
            ->when($filter !== 'all', function ($query) use ($filter) {
                $query->where(function ($nested) use ($filter) {
                    if ($filter === 'rare') {
                        $nested->whereHas('card', fn ($cardQuery) => $cardQuery->whereIn('rarity', ['Rare', 'Hot']));
                    }

                    if ($filter === 'limited') {
                        $nested->whereHas('card', fn ($cardQuery) => $cardQuery->where('edition', 'like', '%Limited%')
                            ->orWhere('edition', 'like', '%Broadcast%')
                            ->orWhere('edition', 'like', '%Fansign%'));
                    }

                    if ($filter === 'mint') {
                        $nested->where('condition', 'Mint');
                    }

                    if ($filter === 'album') {
                        $nested->whereHas('card', fn ($cardQuery) => $cardQuery->whereNotNull('album'));
                    }
                });
            })
            ->latest('acquired_at')
            ->latest();

        $collectionCards = $collectionQuery->paginate(10)->withQueryString();

        return view('collection.index', [
            'collectionCards' => $collectionCards,
            'filters' => [
                'search' => $search,
                'active' => $filter,
                'items' => [
                    'all' => 'All',
                    'rare' => 'Rare',
                    'limited' => 'Limited',
                    'mint' => 'Mint',
                    'album' => 'Album',
                ],
            ],
            'collectionCount' => $user->userCards()->count(),
        ]);
    }
}
