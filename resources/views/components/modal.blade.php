@props(['name', 'title'])

<div
    x-data="{
        open: false,
        trigger: null,
        show() {
            this.trigger = document.activeElement;
            this.open = true;
            this.$nextTick(() => this.$refs.panel.focus());
        },
        close() {
            this.open = false;
            this.$nextTick(() => this.trigger?.focus());
        },
        trap(event) {
            const focusable = Array.from(this.$refs.panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled])'));
            if (!focusable.length) return;
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (document.activeElement === this.$refs.panel || (event.shiftKey && document.activeElement === first)) {
                event.preventDefault();
                (event.shiftKey ? last : first).focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    }"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') show()"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') close()"
    x-on:keydown.escape.window="if (open) close()"
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
            x-on:click.outside="close()"
            x-on:keydown.tab="trap($event)"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $name }}-title"
            class="w-full max-w-lg rounded-[0.875rem] border border-line bg-surface p-6"
        >
            <div class="flex items-start justify-between gap-4">
                <h2 id="{{ $name }}-title" class="text-lg font-semibold text-ink">{{ $title }}</h2>
                <button type="button" x-on:click="close()" class="grid size-10 place-items-center rounded-field text-ink-muted hover:bg-surface-muted hover:text-ink" aria-label="Tutup dialog">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>
            <div class="mt-5">{{ $slot }}</div>
        </section>
    </div>
</div>
