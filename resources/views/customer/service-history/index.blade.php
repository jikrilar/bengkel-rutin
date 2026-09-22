<x-layouts.app title="Riwayat Servis">
    <x-page-header title="Riwayat servis" description="Catatan pekerjaan bengkel yang telah selesai untuk seluruh kendaraan Anda." />

    @if ($records->isEmpty())
        <x-empty-state class="mt-8" icon="heroicon-o-clipboard-document-list" title="Belum ada riwayat servis" description="Catatan akan muncul setelah servis kendaraan diselesaikan oleh bengkel." />
    @else
        <div class="mt-8 overflow-hidden rounded-panel border border-line bg-surface">
            <div class="hidden grid-cols-[8rem_minmax(0,1fr)_9rem_10rem_9rem_1.5rem] gap-4 border-b border-line bg-surface-subtle px-5 py-3 text-xs font-semibold uppercase tracking-wide text-ink-subtle md:grid">
                <span>Tanggal</span><span>Kendaraan</span><span>Odometer</span><span>Jenis servis</span><span class="text-right">Biaya</span><span></span>
            </div>
            <div class="divide-y divide-line">
                @foreach ($records as $record)
                    <a href="{{ route('service-history.show', $record) }}" class="grid min-w-0 gap-3 p-4 transition-colors hover:bg-surface-subtle sm:p-5 md:grid-cols-[8rem_minmax(0,1fr)_9rem_10rem_9rem_1.5rem] md:items-center md:gap-4">
                        <div><span class="text-xs text-ink-subtle md:hidden">Tanggal</span><p class="font-semibold text-ink">{{ $record->service_date->translatedFormat('j M Y') }}</p></div>
                        <div class="min-w-0"><span class="text-xs text-ink-subtle md:hidden">Kendaraan</span><p class="truncate font-semibold text-ink">{{ $record->vehicle->name }}</p><p class="truncate text-sm text-ink-muted">{{ $record->vehicle->plate_number }} · {{ $record->service_code }}</p></div>
                        <div><span class="text-xs text-ink-subtle md:hidden">Odometer</span><p class="tabular-nums text-ink">{{ number_format($record->odometer, 0, ',', '.') }} km</p></div>
                        <div><span class="text-xs text-ink-subtle md:hidden">Jenis</span><p class="text-ink">{{ $record->service_type }}</p></div>
                        <div class="md:text-right"><span class="text-xs text-ink-subtle md:hidden">Biaya</span><p class="font-semibold tabular-nums text-ink">Rp{{ number_format((float) $record->total_cost, 0, ',', '.') }}</p></div>
                        <x-heroicon-o-chevron-right class="hidden size-5 text-ink-subtle md:block" />
                    </a>
                @endforeach
            </div>
        </div>
        <div class="mt-6">{{ $records->links() }}</div>
    @endif
</x-layouts.app>
