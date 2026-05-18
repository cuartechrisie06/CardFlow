@extends('onboarding.layout')

@section('content')
<div style="max-width:620px;width:100%;">
    <div style="margin-bottom:32px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
            <span style="font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#8B4513;">Step {{ $step }} of {{ $totalSteps }}</span>
            <form method="POST" action="{{ route('onboarding.skip') }}">
                @csrf
                <button type="submit" style="background:none;border:none;font-family:'DM Sans',sans-serif;font-size:0.75rem;color:#b09070;cursor:pointer;">Skip tour</button>
            </form>
        </div>
        <div style="height:4px;background:#e8d5c0;border-radius:2px;overflow:hidden;">
            <div style="height:100%;width:{{ ($step / $totalSteps) * 100 }}%;background:#8B4513;border-radius:2px;transition:width 0.5s ease;"></div>
        </div>
        <div style="display:flex;gap:8px;justify-content:center;margin-top:12px;">
            @for($i = 1; $i <= $totalSteps; $i++)
                <div style="width:8px;height:8px;border-radius:50%;background:{{ $i <= $step ? '#8B4513' : '#e8d5c0' }};"></div>
            @endfor
        </div>
    </div>

    <div style="background:#ffffff;border-radius:24px;padding:40px;box-shadow:0 8px 40px rgba(139,69,19,0.1);">
        <div style="width:64px;height:64px;background:#f5e6d8;border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:20px;color:#8B4513;font-weight:800;">
            {{ $shortLabel }}
        </div>

        <p style="font-family:'DM Sans',sans-serif;font-size:0.65rem;text-transform:uppercase;letter-spacing:0.08em;color:#8B4513;margin:0 0 8px;">
            STEP {{ $step }} - {{ $eyebrow }}
        </p>

        <h2 style="font-family:'Playfair Display',serif;font-size:1.6rem;font-weight:700;color:#3d2b1f;margin:0 0 12px;">
            {{ $title }}
        </h2>

        <p style="font-family:'DM Sans',sans-serif;font-size:0.9rem;color:#8B6F5E;margin:0 0 24px;line-height:1.6;">
            {{ $description }}
        </p>

        @foreach($tips as $tip)
            <div style="display:flex;gap:12px;align-items:flex-start;margin-bottom:12px;">
                <span style="color:#8B4513;font-weight:700;flex-shrink:0;margin-top:2px;">*</span>
                <p style="font-family:'DM Sans',sans-serif;font-size:0.85rem;color:#8B6F5E;margin:0;line-height:1.4;">
                    {{ $tip }}
                </p>
            </div>
        @endforeach

        <div style="background:#fdf6f0;border-radius:14px;padding:16px;margin:24px 0;border:1px solid #e8d5c0;display:flex;align-items:center;gap:14px;">
            <div style="font-size:1.2rem;color:#8B4513;">Tip</div>
            <div>
                <p style="font-family:'DM Sans',sans-serif;font-size:0.8rem;font-weight:600;color:#3d2b1f;margin:0 0 2px;">Quick start tip</p>
                <p style="font-family:'DM Sans',sans-serif;font-size:0.78rem;color:#8B6F5E;margin:0;">{{ $quickTip }}</p>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <a href="{{ $prevStep > 0 ? route('onboarding.step', $prevStep) : route('onboarding.start') }}"
               style="padding:12px 24px;border:1px solid #d4b896;border-radius:30px;color:#8B4513;font-family:'DM Sans',sans-serif;font-size:0.85rem;text-decoration:none;">
                Back
            </a>

            @if($nextStep <= $totalSteps)
                <a href="{{ route('onboarding.step', $nextStep) }}"
                   style="flex:1;background:#8B4513;color:#ffffff;font-family:'DM Sans',sans-serif;font-size:0.88rem;font-weight:600;padding:12px 24px;border-radius:30px;text-decoration:none;text-align:center;">
                    Next: {{ ['', 'Collection', 'Marketplace', 'Wishlist', 'Messages', 'Explorer'][$nextStep] }}
                </a>
            @else
                <form method="POST" action="{{ route('onboarding.complete') }}" style="flex:1;">
                    @csrf
                    <button type="submit" style="width:100%;background:#2d6a4f;color:#ffffff;font-family:'DM Sans',sans-serif;font-size:0.88rem;font-weight:600;padding:12px 24px;border-radius:30px;border:none;cursor:pointer;">
                        Finish and go to dashboard
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
