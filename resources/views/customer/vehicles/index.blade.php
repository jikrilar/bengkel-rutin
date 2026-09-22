<x-layouts.app title="Kendaraan">
    <x-page-header title="Kendaraan" description="Catat kendaraan dan pantau kebutuhan servisnya dari satu daftar.">
        <x-slot:actions>
            <x-button :href="route('vehicles.create')">Tambah Kendaraan</x-button>
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
        <div class="mt-8 border-t border-line bg-surface">
            @foreach ($vehicles as $vehicle)
                @php $calculation = $vehicle->latestFuzzyCalculation; @endphp
                <article class="grid gap-5 border-b border-line px-4 py-5 sm:px-5 md:grid-cols-[minmax(0,1.2fr)_minmax(10rem,0.55fr)_auto] md:items-center">
                    <div class="min-w-0">
                        <h2 class="truncate text-lg font-semibold text-ink">{{ $vehicle->name }}</h2>
                        <p class="mt-1 text-sm text-ink-muted">{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->year }}</p>
                        <p class="mt-2 font-mono text-sm tracking-wide text-ink-muted">{{ $vehicle->plate_number }}</p>
                    </div>
                    <div>
                        <x-recommendation-status :status="$calculation?->final_status" />
                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-ink-muted">
                            <span class="tabular-nums">{{ number_format($vehicle->latestOdometer?->odometer ?? 0, 0, ',', '.') }} km</span>
                            @if ($calculation)
                                <span class="font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->score, 0) }} / 100</span>
                            @endif
                        </div>
                    </div>
                    <x-button :href="route('vehicles.show', $vehicle)" variant="secondary" class="w-full md:w-auto">Lihat Detail</x-button>
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $vehicles->links() }}</div>
    @endif
</x-layouts.app>
