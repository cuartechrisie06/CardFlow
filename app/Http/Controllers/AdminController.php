<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\AdminAction;
use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\User;
use App\Models\UserCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_cards' => UserCard::count(),
            'catalog_cards' => Card::count(),
            'total_listings' => MarketplaceListing::count(),
            'active_listings' => MarketplaceListing::where('status', 'active')->count(),
            'total_trades' => TradeRequest::count(),
            'completed_trades' => TradeRequest::where('status', 'completed')->count(),
            'pending_proofs' => MarketplaceListing::where('proof_status', 'pending')->whereNotNull('proof_photo')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
        ];

        $recentUsers = User::latest()->limit(5)->get();
        $recentListings = MarketplaceListing::with(['user', 'card'])->latest()->limit(5)->get();
        $pendingProofs = MarketplaceListing::with(['user', 'card'])
            ->where('proof_status', 'pending')
            ->whereNotNull('proof_photo')
            ->latest()
            ->get();

        return view('admin.index', compact('stats', 'recentUsers', 'recentListings', 'pendingProofs'));
    }

    public function users(): View
    {
        $users = User::withCount(['userCards', 'marketplaceListings'])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function toggleAdmin(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'error' => 'Cannot remove your own admin access.',
            ]);
        }

        $user->forceFill(['is_admin' => ! $user->is_admin])->save();
        AdminAction::log('promote_user', $user, '@'.$user->username, 'Updated admin status for @'.$user->username);

        return back()->with('status', $user->name.' admin status updated.');
    }

    public function deleteUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors([
                'error' => 'Cannot delete your own account.',
            ]);
        }

        AdminAction::log('delete_user', $user, '@'.$user->username, 'Deleted user @'.$user->username);
        $user->delete();

        return back()->with('status', 'User deleted successfully.');
    }

    public function listings(): View
    {
        $listings = MarketplaceListing::with(['user', 'card'])
            ->latest()
            ->paginate(20);

        return view('admin.listings.index', compact('listings'));
    }

    public function deleteListing(MarketplaceListing $marketplaceListing): RedirectResponse
    {
        AdminAction::log('delete_listing', $marketplaceListing, $marketplaceListing->card?->title ?? 'Listing', 'Deleted listing '.($marketplaceListing->card?->title ?? '#'.$marketplaceListing->id));
        $marketplaceListing->delete();

        return back()->with('status', 'Listing removed.');
    }

    public function verifyProof(MarketplaceListing $marketplaceListing): RedirectResponse
    {
        $marketplaceListing->forceFill([
            'proof_verified' => true,
            'proof_status' => 'verified',
            'proof_score' => 100,
            'proof_of_ownership' => 'verified',
        ])->save();
        AdminAction::log('verify_proof', $marketplaceListing, $marketplaceListing->card?->title ?? 'Listing', 'Verified proof for '.($marketplaceListing->card?->title ?? 'listing'));

        return back()->with('status', 'Proof verified for: '.($marketplaceListing->card?->title ?? 'listing'));
    }

    public function trades(): View
    {
        $trades = TradeRequest::with(['sender', 'receiver', 'listing.card', 'offeredCard'])
            ->latest()
            ->paginate(20);

        return view('admin.trades.index', compact('trades'));
    }

    public function reports(): View
    {
        return view('admin.moderation.index', [
            'reports' => collect(),
            'resolved' => collect(),
            'pendingCount' => 0,
        ]);
    }
}
