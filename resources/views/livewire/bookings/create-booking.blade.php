<div>
    <x-page-header title="Buat jadwal servis" description="Pilih kendaraan, tanggal, dan slot yang masih tersedia. Kapasitas akan diperiksa ulang saat Anda mengirim booking.">
        <x-slot:actions><x-button :href="route('bookings.index')" variant="secondary">Kembali</x-button></x-slot:actions>
    </x-page-header>

    @if ($vehicles->isEmpty())
        <x-empty-state class="mt-8" icon="heroicon-o-truck" title="Tambahkan kendaraan dahulu" description="Booking perlu dihubungkan ke kendaraan milik Anda.">
            <x-slot:action><x-button :href="route('vehicles.create')">Tambah Kendaraan</x-button></x-slot:action>
        </x-empty-state>
    @else
        <form wire:submit="submit" class="mt-8 grid min-w-0 gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="min-w-0 space-y-8">
                <section class="rounded-panel border border-line bg-surface p-5 sm:p-7">
                    <div class="flex items-start gap-4">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-soft text-sm font-semibold text-brand">1</span>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-semibold text-ink">Kendaraan</h2>
                            <label for="vehicleId" class="mt-5 block text-sm font-semibold text-ink">Pilih kendaraan</label>
                            <select id="vehicleId" wire:model.live="vehicleId" @error('vehicleId') aria-invalid="true" aria-describedby="vehicleId-error" @enderror class="mt-2 min-h-11 w-full rounded-field border border-line-strong bg-surface px-3.5 text-sm focus:border-brand focus:ring-2 focus:ring-brand-soft">
                                <option value="">Pilih kendaraan</option>
                                @foreach ($vehicles as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }} — {{ $option->plate_number }}</option>
                                @endforeach
                            </select>
                            @error('vehicleId') <p id="vehicleId-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded-panel border border-line bg-surface p-5 sm:p-7">
                    <div class="flex items-start gap-4">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-soft text-sm font-semibold text-brand">2</span>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-semibold text-ink">Tanggal dan slot</h2>
                            <label for="booking-date" class="mt-5 block text-sm font-semibold text-ink">Tanggal kunjungan</label>
                            <input id="booking-date" type="date" wire:model.live="date" min="{{ now()->toDateString() }}" @error('date') aria-invalid="true" aria-describedby="booking-date-error" @enderror class="mt-2 min-h-11 w-full rounded-field border border-line-strong bg-surface px-3.5 text-sm sm:max-w-xs">
                            @error('date') <p id="booking-date-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror

                            @if ($calculation)
                                <p class="mt-3 flex items-start gap-2 text-sm text-ink-muted">
                                    <x-heroicon-o-calendar-days class="mt-0.5 size-4 shrink-0 text-brand" />
                                    Rekomendasi: <strong class="text-ink">{{ $calculation->recommended_date->translatedFormat('j F Y') }}</strong>
                                </p>
                            @endif

                            @if ($outsideRecommendedWindow)
                                <x-alert class="mt-4" type="warning" title="Di luar jendela rekomendasi">
                                    Tanggal ini tetap dapat dipilih. Pertimbangkan jadwal {{ $calculation->recommended_from_date->translatedFormat('j F Y') }}@if ($calculation->recommended_to_date)–{{ $calculation->recommended_to_date->translatedFormat('j F Y') }}@endif agar sesuai rekomendasi kendaraan.
                                </x-alert>
                            @endif

                            <div class="mt-6" wire:loading.class="opacity-60" wire:target="date,vehicleId">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <h3 class="text-sm font-semibold text-ink">Slot tersedia</h3>
                                    <p class="text-xs text-ink-muted">Durasi {{ $settings->slot_duration_minutes }} menit · kapasitas {{ $settings->slot_capacity }}</p>
                                </div>

                                @if (! $resolvedSchedule?->isOpen)
                                    <div class="mt-3 rounded-field border border-line bg-surface-muted p-4 text-sm text-ink-muted">
                                        <strong class="block text-ink">Bengkel tutup</strong>
                                        {{ $resolvedSchedule?->reason ?? 'Tidak ada jam operasional pada tanggal ini.' }}
                                    </div>
                                @elseif (empty($slots))
                                    <p class="mt-3 text-sm text-ink-muted">Tidak ada slot yang dapat dipilih pada tanggal ini.</p>
                                @else
                                    <fieldset class="mt-3" @error('time') aria-describedby="booking-time-error" @enderror>
                                        <legend class="sr-only">Pilih waktu booking</legend>
                                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4">
                                            @foreach ($slots as $slot)
                                                @php
                                                    $value = $slot->startsAt->format('H:i');
                                                    $recommended = $calculation && $date === $calculation->recommended_date->toDateString();
                                                @endphp
                                                <label @class([
                                                    'relative flex min-h-16 cursor-pointer flex-col justify-center rounded-field border px-3 py-2 text-center transition focus-within:ring-2 focus-within:ring-brand',
                                                    'border-brand bg-brand-soft text-brand' => $time === $value,
                                                    'border-warning/40 bg-warning-soft text-warning-ink' => $time !== $value && $slot->state === 'limited',
                                                    'border-line bg-surface hover:border-line-emphasis' => $time !== $value && $slot->state === 'available',
                                                    'cursor-not-allowed border-line bg-surface-muted text-ink-subtle' => ! $slot->isAvailable,
                                                ])>
                                                    <input class="sr-only" type="radio" wire:model.live="time" value="{{ $value }}" @disabled(! $slot->isAvailable)>
                                                    <span class="font-semibold tabular-nums">{{ $slot->label() }}</span>
                                                    <span class="mt-0.5 text-[0.68rem] font-medium">{{ $slot->stateLabel() }}@if ($slot->isAvailable) · {{ $slot->remainingCapacity }} slot @endif</span>
                                                    @if ($recommended)<span class="mt-1 text-[0.65rem] font-semibold uppercase tracking-wide">Tanggal rekomendasi</span>@endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endif
                                @error('time') <p id="booking-time-error" class="mt-2 text-sm text-danger">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-panel border border-line bg-surface p-5 sm:p-7">
                    <div class="flex items-start gap-4">
                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-brand-soft text-sm font-semibold text-brand">3</span>
                        <div class="min-w-0 flex-1">
                            <h2 class="text-lg font-semibold text-ink">Keluhan atau catatan</h2>
                            <label for="complaint" class="mt-5 block text-sm font-semibold text-ink">Catatan opsional</label>
                            <textarea id="complaint" wire:model="complaint" rows="4" maxlength="2000" placeholder="Contoh: terdengar bunyi saat pengereman" @error('complaint') aria-invalid="true" aria-describedby="complaint-error" @enderror class="mt-2 w-full rounded-field border border-line-strong bg-surface px-3.5 py-3 text-sm focus:border-brand focus:ring-2 focus:ring-brand-soft"></textarea>
                            @error('complaint') <p id="complaint-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>
            </div>

            <aside class="h-fit rounded-panel border border-line bg-surface p-5 lg:sticky lg:top-24">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-ink-muted">Konfirmasi booking</p>
                <dl class="mt-5 space-y-4 text-sm">
                    <div><dt class="text-ink-muted">Kendaraan</dt><dd class="mt-1 font-semibold text-ink">{{ $vehicle?->name ?? 'Belum dipilih' }}</dd></div>
                    <div><dt class="text-ink-muted">Tanggal</dt><dd class="mt-1 font-semibold text-ink">{{ $selectedDate?->translatedFormat('j F Y') ?? 'Belum dipilih' }}</dd></div>
                    <div><dt class="text-ink-muted">Waktu</dt><dd class="mt-1 font-semibold text-ink">{{ $time ? str_replace(':', '.', $time) : 'Belum dipilih' }}</dd></div>
                </dl>
                <x-button type="submit" class="mt-6 w-full" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">Konfirmasi Booking</span>
                    <span wire:loading wire:target="submit">Memeriksa slot…</span>
                </x-button>
                <p class="mt-3 text-xs leading-5 text-ink-muted">Booking berstatus menunggu hingga dikonfirmasi bengkel.</p>
            </aside>
        </form>
    @endif
</div>
