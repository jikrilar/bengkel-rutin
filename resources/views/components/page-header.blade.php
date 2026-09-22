@props(['eyebrow' => null, 'title', 'description' => null])

<header {{ $attributes->class(['flex flex-col gap-5 border-b border-line pb-7 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="max-w-2xl">
        @if ($eyebrow)<p class="mb-2 text-xs font-semibold uppercase tracking-[0.16em] text-brand">{{ $eyebrow }}</p>@endif
        <h1 class="text-3xl font-semibold tracking-[-0.035em] text-ink sm:text-4xl">{{ $title }}</h1>
        @if ($description)<p class="mt-3 max-w-xl text-sm leading-6 text-ink-muted sm:text-base">{{ $description }}</p>@endif
    </div>
    @isset($actions)<div class="flex shrink-0 flex-wrap items-center gap-3">{{ $actions }}</div>@endisset
</header>
