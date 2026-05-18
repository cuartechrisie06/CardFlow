<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TradeRequest;
use Illuminate\Contracts\View\View;

class TradeController extends Controller
{
    public function index(): View
    {
        $trades = TradeRequest::with(['sender', 'receiver', 'listing.card', 'offeredCard'])
            ->latest()
            ->get();

        return view('admin.trades.index', compact('trades'));
    }
}
