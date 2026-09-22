@props(['title', 'heading', 'description'])

<x-layouts.public :title="$title">
    <main class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1fr)_minmax(26rem,0.72fr)]">
        <section class="relative hidden overflow-hidden bg-action lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <a href="{{ route('home') }}" class="relative z-10 inline-flex w-fit items-center gap-3 text-ink-inverse">
                <span class="grid size-10 place-items-center rounded-field bg-brand">
                    <x-heroicon-o-wrench-screwdriver class="size-5" />
                </span>
                <span>
                    <span class="block text-sm font-semibold">Bengkel Rutin</span>
                    <span class="block text-xs text-white/60">Servis lebih terencana</span>
                </span>
            </a>

            <div class="relative z-10 max-w-xl">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-soft">Perawatan tanpa tebakan</p>
                <p class="mt-5 text-4xl font-semibold leading-tight tracking-[-0.04em] text-ink-inverse xl:text-5xl">
                    Jadwal servis yang jelas, sejak kilometer pertama.
                </p>
                <p class="mt-5 max-w-lg text-base leading-7 text-white/65">
                    Satu akun untuk memantau kendaraan, rekomendasi, dan kunjungan bengkel Anda.
                </p>
            </div>

            <p class="relative z-10 text-xs text-white/45">{{ config('workshop.name') }} · {{ now()->year }}</p>
        </section>

        <section class="flex min-h-screen flex-col bg-canvas px-5 py-6 sm:px-10 lg:px-12 xl:px-16">
            <div class="flex items-center justify-between lg:hidden">
                <a href="{{ route('home') }}" aria-label="Kembali ke beranda"><x-brand-mark /></a>
            </div>

            <div class="mx-auto flex w-full max-w-md flex-1 items-center py-10">
                <div class="w-full">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand">Akun customer</p>
                    <h1 class="mt-3 text-3xl font-semibold tracking-[-0.035em] text-ink">{{ $heading }}</h1>
                    <p class="mt-3 text-sm leading-6 text-ink-muted">{{ $description }}</p>

                    @if (session('status'))
                        <x-alert type="success" class="mt-6">{{ session('status') }}</x-alert>
                    @endif

                    <div class="mt-8">{{ $slot }}</div>
                </div>
            </div>
        </section>
    </main>
</x-layouts.public>
