<x-layouts.app :title="$title">
    <x-page-header :eyebrow="$eyebrow" :title="$title" :description="$description" />

    <section class="mt-8">
        <x-empty-state :icon="$icon" :title="$emptyTitle" :description="$emptyDescription" />
    </section>
</x-layouts.app>
