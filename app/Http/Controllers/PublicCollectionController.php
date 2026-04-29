<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PublicCollectionController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $cards = MarketplaceListing::query()
            ->with(['card', 'user', 'userCard'])
            ->where('user_id', $user->id)
            ->activeVisible()
            ->latest('updated_at')
            ->paginate(8)
            ->withQueryString();

        return view('marketplace.collection', [
            'profileUser' => $user,
            'publicCards' => $cards,
        ]);
    }
}
