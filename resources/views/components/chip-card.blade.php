@props([
    'label',
    'value',
])

<article {{ $attributes->merge(['class' => 'card-detail-chip']) }}>
    <span class="summary-label">{{ $label }}</span>
    <strong>{{ $value }}</strong>

    {{ $slot }}
</article>
