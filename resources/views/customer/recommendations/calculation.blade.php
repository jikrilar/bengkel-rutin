<x-layouts.app :title="'Perhitungan '.$vehicle->name">
    @php
        $stateLabels = ['safe' => 'Aman', 'approaching' => 'Mendekati', 'critical' => 'Kritis', 'normal' => 'Normal', 'intensive' => 'Intensif'];
        $consequentLabels = ['not_urgent' => 'Tidak mendesak', 'urgent' => 'Mendesak'];
        $alphaSum = (float) $calculation->ruleResults->sum(fn ($result) => (float) $result->alpha);
        $weightedSum = (float) $calculation->ruleResults->sum(fn ($result) => (float) $result->weighted_value);
    @endphp

    <x-page-header title="Detail Perhitungan Fuzzy" description="Snapshot teknis untuk {{ $vehicle->name }}, dihitung {{ $calculation->calculated_at->translatedFormat('j F Y, H.i') }}.">
        <x-slot:actions>
            <x-button :href="route('recommendations.show', $vehicle)" variant="secondary">Kembali</x-button>
        </x-slot:actions>
    </x-page-header>

    <section class="mt-8 rounded-panel border border-line bg-surface p-5 sm:p-7">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <x-recommendation-status :status="$calculation->final_status" />
                <p class="mt-4 text-5xl font-semibold tracking-tight text-ink"><span class="tabular-nums">{{ number_format((float) $calculation->score, 2, ',', '.') }}</span><span class="ml-1 text-base text-ink-muted">/ 100</span></p>
            </div>
            <dl class="grid gap-x-8 gap-y-3 text-sm sm:grid-cols-2">
                <div><dt class="text-ink-muted">Status fuzzy</dt><dd class="mt-1 font-semibold text-ink">{{ $calculation->fuzzy_status->label() }}</dd></div>
                <div><dt class="text-ink-muted">Konfigurasi</dt><dd class="mt-1 font-semibold text-ink">Versi {{ $calculation->fuzzyConfig->version }}</dd></div>
            </dl>
        </div>
    </section>

    <section class="mt-10">
        <x-section-header title="Input dan profil servis" description="Semua nilai berasal dari snapshot historis, bukan kalkulasi ulang halaman ini." />
        <dl class="mt-6 grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-sm text-ink-muted">Odometer saat ini</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ number_format($calculation->current_odometer, 0, ',', '.') }} km</dd></div>
            <div><dt class="text-sm text-ink-muted">Baseline odometer</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ number_format($calculation->baseline_odometer, 0, ',', '.') }} km</dd></div>
            <div><dt class="text-sm text-ink-muted">Baseline tanggal</dt><dd class="mt-1 font-semibold text-ink">{{ $calculation->baseline_service_date->translatedFormat('j F Y') }}</dd></div>
            <div><dt class="text-sm text-ink-muted">Interval servis</dt><dd class="mt-1 font-semibold text-ink">{{ number_format($calculation->interval_km_snapshot, 0, ',', '.') }} km / {{ $calculation->interval_days_snapshot }} hari</dd></div>
            <div><dt class="text-sm text-ink-muted">Sejak servis</dt><dd class="mt-1 font-semibold text-ink">{{ number_format($calculation->km_since_service, 0, ',', '.') }} km / {{ $calculation->days_since_service }} hari</dd></div>
            <div><dt class="text-sm text-ink-muted">Progress kilometer</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->progress_km, 2, ',', '.') }}%</dd></div>
            <div><dt class="text-sm text-ink-muted">Progress waktu</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->progress_time, 2, ',', '.') }}%</dd></div>
            <div><dt class="text-sm text-ink-muted">Penggunaan</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->average_daily_km, 2, ',', '.') }} km/hari ({{ number_format((float) $calculation->usage_intensity, 2, ',', '.') }}%)</dd></div>
        </dl>
    </section>

    <section class="mt-10 border-t border-line pt-9">
        <x-section-header title="Derajat keanggotaan" />
        <div class="mt-6 grid gap-6 md:grid-cols-3">
            <div>
                <h3 class="font-semibold text-ink">Progress kilometer</h3>
                <dl class="mt-3 space-y-2 text-sm"><div class="flex justify-between gap-4"><dt class="text-ink-muted">Aman</dt><dd class="font-mono text-ink">{{ $calculation->km_safe_mu }}</dd></div><div class="flex justify-between gap-4"><dt class="text-ink-muted">Mendekati</dt><dd class="font-mono text-ink">{{ $calculation->km_approaching_mu }}</dd></div><div class="flex justify-between gap-4"><dt class="text-ink-muted">Kritis</dt><dd class="font-mono text-ink">{{ $calculation->km_critical_mu }}</dd></div></dl>
            </div>
            <div>
                <h3 class="font-semibold text-ink">Progress waktu</h3>
                <dl class="mt-3 space-y-2 text-sm"><div class="flex justify-between gap-4"><dt class="text-ink-muted">Aman</dt><dd class="font-mono text-ink">{{ $calculation->time_safe_mu }}</dd></div><div class="flex justify-between gap-4"><dt class="text-ink-muted">Mendekati</dt><dd class="font-mono text-ink">{{ $calculation->time_approaching_mu }}</dd></div><div class="flex justify-between gap-4"><dt class="text-ink-muted">Kritis</dt><dd class="font-mono text-ink">{{ $calculation->time_critical_mu }}</dd></div></dl>
            </div>
            <div>
                <h3 class="font-semibold text-ink">Intensitas penggunaan</h3>
                <dl class="mt-3 space-y-2 text-sm"><div class="flex justify-between gap-4"><dt class="text-ink-muted">Normal</dt><dd class="font-mono text-ink">{{ $calculation->usage_normal_mu }}</dd></div><div class="flex justify-between gap-4"><dt class="text-ink-muted">Intensif</dt><dd class="font-mono text-ink">{{ $calculation->usage_intensive_mu }}</dd></div></dl>
            </div>
        </div>
    </section>

    <section class="mt-10 border-t border-line pt-9">
        <x-section-header title="Rule aktif" description="Hanya rule dengan alpha lebih besar dari nol yang disimpan oleh RecommendationService." />
        <div class="mt-6 space-y-3">
            @foreach ($calculation->ruleResults as $result)
                <article class="grid gap-4 rounded-field border border-line bg-surface px-4 py-4 md:grid-cols-[5rem_minmax(0,1fr)_repeat(3,minmax(6.5rem,auto))] md:items-center">
                    <p class="font-mono text-sm font-semibold text-brand">{{ $result->rule->code }}</p>
                    <p class="text-sm leading-6 text-ink">KM {{ $stateLabels[$result->rule->km_state] }}, waktu {{ $stateLabels[$result->rule->time_state] }}, penggunaan {{ $stateLabels[$result->rule->usage_state] }} menghasilkan {{ $consequentLabels[$result->rule->consequent] }}.</p>
                    <dl class="text-sm"><dt class="text-ink-muted">Alpha</dt><dd class="mt-1 font-mono font-semibold text-ink">{{ $result->alpha }}</dd></dl>
                    <dl class="text-sm"><dt class="text-ink-muted">z</dt><dd class="mt-1 font-mono font-semibold text-ink">{{ $result->z_value }}</dd></dl>
                    <dl class="text-sm"><dt class="text-ink-muted">Alpha × z</dt><dd class="mt-1 font-mono font-semibold text-ink">{{ $result->weighted_value }}</dd></dl>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mt-10 border-t border-line pt-9">
        <x-section-header title="Defuzzifikasi" />
        <div class="mt-5 rounded-panel border border-line bg-surface p-5 sm:p-6">
            <p class="font-mono text-sm leading-7 text-ink">Z = Σ(alpha × z) / Σalpha</p>
            <p class="mt-2 font-mono text-sm leading-7 text-ink">Z = {{ number_format($weightedSum, 4, ',', '.') }} / {{ number_format($alphaSum, 4, ',', '.') }} = {{ number_format((float) $calculation->score, 2, ',', '.') }}</p>
        </div>
    </section>

    <section class="mt-10 border-t border-line pt-9">
        <x-section-header title="Hasil akhir" />
        <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-4">
            <div><dt class="text-sm text-ink-muted">Skor fuzzy</dt><dd class="mt-1 font-semibold text-ink">{{ number_format((float) $calculation->score, 2, ',', '.') }} / 100</dd></div>
            <div><dt class="text-sm text-ink-muted">Status fuzzy</dt><dd class="mt-1 font-semibold text-ink">{{ $calculation->fuzzy_status->label() }}</dd></div>
            <div><dt class="text-sm text-ink-muted">Rekomendasi final</dt><dd class="mt-1 font-semibold text-ink">{{ $calculation->final_status->label() }}</dd></div>
            <div><dt class="text-sm text-ink-muted">Guard</dt><dd class="mt-1 font-semibold text-ink">{{ $calculation->guard_applied ? 'Diterapkan' : 'Tidak diterapkan' }}</dd></div>
        </dl>
        @if ($calculation->guard_applied)
            <x-alert type="warning" class="mt-5" title="Alasan guard">
                {{ $calculation->guard_reason }} Skor fuzzy tetap tersimpan tanpa perubahan.
            </x-alert>
        @endif
    </section>
</x-layouts.app>
