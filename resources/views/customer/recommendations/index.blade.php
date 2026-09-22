<x-layouts.app title="Rekomendasi Servis">
    <x-page-header title="Rekomendasi Servis" description="Bandingkan kebutuhan servis seluruh kendaraan berdasarkan kalkulasi terakhir." />

    @if ($vehicles->isEmpty())
        <x-empty-state
            class="mt-8"
            icon="heroicon-o-wrench-screwdriver"
            title="Belum ada rekomendasi"
            description="Tambahkan kendaraan untuk mulai mendapatkan rekomendasi servis."
        >
            <x-slot:action><x-button :href="route('vehicles.create')">Tambah Kendaraan</x-button></x-slot:action>
        </x-empty-state>
    @else
        <div class="mt-8 border-t border-line bg-surface">
            @foreach ($vehicles as $vehicle)
                @php $calculation = $vehicle->latestFuzzyCalculation; @endphp
                <article class="grid gap-5 border-b border-line px-4 py-5 sm:px-5 md:grid-cols-[minmax(0,1fr)_minmax(12rem,0.7fr)_auto] md:items-center">
                    <div>
                        <h2 class="text-lg font-semibold text-ink">{{ $vehicle->name }}</h2>
                        <p class="mt-1 text-sm text-ink-muted">{{ $vehicle->brand }} {{ $vehicle->model }} · {{ $vehicle->plate_number }}</p>
                    </div>
                    <div>
                        <x-recommendation-status :status="$calculation?->final_status" />
                        @if ($calculation)
                            <p class="mt-2 text-sm text-ink-muted"><span class="font-semibold tabular-nums text-ink">{{ number_format((float) $calculation->score, 0) }} / 100</span> · {{ $calculation->recommended_date->translatedFormat('j F Y') }}</p>
                        @else
                            <p class="mt-2 text-sm text-ink-muted">Lengkapi baseline servis.</p>
                        @endif
                    </div>
                    <x-button :href="route('recommendations.show', $vehicle)" variant="secondary" class="w-full md:w-auto">Lihat Detail</x-button>
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.app>
