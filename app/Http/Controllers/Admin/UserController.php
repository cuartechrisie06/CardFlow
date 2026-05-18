<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAction;
use App\Models\AdminUserNote;
use App\Models\MarketplaceListing;
use App\Models\TradeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function show(User $user): View
    {
        $user->load(['userCards.card', 'marketplaceListings.card', 'marketplaceListings.userCard']);

        $note = AdminUserNote::query()
            ->where('admin_id', auth()->id())
            ->where('user_id', $user->id)
            ->first();

        $actions = AdminAction::query()
            ->where('target_type', 'user')
            ->where('target_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();

        $tradeCount = TradeRequest::where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
        })->count();

        return view('admin.users.show', compact('user', 'note', 'actions', 'tradeCount'));
    }

    public function suspend(User $user): RedirectResponse
    {
        abort_if($user->is_admin, 403);

        $user->forceFill(['suspended_at' => now()])->save();
        AdminAction::log('suspend_user', $user, '@'.$user->username, 'Suspended @'.$user->username);

        return back()->with('status', 'User suspended.');
    }

    public function restore(User $user): RedirectResponse
    {
        $user->forceFill(['suspended_at' => null])->save();
        AdminAction::log('restore_user', $user, '@'.$user->username, 'Restored @'.$user->username);

        return back()->with('status', 'User restored.');
    }

    public function saveNote(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:5000']]);

        AdminUserNote::query()->updateOrCreate(
            ['admin_id' => auth()->id(), 'user_id' => $user->id],
            ['note' => $validated['note']]
        );

        AdminAction::log('update_user_note', $user, '@'.$user->username, 'Updated internal note for @'.$user->username);

        return back()->with('status', 'Internal note saved.');
    }
}
