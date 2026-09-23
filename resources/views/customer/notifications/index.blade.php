<x-layouts.app title="Notifikasi">
    <x-page-header
        title="Notifikasi"
        description="Pembaruan rekomendasi, booking, dan servis kendaraan Anda."
    />

    <section class="mt-8 overflow-hidden rounded-panel border border-line bg-surface" aria-labelledby="notification-list-title">
        <h2 id="notification-list-title" class="sr-only">Daftar notifikasi</h2>

        @forelse ($notifications as $notification)
            <article @class([
                'grid gap-3 border-b border-line p-4 last:border-b-0 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:px-5',
                'bg-surface-subtle' => $notification->unread(),
            ])>
                <div class="min-w-0">
                    <div class="flex items-start gap-3">
                        @if ($notification->unread())
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-brand" aria-label="Belum dibaca"></span>
                        @else
                            <span class="mt-2 size-2 shrink-0 rounded-full bg-line-strong" aria-label="Sudah dibaca"></span>
                        @endif
                        <div class="min-w-0">
                            <h3 class="font-semibold text-ink">{{ $notification->data['title'] ?? 'Pembaruan' }}</h3>
                            <p class="mt-1 text-sm leading-6 text-ink-muted">{{ $notification->data['message'] ?? '' }}</p>
                            <time datetime="{{ $notification->created_at->toIso8601String() }}" class="mt-2 block text-xs text-ink-subtle">
                                {{ $notification->created_at->diffForHumans() }}
                            </time>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 pl-5 sm:pl-0">
                    @if ($notification->unread())
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="min-h-10 rounded-field px-3 text-sm font-medium text-ink-muted hover:bg-surface-muted hover:text-ink">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('notifications.open', $notification->id) }}" class="inline-flex min-h-10 items-center rounded-field border border-line px-3 text-sm font-semibold text-ink hover:border-line-strong hover:bg-surface-muted">
                        Buka
                    </a>
                </div>
            </article>
        @empty
            <x-empty-state
                title="Belum ada notifikasi"
                description="Pembaruan rekomendasi, booking, dan servis akan tampil di sini."
                icon="heroicon-o-bell"
            />
        @endforelse
    </section>

    @if ($notifications->hasPages())
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</x-layouts.app>
