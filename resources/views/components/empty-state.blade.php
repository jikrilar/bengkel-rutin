@props(['title', 'description', 'icon' => 'heroicon-o-inbox'])

<div {{ $attributes->class(['border-y border-line bg-surface-subtle px-5 py-12 text-center sm:px-8']) }}>
    <div class="mx-auto grid size-11 place-items-center rounded-full bg-surface-muted text-ink-muted">
        <x-dynamic-component :component="$icon" class="size-5" />
    </div>
    <h3 class="mt-4 text-base font-semibold text-ink">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-ink-muted">{{ $description }}</p>
    @isset($action)<div class="mt-6">{{ $action }}</div>@endisset
</div>
