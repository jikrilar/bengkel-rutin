@props(['title' => null])

@php
    $unreadNotificationsCount = auth()->user()->unreadNotifications()->count();
    $navigation = [
        ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'heroicon-o-squares-2x2'],
        ['label' => 'Kendaraan', 'route' => 'vehicles.index', 'active' => 'vehicles.*', 'icon' => 'heroicon-o-truck'],
        ['label' => 'Rekomendasi Servis', 'route' => 'recommendations.index', 'active' => 'recommendations.*', 'icon' => 'heroicon-o-wrench-screwdriver'],
        ['label' => 'Booking', 'route' => 'bookings.index', 'active' => 'bookings.*', 'icon' => 'heroicon-o-calendar-days'],
        ['label' => 'Riwayat Servis', 'route' => 'service-history.index', 'active' => 'service-history.*', 'icon' => 'heroicon-o-clipboard-document-list'],
        ['label' => 'Notifikasi', 'route' => 'notifications.index', 'active' => 'notifications.*', 'icon' => 'heroicon-o-bell'],
        ['label' => 'Profil', 'route' => 'profile.edit', 'active' => 'profile.*', 'icon' => 'heroicon-o-user-circle'],
    ];
@endphp

<x-layouts.public :title="$title">
    <a href="#main-content" class="sr-only fixed left-4 top-4 z-50 rounded-field bg-action px-4 py-3 text-sm font-semibold text-ink-inverse focus:not-sr-only">Lewati ke konten</a>
    <div
        x-data="{
            sidebarOpen: false,
            desktop: window.matchMedia('(min-width: 1024px)').matches,
            sidebarTrigger: null,
            openSidebar(event) {
                this.sidebarTrigger = event.currentTarget;
                this.sidebarOpen = true;
                this.$nextTick(() => this.$refs.sidebarClose.focus());
            },
            closeSidebar() {
                this.sidebarOpen = false;
                this.$nextTick(() => this.sidebarTrigger?.focus());
            },
            trapSidebar(event) {
                if (!this.sidebarOpen || this.desktop) return;
                const focusable = Array.from(this.$refs.sidebar.querySelectorAll('a[href], button:not([disabled])'));
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        }"
        x-on:keydown.escape.window="if (sidebarOpen) closeSidebar()"
        x-on:resize.window="desktop = window.matchMedia('(min-width: 1024px)').matches"
        class="min-h-screen"
    >
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-ink/55 lg:hidden" x-on:click="closeSidebar()" aria-hidden="true"></div>

        <aside
            id="customer-sidebar"
            x-ref="sidebar"
            x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            x-bind:inert="!desktop && !sidebarOpen"
            x-bind:aria-hidden="!desktop && !sidebarOpen ? 'true' : null"
            x-on:keydown.tab="trapSidebar($event)"
            class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col border-r border-line bg-surface transition-transform duration-200 lg:translate-x-0"
            aria-label="Navigasi customer"
        >
            <div class="flex h-20 items-center justify-between border-b border-line px-5">
                <a href="{{ route('dashboard') }}"><x-brand-mark /></a>
                <button x-ref="sidebarClose" type="button" class="grid size-10 place-items-center rounded-field text-ink-muted hover:bg-surface-muted lg:hidden" x-on:click="closeSidebar()" aria-label="Tutup navigasi">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-5">
                <p class="px-3 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-ink-muted">Menu utama</p>
                <ul class="mt-3 space-y-1">
                    @foreach ($navigation as $item)
                        <li>
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'flex min-h-11 items-center gap-3 rounded-field px-3 text-sm font-medium transition-colors',
                                    'bg-brand-soft text-brand' => request()->routeIs($item['active']),
                                    'text-ink-muted hover:bg-surface-muted hover:text-ink' => ! request()->routeIs($item['active']),
                                ])
                                @if (request()->routeIs($item['active'])) aria-current="page" @endif
                            >
                                <x-dynamic-component :component="$item['icon']" class="size-5 shrink-0" />
                                <span>{{ $item['label'] }}</span>
                                @if ($item['route'] === 'notifications.index' && $unreadNotificationsCount > 0)
                                    <span class="ml-auto min-w-5 rounded-full bg-brand-soft px-1.5 py-0.5 text-center text-xs font-semibold text-brand" aria-label="{{ $unreadNotificationsCount }} notifikasi belum dibaca">
                                        {{ min($unreadNotificationsCount, 99) }}
                                    </span>
                                @endif
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <div class="border-t border-line p-4">
                <div class="flex items-center gap-3 px-2">
                    <span class="grid size-9 shrink-0 place-items-center rounded-full bg-surface-muted text-sm font-semibold text-ink">
                        {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-ink">{{ auth()->user()->name }}</p>
                        <p class="truncate text-xs text-ink-muted">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="flex min-h-10 w-full items-center gap-3 rounded-field px-3 text-sm font-medium text-ink-muted hover:bg-surface-muted hover:text-ink">
                        <x-heroicon-o-arrow-right-start-on-rectangle class="size-5" />
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <div class="lg:pl-60">
            <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-line bg-canvas px-4 sm:px-6 lg:px-8">
                <button type="button" class="grid size-10 place-items-center rounded-field border border-line bg-surface text-ink lg:hidden" x-on:click="openSidebar($event)" x-bind:aria-expanded="sidebarOpen.toString()" aria-controls="customer-sidebar" aria-label="Buka navigasi">
                    <x-heroicon-o-bars-3 class="size-5" />
                </button>
                <p class="hidden text-sm text-ink-muted sm:block lg:ml-auto">{{ now()->translatedFormat('l, d F Y') }}</p>
                <a href="{{ route('notifications.index') }}" class="relative ml-auto grid size-10 place-items-center rounded-field text-ink-muted hover:bg-surface-muted hover:text-ink sm:ml-4" aria-label="Lihat notifikasi{{ $unreadNotificationsCount > 0 ? ', '.$unreadNotificationsCount.' belum dibaca' : '' }}">
                    <x-heroicon-o-bell class="size-5" />
                    @if ($unreadNotificationsCount > 0)
                        <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-brand" aria-hidden="true"></span>
                    @endif
                </a>
            </header>

            <main id="main-content" tabindex="-1" class="mx-auto w-full max-w-[76rem] px-4 py-7 sm:px-6 sm:py-9 lg:px-8 lg:py-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.public>
