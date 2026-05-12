<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use Intervention\Image\Facades\Image;

class CardController extends Controller
{
    public function uploadProof(Request $request, $cardId)
    {
        // 1️⃣ Validate file
        $request->validate([
            'proof' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        // 2️⃣ Find card
        $card = Card::findOrFail($cardId);

        // 3️⃣ Store file
        $path = $request->file('proof')->store('proofs', 'public');

        // 4️⃣ Add digital timestamp overlay
        $image = Image::make(public_path("storage/{$path}"));

        $timestamp = now()->format('Y-m-d H:i:s');
        $username = $request->user()->username;

        $image->text("Uploaded by: {$username}", 10, 20, function($font) {
            $font->size(18);
            $font->color('#FF0000');
            $font->file(public_path('fonts/arial.ttf'));
        });

        $image->text("Time: {$timestamp}", 10, 50, function($font) {
            $font->size(16);
            $font->color('#FF0000');
            $font->file(public_path('fonts/arial.ttf'));
        });

        $image->save();

        // 5️⃣ Save to database
        $card->proof_image = $path;
        $card->proof_uploaded_at = now();
        $card->proof_verified = false; // default pending verification
        $card->save();

        return back()->with('success', 'Proof of possession uploaded successfully!');
    }
}