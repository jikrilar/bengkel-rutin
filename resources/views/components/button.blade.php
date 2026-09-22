@props([
    'href' => null,
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-field font-semibold transition-colors duration-150 disabled:cursor-not-allowed disabled:opacity-50';
    $variants = [
        'primary' => 'bg-action text-ink-inverse hover:bg-action-hover',
        'brand' => 'bg-brand text-ink-inverse hover:bg-brand-hover',
        'secondary' => 'border border-line-strong bg-surface text-ink hover:bg-surface-muted',
        'ghost' => 'text-ink-muted hover:bg-surface-muted hover:text-ink',
        'danger' => 'bg-danger text-ink-inverse hover:bg-red-800',
    ];
    $sizes = [
        'sm' => 'min-h-9 px-3 text-sm',
        'md' => 'min-h-11 px-4 text-sm',
        'lg' => 'min-h-12 px-5 text-base',
    ];
    $classes = trim($base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
