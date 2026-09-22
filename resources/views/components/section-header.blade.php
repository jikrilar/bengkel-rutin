@props(['title', 'description' => null])

<div {{ $attributes->class(['flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        <h2 class="text-lg font-semibold tracking-tight text-ink">{{ $title }}</h2>
        @if ($description)<p class="mt-1 text-sm leading-6 text-ink-muted">{{ $description }}</p>@endif
    </div>
    @isset($actions)<div class="mt-3 sm:mt-0">{{ $actions }}</div>@endisset
</div>
