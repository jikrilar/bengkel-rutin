<x-filament-panels::page>
    <form wire:submit="applyFilters" class="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 sm:grid-cols-[1fr_1fr_auto] sm:items-end dark:border-white/10 dark:bg-gray-900">
        <label class="grid gap-2 text-sm font-medium text-gray-950 dark:text-white">
            Tanggal awal
            <input type="date" wire:model="startDate" class="rounded-lg border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-white/5" required>
            @error('startDate') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
        </label>
        <label class="grid gap-2 text-sm font-medium text-gray-950 dark:text-white">
            Tanggal akhir
            <input type="date" wire:model="endDate" class="rounded-lg border-gray-300 bg-white text-sm dark:border-white/10 dark:bg-white/5" required>
            @error('endDate') <span class="text-sm text-danger-600">{{ $message }}</span> @enderror
        </label>
        <x-filament::button type="submit">Terapkan periode</x-filament::button>
    </form>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan laporan">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">Total booking</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($report['total_bookings'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">Servis selesai</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($report['completed_services'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">Pendapatan servis</p>
            <p class="mt-2 text-2xl font-semibold tabular-nums text-gray-950 dark:text-white">Rp{{ number_format($report['service_revenue'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm text-gray-600 dark:text-gray-400">Customer unik</p>
            <p class="mt-2 text-3xl font-semibold tabular-nums text-gray-950 dark:text-white">{{ number_format($report['unique_customers'], 0, ',', '.') }}</p>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900" aria-labelledby="service-transactions-title">
        <div class="border-b border-gray-200 px-5 py-4 dark:border-white/10">
            <h2 id="service-transactions-title" class="text-base font-semibold text-gray-950 dark:text-white">Transaksi servis</h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Maksimal 100 transaksi terbaru pada periode terpilih.</p>
        </div>

        @if ($report['transactions']->isEmpty())
            <div class="p-8 text-center text-sm text-gray-600 dark:text-gray-400">Belum ada transaksi servis pada periode ini.</div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-white/10">
                    <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-600 dark:bg-white/5 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Customer</th>
                            <th class="px-4 py-3">Kendaraan</th>
                            <th class="px-4 py-3">Jenis servis</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($report['transactions'] as $record)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-3 text-gray-700 dark:text-gray-300">{{ $record->service_date->translatedFormat('d M Y') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-950 dark:text-white">{{ $record->service_code }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $record->vehicle->user->name }}</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $record->vehicle->name }} ({{ $record->vehicle->plate_number }})</td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">{{ $record->service_type }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right font-medium tabular-nums text-gray-950 dark:text-white">Rp{{ number_format((float) $record->total_cost, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-filament-panels::page>
