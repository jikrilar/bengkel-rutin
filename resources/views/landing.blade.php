<x-layouts.public>
    <div x-data="{ menuOpen: false }" x-on:keydown.escape.window="menuOpen = false">
        <header class="border-b border-line bg-surface">
            <div class="mx-auto flex h-[4.5rem] max-w-[76rem] items-center justify-between px-5 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" aria-label="Bengkel Rutin, beranda"><x-brand-mark /></a>

                <nav class="hidden items-center gap-7 md:flex" aria-label="Navigasi utama">
                    <a href="#cara-kerja" class="text-sm font-medium text-ink-muted hover:text-ink">Cara kerja</a>
                    <a href="#manfaat" class="text-sm font-medium text-ink-muted hover:text-ink">Manfaat</a>
                    <a href="#bengkel" class="text-sm font-medium text-ink-muted hover:text-ink">Bengkel</a>
                </nav>

                <div class="hidden items-center gap-2 md:flex">
                    @auth
                        @if (auth()->user()->role === \App\Enums\UserRole::Admin)
                            <x-button href="/admin" variant="secondary" size="sm">Panel admin</x-button>
                        @else
                            <x-button :href="route('dashboard')" variant="secondary" size="sm">Buka dashboard</x-button>
                        @endif
                    @else
                        <x-button :href="route('login')" variant="ghost" size="sm">Masuk</x-button>
                        <x-button :href="route('register')" variant="brand" size="sm">Daftar</x-button>
                    @endauth
                </div>

                <button type="button" class="grid size-10 place-items-center rounded-field border border-line md:hidden" x-on:click="menuOpen = ! menuOpen" x-bind:aria-expanded="menuOpen" aria-controls="mobile-menu" aria-label="Buka menu">
                    <x-heroicon-o-bars-3 class="size-5" />
                </button>
            </div>

            <div id="mobile-menu" x-cloak x-show="menuOpen" x-transition class="border-t border-line bg-surface px-5 py-5 md:hidden">
                <nav class="space-y-1" aria-label="Navigasi mobile">
                    <a href="#cara-kerja" x-on:click="menuOpen = false" class="block rounded-field px-3 py-3 text-sm font-medium text-ink">Cara kerja</a>
                    <a href="#manfaat" x-on:click="menuOpen = false" class="block rounded-field px-3 py-3 text-sm font-medium text-ink">Manfaat</a>
                    <a href="#bengkel" x-on:click="menuOpen = false" class="block rounded-field px-3 py-3 text-sm font-medium text-ink">Bengkel</a>
                </nav>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t border-line pt-4">
                    @auth
                        <x-button :href="auth()->user()->role === \App\Enums\UserRole::Admin ? '/admin' : route('dashboard')" variant="brand" class="col-span-2">Buka aplikasi</x-button>
                    @else
                        <x-button :href="route('login')" variant="secondary">Masuk</x-button>
                        <x-button :href="route('register')" variant="brand">Daftar</x-button>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section class="mx-auto max-w-[76rem] px-5 pb-16 pt-14 sm:px-6 sm:pb-20 sm:pt-20 lg:px-8 lg:pb-24 lg:pt-24">
                <div class="max-w-4xl">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand">Perawatan kendaraan yang terukur</p>
                    <h1 class="mt-5 text-[clamp(3rem,8vw,6.8rem)] font-semibold leading-[0.9] tracking-[-0.065em] text-ink">
                        Rawat kendaraan.<br>
                        <span class="text-brand">Sebelum terlambat.</span>
                    </h1>
                    <div class="mt-8 flex max-w-2xl flex-col gap-6 border-l-2 border-brand pl-5 sm:flex-row sm:items-end sm:justify-between sm:pl-7">
                        <p class="max-w-lg text-base leading-7 text-ink-muted sm:text-lg">
                            Pantau interval servis, terima rekomendasi, lalu pesan kunjungan bengkel dari satu tempat.
                        </p>
                        <x-button :href="route('register')" variant="brand" size="lg" class="shrink-0 self-start sm:self-auto">
                            Mulai sekarang
                            <x-heroicon-o-arrow-right class="size-4" />
                        </x-button>
                    </div>
                </div>

                <figure class="mt-12 overflow-hidden rounded-panel border border-line bg-surface sm:mt-16">
                    <img
                        src="{{ asset('images/workshop-service-hero.png') }}"
                        alt="Mekanik memeriksa mesin kendaraan di bengkel yang rapi"
                        width="1792"
                        height="896"
                        fetchpriority="high"
                        class="aspect-[2/1] w-full object-cover"
                    >
                    <figcaption class="flex flex-col gap-2 border-t border-line px-5 py-4 text-sm text-ink-muted sm:flex-row sm:items-center sm:justify-between">
                        <span>Keputusan servis berdasarkan catatan, bukan perkiraan.</span>
                        <span class="font-semibold text-ink">Jelas · Terencana · Tercatat</span>
                    </figcaption>
                </figure>
            </section>

            <section id="cara-kerja" class="border-y border-line bg-surface">
                <div class="mx-auto max-w-[76rem] px-5 py-16 sm:px-6 sm:py-20 lg:px-8">
                    <div class="grid gap-8 lg:grid-cols-[0.7fr_1.3fr] lg:gap-16">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand">Cara kerja</p>
                            <h2 class="mt-4 text-3xl font-semibold tracking-[-0.04em] text-ink sm:text-4xl">Tiga langkah menuju servis yang lebih tertib.</h2>
                        </div>
                        <ol class="border-t border-line">
                            @foreach ([
                                ['01', 'Catat kendaraan', 'Simpan identitas kendaraan dan posisi odometer awal.'],
                                ['02', 'Lihat rekomendasi', 'Sistem membantu membaca kebutuhan servis dari jarak dan waktu.'],
                                ['03', 'Pesan kunjungan', 'Pilih slot bengkel dan pantau statusnya hingga selesai.'],
                            ] as [$number, $title, $copy])
                                <li class="grid gap-3 border-b border-line py-6 sm:grid-cols-[4rem_12rem_1fr] sm:items-start">
                                    <span class="text-sm font-semibold text-brand">{{ $number }}</span>
                                    <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
                                    <p class="text-sm leading-6 text-ink-muted">{{ $copy }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </section>

            <section id="manfaat" class="mx-auto max-w-[76rem] px-5 py-16 sm:px-6 sm:py-20 lg:px-8 lg:py-24">
                <div class="grid gap-12 lg:grid-cols-2 lg:gap-20">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand">Manfaat utama</p>
                        <h2 class="mt-4 max-w-xl text-3xl font-semibold tracking-[-0.04em] text-ink sm:text-4xl">Riwayat yang rapi membuat keputusan berikutnya lebih mudah.</h2>
                        <p class="mt-5 max-w-xl text-base leading-7 text-ink-muted">Setiap pembaruan odometer, rekomendasi, booking, dan hasil servis berada dalam alur yang sama.</p>
                    </div>
                    <div class="grid gap-px overflow-hidden rounded-panel border border-line bg-line sm:grid-cols-2">
                        @foreach ([
                            ['heroicon-o-chart-bar', 'Pantauan berkala', 'Lihat perkembangan jarak tempuh tanpa catatan tercecer.'],
                            ['heroicon-o-bell-alert', 'Pengingat relevan', 'Terima informasi saat kendaraan mulai mendekati waktu servis.'],
                            ['heroicon-o-calendar-days', 'Booking terarah', 'Pilih kunjungan berdasarkan kebutuhan dan ketersediaan bengkel.'],
                            ['heroicon-o-document-check', 'Jejak yang utuh', 'Simpan hasil servis sebagai baseline perawatan berikutnya.'],
                        ] as [$icon, $title, $copy])
                            <article class="bg-surface p-6 sm:p-7">
                                <x-dynamic-component :component="$icon" class="size-6 text-brand" />
                                <h3 class="mt-5 text-base font-semibold text-ink">{{ $title }}</h3>
                                <p class="mt-2 text-sm leading-6 text-ink-muted">{{ $copy }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="bengkel" class="bg-action text-ink-inverse">
                <div class="mx-auto grid max-w-[76rem] gap-10 px-5 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1fr_1fr] lg:gap-20 lg:px-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-soft">Bengkel Anda</p>
                        <h2 class="mt-4 text-3xl font-semibold tracking-[-0.04em] sm:text-4xl">{{ config('workshop.name') }}</h2>
                        <p class="mt-5 max-w-lg text-base leading-7 text-white/65">Informasi operasional dikelola dari satu sumber konfigurasi dan siap disambungkan ke modul admin.</p>
                    </div>
                    <dl class="divide-y divide-white/15 border-y border-white/15">
                        <div class="grid gap-2 py-5 sm:grid-cols-[8rem_1fr]">
                            <dt class="text-sm text-white/55">Alamat</dt>
                            <dd class="text-sm font-medium">{{ config('workshop.address') }}</dd>
                        </div>
                        <div class="grid gap-2 py-5 sm:grid-cols-[8rem_1fr]">
                            <dt class="text-sm text-white/55">Telepon</dt>
                            <dd class="text-sm font-medium">{{ config('workshop.phone') }}</dd>
                        </div>
                        <div class="grid gap-2 py-5 sm:grid-cols-[8rem_1fr]">
                            <dt class="text-sm text-white/55">Jam layanan</dt>
                            <dd class="text-sm font-medium">{{ config('workshop.hours') }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section class="border-b border-line bg-brand-soft">
                <div class="mx-auto flex max-w-[76rem] flex-col gap-7 px-5 py-14 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand">Mulai dari satu kendaraan</p>
                        <h2 class="mt-3 text-2xl font-semibold tracking-[-0.035em] text-ink sm:text-3xl">Jadikan servis rutin lebih mudah diikuti.</h2>
                    </div>
                    <x-button :href="route('register')" size="lg">Buat akun customer</x-button>
                </div>
            </section>
        </main>

        <footer class="bg-surface">
            <div class="mx-auto flex max-w-[76rem] flex-col gap-5 px-5 py-8 text-sm text-ink-muted sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
                <x-brand-mark />
                <p>© {{ now()->year }} {{ config('workshop.name') }}. Perawatan kendaraan yang lebih tertib.</p>
            </div>
        </footer>
    </div>
</x-layouts.public>
