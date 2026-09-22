<x-layouts.app :title="$record->service_code">
    <x-page-header :title="$record->service_code" :description="$record->vehicle->name.' · '.$record->vehicle->plate_number">
        <x-slot:actions><x-button :href="route('service-history.index')" variant="secondary">Kembali ke Riwayat</x-button></x-slot:actions>
    </x-page-header>

    <section class="mt-8 rounded-panel border border-line bg-surface p-5 sm:p-7">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div><p class="text-sm text-ink-muted">Tanggal servis</p><p class="mt-1 font-semibold text-ink">{{ $record->service_date->translatedFormat('j F Y, H.i') }}</p></div>
            <div><p class="text-sm text-ink-muted">Jenis servis</p><p class="mt-1 font-semibold text-ink">{{ $record->service_type }}</p></div>
            <div><p class="text-sm text-ink-muted">Odometer</p><p class="mt-1 font-semibold tabular-nums text-ink">{{ number_format($record->odometer, 0, ',', '.') }} km</p></div>
            <div><p class="text-sm text-ink-muted">Total biaya</p><p class="mt-1 font-semibold tabular-nums text-ink">Rp{{ number_format((float) $record->total_cost, 0, ',', '.') }}</p></div>
        </div>
        @if ($record->booking)<p class="mt-6 border-t border-line pt-5 text-sm text-ink-muted">Referensi booking: <a class="font-semibold text-brand hover:text-brand-hover" href="{{ route('bookings.show', $record->booking) }}">{{ $record->booking->booking_code }}</a></p>@endif
    </section>

    <section class="mt-10 space-y-8">
        <div><x-section-header title="Keluhan" /><p class="mt-3 whitespace-pre-line text-sm leading-7 text-ink-muted">{{ $record->complaint ?: 'Tidak ada keluhan yang dicatat.' }}</p></div>
        <div class="border-t border-line pt-8"><x-section-header title="Pekerjaan yang dilakukan" /><p class="mt-3 whitespace-pre-line text-sm leading-7 text-ink">{{ $record->work_performed }}</p></div>
        <div class="border-t border-line pt-8"><x-section-header title="Catatan bengkel" /><p class="mt-3 whitespace-pre-line text-sm leading-7 text-ink-muted">{{ $record->notes ?: 'Tidak ada catatan tambahan.' }}</p></div>
    </section>

    @if ($record->vehicle->baseline_service_record_id === $record->id)
        <x-alert class="mt-10" type="success" title="Baseline siklus servis aktif">
            Servis ini menjadi titik awal rekomendasi berikutnya pada {{ number_format($record->odometer, 0, ',', '.') }} km.
        </x-alert>
    @endif
</x-layouts.app>
