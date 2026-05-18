<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    private const STEPS = [
        1 => 'collection',
        2 => 'marketplace',
        3 => 'wishlist',
        4 => 'messages',
        5 => 'explorer',
    ];

    public function start(): View|RedirectResponse
    {
        if (auth()->user()->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.start');
    }

    public function step(int $step): View|RedirectResponse
    {
        if (auth()->user()->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        if (! isset(self::STEPS[$step])) {
            return redirect()->route('onboarding.start');
        }

        auth()->user()->forceFill([
            'onboarding_step' => $step,
        ])->save();

        return view('onboarding.steps.'.self::STEPS[$step], [
            'step' => $step,
            'totalSteps' => count(self::STEPS),
            'nextStep' => $step + 1,
            'prevStep' => $step - 1,
        ]);
    }

    public function complete(ActivityLogger $activityLogger): RedirectResponse
    {
        $user = auth()->user();

        $user->forceFill([
            'onboarding_completed' => true,
            'onboarding_step' => count(self::STEPS),
        ])->save();

        $activityLogger->record(
            $user,
            'onboarding_complete',
            'Welcome to CardFlow',
            'Your account is ready.'
        );

        return redirect()
            ->route('dashboard')
            ->with('status', 'Welcome to CardFlow! Start by adding your first card.');
    }

    public function skip(): RedirectResponse
    {
        auth()->user()->forceFill([
            'onboarding_completed' => true,
        ])->save();

        return redirect()->route('dashboard');
    }
}
