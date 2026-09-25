<x-layouts.app :title="$vehicle->name">
    @php
        $calculation = $vehicle->latestFuzzyCalculation;
        $baselineComplete = $vehicle->baseline_service_date !== null && $vehicle->baseline_odometer !== null;
    @endphp

    <x-page-header :title="$vehicle->name" :description="$vehicle->brand.' '.$vehicle->model.' · '.$vehicle->year">
        <x-slot:actions>
            <x-button :href="route('vehicles.edit', $vehicle)" variant="secondary">Edit Identitas</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    <div class="mt-8 grid gap-10 lg:grid-cols-[minmax(0,1.4fr)_minmax(17rem,0.6fr)]">
        <div class="min-w-0 space-y-10">
            <section>
                <x-section-header title="Identitas" />
                <dl class="mt-5 grid gap-x-8 gap-y-5 border-t border-line pt-5 sm:grid-cols-2">
                    <div><dt class="text-sm text-ink-muted">Nomor polisi</dt><dd class="mt-1 font-mono text-base font-semibold tracking-wide text-ink">{{ $vehicle->plate_number }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Kendaraan</dt><dd class="mt-1 font-semibold text-ink">{{ $vehicle->brand }} {{ $vehicle->model }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Tahun</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ $vehicle->year }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Odometer saat ini</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ number_format($vehicle->latestOdometer?->odometer ?? 0, 0, ',', '.') }} km</dd></div>
                </dl>
            </section>

            <section class="border-t border-line pt-9">
                <x-section-header title="Profil dan baseline servis" description="Baseline adalah acuan resmi untuk seluruh perhitungan rekomendasi." />
                <dl class="mt-5 grid gap-x-8 gap-y-5 sm:grid-cols-2">
                    <div><dt class="text-sm text-ink-muted">Profil servis</dt><dd class="mt-1 font-semibold text-ink">{{ $vehicle->serviceProfile->name }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Interval</dt><dd class="mt-1 font-semibold text-ink">{{ number_format($vehicle->serviceProfile->interval_km, 0, ',', '.') }} km atau {{ $vehicle->serviceProfile->interval_days }} hari</dd></div>
                    <div><dt class="text-sm text-ink-muted">Tanggal servis terakhir</dt><dd class="mt-1 font-semibold text-ink">{{ $vehicle->baseline_service_date?->translatedFormat('j F Y') ?? 'Belum tersedia' }}</dd></div>
                    <div><dt class="text-sm text-ink-muted">Odometer baseline</dt><dd class="mt-1 font-semibold tabular-nums text-ink">{{ $vehicle->baseline_odometer === null ? 'Belum tersedia' : number_format($vehicle->baseline_odometer, 0, ',', '.').' km' }}</dd></div>
                </dl>
                @if (! $baselineComplete)
                    <x-alert type="warning" class="mt-5" title="Rekomendasi Belum Tersedia">
                        Baseline servis belum lengkap. Hubungi bengkel untuk memverifikasi data sebelum rekomendasi dapat dihitung.
                    </x-alert>
                @endif
            </section>

            <section class="border-t border-line pt-9">
                <x-section-header title="Rekomendasi saat ini">
                    <x-slot:actions>
                        @if ($calculation)
                            <a href="{{ route('recommendations.show', $vehicle) }}" class="text-sm font-semibold text-brand hover:text-brand-hover">Lihat detail</a>
                        @endif
                    </x-slot:actions>
                </x-section-header>

                @if ($calculation)
                    <div class="mt-5 rounded-panel border border-line bg-surface p-5 sm:p-6">
                        <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <x-recommendation-status :status="$calculation->final_status" />
                                <p class="mt-4 text-4xl font-semibold tracking-tight text-ink"><span class="tabular-nums">{{ number_format((float) $calculation->score, 0) }}</span><span class="ml-1 text-base text-ink-muted">/ 100</span></p>
                            </div>
                            <div class="sm:text-right">
                                <p class="text-sm text-ink-muted">Disarankan servis</p>
                                <p class="mt-1 text-lg font-semibold text-ink">{{ $calculation->recommended_date->translatedFormat('j F Y') }}</p>
                            </div>
                        </div>
                        <div class="mt-6 grid gap-5 border-t border-line pt-5 sm:grid-cols-2">
                            <x-progress-bar :value="$calculation->progress_km" label="Progress kilometer" />
                            <x-progress-bar :value="$calculation->progress_time" label="Progress waktu" />
                        </div>
                    </div>
                @else
                    <x-empty-state class="mt-5" icon="heroicon-o-wrench-screwdriver" title="Rekomendasi belum tersedia" description="Lengkapi baseline atau perbarui odometer untuk mendapatkan kalkulasi terbaru." />
                @endif
            </section>

            <section id="odometer-section" class="scroll-mt-24 border-t border-line pt-9">
                <x-section-header title="Update odometer" description="Setiap pembaruan membuat catatan baru dan menghitung ulang rekomendasi jika baseline lengkap." />

                <form method="POST" action="{{ route('vehicles.odometer.store', $vehicle) }}" class="mt-5 grid gap-5 rounded-panel border border-line bg-surface p-5 sm:grid-cols-2 sm:p-6">
                    @csrf
                    <div class="sm:col-span-2">
                        <p class="text-sm text-ink-muted">Odometer terakhir</p>
                        <p class="mt-1 text-2xl font-semibold tabular-nums text-ink">{{ number_format($vehicle->latestOdometer?->odometer ?? 0, 0, ',', '.') }} km</p>
                    </div>
                    <x-input name="odometer" label="Odometer baru" type="number" :min="$vehicle->latestOdometer?->odometer ?? 0" inputmode="numeric" required hint="Nilai tidak boleh lebih kecil dari catatan terakhir." />
                    <x-input name="recorded_at" label="Waktu pencatatan" type="datetime-local" step="1" :value="now()->format('Y-m-d\TH:i:s')" :min="$vehicle->latestOdometer?->recorded_at?->format('Y-m-d\TH:i:s')" :max="now()->format('Y-m-d\TH:i:s')" required />
                    <div class="sm:col-span-2 sm:text-right">
                        <x-button type="submit">Update Odometer</x-button>
                    </div>
                </form>
            </section>

            <section class="border-t border-line pt-9">
                <x-section-header title="Riwayat odometer" description="Catatan terbaru ditampilkan lebih dahulu." />
                <div class="mt-5 border-t border-line bg-surface">
                    @foreach ($odometerRows as $log)
                        <div class="grid gap-3 border-b border-line px-4 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center sm:px-5">
                            <div>
                                <p class="font-medium text-ink">{{ $log->recorded_at->translatedFormat('j F Y, H.i') }}</p>
                                <p class="mt-1 text-sm text-ink-muted">{{ $log->source->label() }}</p>
                            </div>
                            <div class="sm:text-right">
                                <p class="font-semibold tabular-nums text-ink">{{ number_format($log->odometer, 0, ',', '.') }} km</p>
                                @if ($log->display_delta !== null)
                                    <p class="mt-1 text-sm tabular-nums text-ink-muted">+{{ number_format($log->display_delta, 0, ',', '.') }} km</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="border-t border-line pt-9">
                <x-section-header title="Riwayat servis" description="Lima catatan servis terbaru kendaraan." />
                @if ($vehicle->serviceRecords->isEmpty())
                    <p class="mt-5 border-y border-line bg-surface-subtle px-5 py-6 text-sm text-ink-muted">Belum ada riwayat servis resmi.</p>
                @else
                    <div class="mt-5 border-t border-line bg-surface">
                        @foreach ($vehicle->serviceRecords as $record)
                            <div class="grid gap-2 border-b border-line px-4 py-4 sm:grid-cols-[1fr_auto] sm:items-center sm:px-5">
                                <div><p class="font-semibold text-ink">{{ $record->service_type }}</p><p class="mt-1 text-sm text-ink-muted">{{ $record->service_date->translatedFormat('j F Y') }}</p></div>
                                <p class="font-semibold tabular-nums text-ink">{{ number_format($record->odometer, 0, ',', '.') }} km</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="lg:border-l lg:border-line lg:pl-8">
            <div class="lg:sticky lg:top-24">
                <h2 class="text-lg font-semibold text-ink">Tindakan utama</h2>
                <div class="mt-4 flex flex-col gap-3">
                    <x-button href="#odometer-section">Update Odometer</x-button>
                    @if ($calculation)
                        <x-button :href="route('recommendations.show', $vehicle)" variant="secondary">Lihat Rekomendasi</x-button>
                    @endif
                    <x-button :href="route('bookings.index')" variant="secondary">Lihat Booking</x-button>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.app>
