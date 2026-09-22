@props(['type' => 'info', 'title' => null])

@php
    $styles = [
        'success' => 'border-success/25 bg-success-soft text-success',
        'warning' => 'border-warning/25 bg-warning-soft text-warning',
        'danger' => 'border-danger/25 bg-danger-soft text-danger',
        'info' => 'border-line-strong bg-surface-muted text-ink',
    ];
@endphp

<div role="alert" {{ $attributes->class(['rounded-panel border px-4 py-3 text-sm', $styles[$type] ?? $styles['info']]) }}>
    @if ($title)<p class="font-semibold">{{ $title }}</p>@endif
    <div @class(['mt-1' => $title])>{{ $slot }}</div>
</div>
