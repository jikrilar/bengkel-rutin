<x-layouts.app :title="'Rekomendasi '.$vehicle->name">
    <x-page-header :title="'Rekomendasi '.$vehicle->name" :description="$vehicle->brand.' '.$vehicle->model.' · '.$vehicle->plate_number">
        <x-slot:actions>
            <x-button :href="route('vehicles.show', $vehicle)" variant="secondary">Lihat Kendaraan</x-button>
        </x-slot:actions>
    </x-page-header>

    @if ($calculation === null)
        <x-empty-state
            class="mt-8"
            icon="heroicon-o-wrench-screwdriver"
            title="Rekomendasi Belum Tersedia"
            description="Baseline servis belum lengkap atau kalkulasi pertama belum dibuat."
        >
            <x-slot:action><x-button :href="route('vehicles.show', $vehicle)">Periksa Kendaraan</x-button></x-slot:action>
        </x-empty-state>
    @else
        <section class="mt-8 rounded-panel border border-line bg-surface p-5 sm:p-7">
            <div class="grid gap-7 md:grid-cols-[minmax(0,0.75fr)_minmax(15rem,1.25fr)] md:items-end">
                <div>
                    <x-recommendation-status :status="$calculation->final_status" />
                    <p class="mt-5 text-6xl font-semibold tracking-[-0.06em] text-ink"><span class="tabular-nums">{{ number_format((float) $calculation->score, 0) }}</span><span class="ml-2 text-lg tracking-normal text-ink-muted">/ 100</span></p>
                    <p class="mt-3 max-w-sm text-sm leading-6 text-ink-muted">{{ $calculation->final_status->explanation() }}</p>
                </div>
                <div class="border-t border-line pt-6 md:border-t-0 md:border-l md:pt-0 md:pl-7">
                    <p class="text-sm text-ink-muted">Tanggal servis yang disarankan</p>
                    <p class="mt-1 text-2xl font-semibold text-ink">{{ $calculation->recommended_date->translatedFormat('j F Y') }}</p>
                    <p class="mt-4 text-sm text-ink-muted">Jendela servis</p>
                    <p class="mt-1 font-semibold text-ink">
                        @if ($calculation->recommended_to_date)
                            {{ $calculation->recommended_from_date->translatedFormat('j F Y') }} - {{ $calculation->recommended_to_date->translatedFormat('j F Y') }}
                        @else
                            Mulai {{ $calculation->recommended_from_date->translatedFormat('j F Y') }}
                        @endif
                    </p>
                    <x-button class="mt-6" :href="route('bookings.index')">Buat Jadwal Servis</x-button>
                </div>
            </div>
        </section>

        @if ($calculation->guard_applied)
            <x-alert type="warning" class="mt-6" title="Status dinaikkan oleh proyeksi jatuh tempo">
                Proyeksi tanggal servis menunjukkan kebutuhan yang lebih mendesak daripada skor fuzzy. Skor fuzzy tetap {{ number_format((float) $calculation->score, 0) }} / 100 dan tidak diubah.
            </x-alert>
        @endif

        <section class="mt-10">
            <x-section-header title="Dasar rekomendasi" description="Ringkasan ini memakai snapshot kalkulasi yang tersimpan pada {{ $calculation->calculated_at->translatedFormat('j F Y, H.i') }}." />
            <div class="mt-6 grid gap-8 md:grid-cols-2">
                <div class="space-y-6">
                    <x-progress-bar :value="$calculation->progress_km" label="Progress kilometer" />
                    <x-progress-bar :value="$calculation->progress_time" label="Progress waktu" />
                </div>
                <dl class="grid gap-x-6 gap-y-5 sm:grid-cols-2">
                    <div><dt class="text-sm text-ink-muted">Penggunaan rata-rata</dt><dd class="mt-1 text-lg font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->average_daily_km, 1, ',', '.') }} km/hari</dd></div>
                    <div><dt class="text-sm text-ink-muted">Intensitas penggunaan</dt><dd class="mt-1 text-lg font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->usage_intensity, 0, ',', '.') }}%</dd></div>
                    <div><dt class="text-sm text-ink-muted">Jatuh tempo kilometer</dt><dd class="mt-1 font-semibold text-ink">{{ $calculation->estimated_due_by_km?->translatedFormat('j F Y') ?? 'Tidak terproyeksi' }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Jatuh tempo waktu</dt><dd class="mt-1 font-semibold text-ink">{{ $calculation->estimated_due_by_time->translatedFormat('j F Y') }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="mt-10 border-t border-line pt-8">
            <details class="group">
                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-4 font-semibold text-ink focus-visible:outline-none">
                    <span>Lihat perhitungan teknis</span>
                    <x-heroicon-o-chevron-down class="size-5 shrink-0 transition-transform group-open:rotate-180" />
                </summary>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-ink-muted">Lihat input, derajat keanggotaan, rule aktif, dan formula defuzzifikasi yang tersimpan.</p>
                <x-button class="mt-5" :href="route('recommendations.calculation.show', [$vehicle, $calculation])" variant="secondary">Buka Detail Fuzzy</x-button>
            </details>
        </section>
    @endif
</x-layouts.app>
