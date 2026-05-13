<?php

namespace App\Http\Controllers;

use App\Models\UserCard;
use Illuminate\Http\RedirectResponse;

class ProofVerificationController extends Controller
{
    public function approve(UserCard $userCard): RedirectResponse
    {
        if (! $userCard->proof_image) {
            return back()->with('error', 'No proof image uploaded.');
        }

        $userCard->update([
            'proof_verified' => true,
        ]);

        return back()->with('success', 'Proof of possession approved successfully.');
    }
}