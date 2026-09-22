@props(['title' => null])

@php
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
    <div x-data="{ sidebarOpen: false, profileOpen: false }" x-on:keydown.escape.window="sidebarOpen = false; profileOpen = false" class="min-h-screen">
        <div x-cloak x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-ink/55 lg:hidden" x-on:click="sidebarOpen = false" aria-hidden="true"></div>

        <aside
            x-bind:class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 flex w-60 flex-col border-r border-line bg-surface transition-transform duration-200 lg:translate-x-0"
            aria-label="Navigasi customer"
        >
            <div class="flex h-20 items-center justify-between border-b border-line px-5">
                <a href="{{ route('dashboard') }}"><x-brand-mark /></a>
                <button type="button" class="grid size-10 place-items-center rounded-field text-ink-muted hover:bg-surface-muted lg:hidden" x-on:click="sidebarOpen = false" aria-label="Tutup navigasi">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto px-3 py-5">
                <p class="px-3 text-[0.68rem] font-semibold uppercase tracking-[0.16em] text-ink-subtle">Menu utama</p>
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
                <button type="button" class="grid size-10 place-items-center rounded-field border border-line bg-surface text-ink lg:hidden" x-on:click="sidebarOpen = true" aria-label="Buka navigasi">
                    <x-heroicon-o-bars-3 class="size-5" />
                </button>
                <p class="hidden text-sm text-ink-muted sm:block lg:ml-auto">{{ now()->translatedFormat('l, d F Y') }}</p>
                <a href="{{ route('notifications.index') }}" class="ml-auto grid size-10 place-items-center rounded-field text-ink-muted hover:bg-surface-muted hover:text-ink sm:ml-4" aria-label="Lihat notifikasi">
                    <x-heroicon-o-bell class="size-5" />
                </a>
            </header>

            <main id="main-content" tabindex="-1" class="mx-auto w-full max-w-[76rem] px-4 py-7 sm:px-6 sm:py-9 lg:px-8 lg:py-10">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.public>
