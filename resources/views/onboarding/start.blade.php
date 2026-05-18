@extends('onboarding.layout')

@section('content')
<div style="max-width:560px;width:100%;text-align:center;">
    <div style="margin-bottom:32px;">
        <div style="display:inline-flex;align-items:center;gap:10px;color:#8B4513;font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;">
            <span style="width:34px;height:34px;border-radius:9px;background:#8B4513;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-family:'DM Sans',sans-serif;font-size:0.76rem;">CF</span>
            CardFlow
        </div>
    </div>

    <div style="background:#ffffff;border-radius:24px;padding:48px 40px;box-shadow:0 8px 40px rgba(139,69,19,0.1);margin-bottom:24px;">
        <div style="font-size:3rem;margin-bottom:16px;">*</div>

        <p style="font-family:'DM Sans',sans-serif;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.1em;color:#8B4513;margin:0 0 8px;">
            WELCOME TO CARDFLOW
        </p>

        <h1 style="font-family:'Playfair Display',serif;font-size:2rem;font-weight:700;color:#3d2b1f;margin:0 0 16px;line-height:1.2;">
            Hi {{ auth()->user()->name }},<br>you're all set.
        </h1>

        <p style="font-family:'DM Sans',sans-serif;font-size:0.92rem;color:#8B6F5E;margin:0 0 32px;line-height:1.6;">
            CardFlow is your personal K-pop photocard collection and trading hub. Let us walk you through the key features in five quick steps.
        </p>

        <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;margin-bottom:32px;">
            @foreach([
                ['Manage collection'],
                ['List on marketplace'],
                ['Message sellers'],
                ['Track wishlist'],
                ['Explore idols'],
            ] as $feat)
                <span style="font-family:'DM Sans',sans-serif;font-size:0.78rem;background:#f5e6d8;color:#8B4513;padding:6px 14px;border-radius:20px;">
                    {{ $feat[0] }}
                </span>
            @endforeach
        </div>

        <a href="{{ route('onboarding.step', 1) }}"
           style="display:block;background:#8B4513;color:#ffffff;font-family:'DM Sans',sans-serif;font-size:0.95rem;font-weight:600;padding:16px 32px;border-radius:30px;text-decoration:none;margin-bottom:12px;">
            Start the tour
        </a>

        <form method="POST" action="{{ route('onboarding.skip') }}">
            @csrf
            <button type="submit" style="background:none;border:none;font-family:'DM Sans',sans-serif;font-size:0.82rem;color:#b09070;cursor:pointer;padding:8px;">
                Skip for now - take me to the app
            </button>
        </form>
    </div>

    <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#b09070;">
        You can always revisit this guide from your profile settings.
    </p>
</div>
@endsection
