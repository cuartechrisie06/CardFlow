<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ComingSoonController extends Controller
{
    public function forgotPassword(): View
    {
        return view('placeholders.coming-soon', [
            'title' => 'Password Reset',
            'message' => 'Password reset is not available in this build yet.',
            'backUrl' => route('login'),
            'backLabel' => 'Back to Sign In',
        ]);
    }
}
