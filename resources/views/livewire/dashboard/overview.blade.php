<div>
    <x-page-header
        title="Halo, {{ str(auth()->user()->name)->before(' ') }}"
        description="Lihat kondisi kendaraan dan langkah perawatan yang paling relevan hari ini."
    >
        <x-slot:actions>
            @if ($vehicles->isNotEmpty())
                <x-button :href="route('vehicles.create')" variant="secondary">Tambah Kendaraan</x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-flash />

    @if ($vehicles->isEmpty())
        <x-empty-state
            class="mt-8"
            icon="heroicon-o-truck"
            title="Belum ada kendaraan"
            description="Tambahkan kendaraan pertama untuk mulai mencatat odometer dan mendapatkan rekomendasi servis."
        >
            <x-slot:action>
                <x-button :href="route('vehicles.create')">Tambah Kendaraan</x-button>
            </x-slot:action>
        </x-empty-state>
    @else
        @php
            $calculation = $selectedVehicle->latestFuzzyCalculation;
            $baselineComplete = $selectedVehicle->baseline_service_date !== null && $selectedVehicle->baseline_odometer !== null;
        @endphp

        @if ($vehicles->count() > 1)
            <div class="mt-7 max-w-sm">
                <x-select name="vehicleId" label="Kendaraan aktif" wire:model.live="vehicleId">
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->name }} · {{ $vehicle->plate_number }}</option>
                    @endforeach
                </x-select>
            </div>
        @endif

        <section class="mt-8 overflow-hidden rounded-panel border border-line bg-surface">
            <div class="grid gap-0 lg:grid-cols-[minmax(0,1.35fr)_minmax(17rem,0.65fr)]">
                <div class="p-5 sm:p-7 lg:p-8">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm text-ink-muted">{{ $selectedVehicle->brand }} {{ $selectedVehicle->model }} · {{ $selectedVehicle->year }}</p>
                            <h2 class="mt-1 text-2xl font-semibold tracking-tight text-ink">{{ $selectedVehicle->name }}</h2>
                            <p class="mt-1 font-mono text-sm tracking-wide text-ink-muted">{{ $selectedVehicle->plate_number }}</p>
                        </div>
                        <x-recommendation-status :status="$calculation?->final_status" class="self-start" />
                    </div>

                    @if (! $baselineComplete)
                        <div class="mt-8 border-t border-line pt-6">
                            <h3 class="text-lg font-semibold text-ink">Baseline servis belum lengkap</h3>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-ink-muted">Kendaraan tersimpan dan odometer tetap dapat diperbarui. Rekomendasi baru tersedia setelah baseline servis diverifikasi.</p>
                            <div class="mt-5 flex flex-wrap gap-3">
                                <x-button :href="route('vehicles.show', $selectedVehicle)">Lihat Kendaraan</x-button>
                                <x-button :href="route('profile.edit')" variant="secondary">Periksa Kontak</x-button>
                            </div>
                        </div>
                    @elseif ($calculation === null)
                        <div class="mt-8 border-t border-line pt-6">
                            <h3 class="text-lg font-semibold text-ink">Rekomendasi belum tersedia</h3>
                            <p class="mt-2 text-sm leading-6 text-ink-muted">Data dasar sudah lengkap, tetapi kalkulasi belum ditemukan. Perbarui odometer untuk menghitung rekomendasi terbaru.</p>
                            <x-button class="mt-5" :href="route('vehicles.show', $selectedVehicle)">Update Odometer</x-button>
                        </div>
                    @else
                        <div class="mt-8 grid gap-6 border-t border-line pt-6 sm:grid-cols-[auto_1fr] sm:items-end">
                            <div>
                                <p class="text-sm font-medium text-ink-muted">Skor fuzzy</p>
                                <p class="mt-1 text-5xl font-semibold tracking-[-0.05em] text-ink"><span class="tabular-nums">{{ number_format((float) $calculation->score, 0) }}</span><span class="ml-1 text-lg font-medium text-ink-muted">/ 100</span></p>
                            </div>
                            <div class="sm:text-right">
                                <p class="text-sm text-ink-muted">Disarankan servis</p>
                                <p class="mt-1 text-xl font-semibold text-ink">{{ $calculation->recommended_date->translatedFormat('j F Y') }}</p>
                                @if ($calculation->recommended_from_date && $calculation->recommended_to_date)
                                    <p class="mt-1 text-sm text-ink-muted">Jendela {{ $calculation->recommended_from_date->translatedFormat('j M') }} - {{ $calculation->recommended_to_date->translatedFormat('j M Y') }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="mt-7 grid gap-5 border-t border-line pt-6 sm:grid-cols-3">
                            <div>
                                <p class="text-sm text-ink-muted">Odometer saat ini</p>
                                <p class="mt-1 text-lg font-semibold tabular-nums text-ink">{{ number_format($calculation->current_odometer, 0, ',', '.') }} km</p>
                            </div>
                            <div>
                                <p class="text-sm text-ink-muted">Penggunaan rata-rata</p>
                                <p class="mt-1 text-lg font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->average_daily_km, 1, ',', '.') }} km/hari</p>
                            </div>
                            <div>
                                <p class="text-sm text-ink-muted">Dihitung</p>
                                <p class="mt-1 text-lg font-semibold text-ink">{{ $calculation->calculated_at->translatedFormat('j M Y, H.i') }}</p>
                            </div>
                        </div>

                        <div class="mt-7 space-y-5 border-t border-line pt-6">
                            <x-progress-bar :value="$calculation->progress_km" label="Progress kilometer" />
                            <x-progress-bar :value="$calculation->progress_time" label="Progress waktu" />
                        </div>

                        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                            <x-button :href="route('bookings.create', ['vehicle' => $selectedVehicle->id, 'date' => $calculation->recommended_date->toDateString()])">Buat Jadwal Servis</x-button>
                            <x-button :href="route('recommendations.show', $selectedVehicle)" variant="secondary">Lihat Rekomendasi</x-button>
                        </div>
                    @endif
                </div>

                <aside class="border-t border-line bg-surface-subtle p-5 sm:p-7 lg:border-t-0 lg:border-l lg:p-8">
                    <h2 class="text-lg font-semibold text-ink">Booking aktif</h2>
                    @if ($selectedVehicle->activeBooking)
                        <div class="mt-5">
                            <x-status-badge :status="$selectedVehicle->activeBooking->status->tone()">{{ $selectedVehicle->activeBooking->status->label() }}</x-status-badge>
                            <p class="mt-4 text-xl font-semibold text-ink">{{ $selectedVehicle->activeBooking->scheduled_at->translatedFormat('j F Y') }}</p>
                            <p class="mt-1 text-sm text-ink-muted">Pukul {{ $selectedVehicle->activeBooking->scheduled_at->format('H.i') }}</p>
                            <x-button class="mt-5 w-full" :href="route('bookings.index')" variant="secondary">Lihat Booking</x-button>
                        </div>
                    @else
                        <p class="mt-3 text-sm leading-6 text-ink-muted">Belum ada jadwal servis aktif untuk kendaraan ini.</p>
                        <x-button class="mt-5 w-full" :href="route('bookings.index')" variant="secondary">Lihat Booking</x-button>
                    @endif
                </aside>
            </div>
        </section>
    @endif
</div>
