@props(['value' => 0, 'label' => null])

@php $percentage = max(0, min(100, (float) $value)); @endphp

<div {{ $attributes }}>
    @if ($label)
        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
            <span class="font-medium text-ink">{{ $label }}</span>
            <span class="tabular-nums text-ink-muted">{{ number_format($percentage) }}%</span>
        </div>
    @endif
    <div class="h-2 overflow-hidden rounded-full bg-surface-muted" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentage }}">
        <div class="h-full rounded-full bg-brand" style="width: {{ $percentage }}%"></div>
    </div>
</div>
