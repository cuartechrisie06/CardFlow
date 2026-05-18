<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\MarketplaceListing;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ModerationController extends Controller
{
    public function index(): View
    {
        $reports = MarketplaceListing::with(['user', 'card', 'userCard'])
            ->where('proof_status', 'pending')
            ->whereNotNull('proof_photo')
            ->latest()
            ->get();

        $resolved = MarketplaceListing::with(['user', 'card'])
            ->whereIn('proof_status', ['verified', 'failed'])
            ->whereNotNull('proof_photo')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('admin.moderation.index', [
            'reports' => $reports,
            'resolved' => $resolved,
            'pendingCount' => $reports->count(),
        ]);
    }

    public function proof(): View
    {
        $listings = MarketplaceListing::with(['user', 'card', 'userCard'])
            ->where(function ($query) {
                $query->whereNull('proof_of_ownership')
                    ->orWhere('proof_of_ownership', 'none')
                    ->orWhere('proof_of_ownership', 'requested');
            })
            ->latest()
            ->get();

        return view('admin.moderation.proof', compact('listings'));
    }

    public function verifyProof(MarketplaceListing $listing): RedirectResponse
    {
        $listing->forceFill([
            'proof_of_ownership' => 'verified',
            'proof_verified' => true,
            'proof_status' => 'verified',
        ])->save();

        AdminAction::log('verify_proof', $listing, $listing->card?->title ?? 'Listing', 'Verified proof for '.($listing->card?->title ?? 'listing'));

        return back()->with('status', 'Proof marked as verified.');
    }

    public function requestProof(MarketplaceListing $listing): RedirectResponse
    {
        $listing->forceFill(['proof_of_ownership' => 'requested'])->save();

        AdminAction::log('request_proof', $listing, $listing->card?->title ?? 'Listing', 'Requested proof for '.($listing->card?->title ?? 'listing'));

        return back()->with('status', 'Proof request recorded.');
    }
}
