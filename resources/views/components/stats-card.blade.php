@props([
    'label',
    'value',
    'note' => null,
])

<article {{ $attributes->merge(['class' => 'stat-card']) }}>
    <span class="stat-label">{{ $label }}</span>
    <div class="stat-value">{{ $value }}</div>

    @if ($note)
        <div class="stat-note">{{ $note }}</div>
    @endif

    {{ $slot }}
</article>
