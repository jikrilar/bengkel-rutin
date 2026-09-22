@props(['status' => 'neutral'])

@php
    $styles = [
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'brand' => 'bg-brand-soft text-brand',
        'neutral' => 'bg-neutral-soft text-neutral',
    ];
@endphp

<span {{ $attributes->class(['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', $styles[$status] ?? $styles['neutral']]) }}>
    {{ $slot }}
</span>
