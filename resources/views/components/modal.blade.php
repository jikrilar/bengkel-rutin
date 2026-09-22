@props(['name', 'title'])

<div
    x-data="{ open: false }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') { open = true; $nextTick(() => $refs.panel.focus()) }"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') open = false"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50"
>
    <div x-show="open" x-transition.opacity class="absolute inset-0 bg-ink/55" aria-hidden="true"></div>
    <div class="relative flex min-h-full items-end justify-center p-4 sm:items-center">
        <section
            x-ref="panel"
            x-show="open"
            x-transition
            x-on:click.outside="open = false"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $name }}-title"
            class="w-full max-w-lg rounded-[0.875rem] border border-line bg-surface p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <h2 id="{{ $name }}-title" class="text-lg font-semibold text-ink">{{ $title }}</h2>
                <button type="button" x-on:click="open = false" class="grid size-10 place-items-center rounded-field text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="Tutup dialog">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>
            <div class="mt-5">{{ $slot }}</div>
        </section>
    </div>
</div>
