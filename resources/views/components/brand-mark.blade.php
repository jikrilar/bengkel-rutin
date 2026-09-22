@props(['compact' => false])

<span {{ $attributes->class(['inline-flex items-center gap-3']) }}>
    <span class="grid size-9 shrink-0 place-items-center rounded-field bg-action text-ink-inverse" aria-hidden="true">
        <x-heroicon-o-wrench-screwdriver class="size-5" />
    </span>
    @unless ($compact)
        <span class="leading-tight">
            <span class="block text-sm font-semibold tracking-tight text-ink">Bengkel Rutin</span>
            <span class="block text-xs text-ink-muted">Servis lebih terencana</span>
        </span>
    @endunless
</span>
