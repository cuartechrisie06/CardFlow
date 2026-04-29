<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceListing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MarketplaceCardController extends Controller
{
    public function show(Request $request, MarketplaceListing $marketplaceListing): View
    {
        $listing = MarketplaceListing::query()
            ->with(['card', 'user', 'userCard'])
            ->activeVisible()
            ->whereKey($marketplaceListing->id)
            ->firstOrFail();

        return view('marketplace.show', [
            'listing' => $listing,
            'userCard' => $listing->userCard,
            'owner' => $listing->user,
        ]);
    }
}
