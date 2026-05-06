<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\MarketplaceListing;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(User $user): View
    {
        $user->loadCount(['userCards', 'marketplaceListings', 'wishlistItems']);

        $marketplaceListings = MarketplaceListing::query()
    ->with(['card', 'userCard'])
    ->where('user_id', $user->id)
    ->activeVisible()
    ->latest('updated_at')
    ->get();

        return view('profile.show', [
            'profileUser' => $user,
            'marketplaceListings' => $marketplaceListings,
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Profile updated successfully.');
    }

    public function settings(Request $request): View
    {
        return view('profile.settings', [
            'user' => $request->user(),
        ]);
    }
}
