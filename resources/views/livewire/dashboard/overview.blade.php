<div>
    <x-page-header
        eyebrow="Ringkasan Hari Ini"
        title="Halo, {{ str(auth()->user()->name)->before(' ') }}"
        description="Fondasi akun Anda sudah siap. Mulai dari kendaraan untuk membangun jadwal servis yang terukur."
    >
        <x-slot:actions>
            <x-status-badge status="success">Akun aktif</x-status-badge>
        </x-slot:actions>
    </x-page-header>

    <section class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(18rem,0.7fr)]">
        <div>
            <x-section-header title="Kendaraan dan rekomendasi" description="Ringkasan kendaraan akan muncul setelah data awal ditambahkan." />
            <x-empty-state
                class="mt-5"
                icon="heroicon-o-truck"
                title="Garasi Anda masih kosong"
                description="Fondasi UI siap. Data kendaraan sengaja belum dibuat sampai task domain T03 dimulai."
            />
        </div>

        <aside class="border-l border-line pl-0 lg:pl-7">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand">Alur perawatan</p>
            <ol class="mt-5 space-y-5">
                @foreach ([
                    ['Kendaraan', 'Simpan identitas dan interval servis.'],
                    ['Rekomendasi', 'Pantau jarak tempuh dan waktu.'],
                    ['Booking', 'Pilih jadwal kunjungan bengkel.'],
                ] as $index => [$label, $copy])
                    <li class="flex gap-4">
                        <span class="grid size-7 shrink-0 place-items-center rounded-full border border-line-strong bg-surface text-xs font-semibold text-ink">{{ $index + 1 }}</span>
                        <div>
                            <p class="text-sm font-semibold text-ink">{{ $label }}</p>
                            <p class="mt-1 text-sm leading-5 text-ink-muted">{{ $copy }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </aside>
    </section>
</div>
