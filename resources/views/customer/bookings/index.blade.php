<x-layouts.app title="Booking">
    <x-page-header title="Booking" description="Pantau jadwal aktif dan riwayat kunjungan bengkel Anda.">
        <x-slot:actions><x-button :href="route('bookings.create')">Buat Booking</x-button></x-slot:actions>
    </x-page-header>

    <section class="mt-9">
        <x-section-header title="Jadwal aktif" description="Booking yang menunggu konfirmasi, sudah dikonfirmasi, atau sedang berlangsung." />
        @if ($activeBookings->isEmpty())
            <x-empty-state class="mt-5" icon="heroicon-o-calendar-days" title="Belum ada jadwal aktif" description="Pilih slot bengkel sesuai waktu yang nyaman untuk Anda.">
                <x-slot:action><x-button :href="route('bookings.create')">Pilih Jadwal</x-button></x-slot:action>
            </x-empty-state>
        @else
            <div class="mt-5 divide-y divide-line overflow-hidden rounded-panel border border-line bg-surface">
                @foreach ($activeBookings as $booking)
                    <a href="{{ route('bookings.show', $booking) }}" class="flex min-w-0 flex-col gap-3 p-4 hover:bg-surface-subtle sm:flex-row sm:items-center sm:p-5">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2"><p class="font-semibold text-ink">{{ $booking->vehicle->name }}</p><x-status-badge :status="$booking->status->tone()">{{ $booking->status->label() }}</x-status-badge></div>
                            <p class="mt-1 text-sm text-ink-muted">{{ $booking->vehicle->plate_number }} · {{ $booking->booking_code }}</p>
                        </div>
                        <div class="shrink-0 sm:text-right"><p class="font-semibold text-ink">{{ $booking->scheduled_at->translatedFormat('j F Y') }}</p><p class="text-sm tabular-nums text-ink-muted">{{ $booking->scheduled_at->format('H.i') }}</p></div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="mt-10 border-t border-line pt-8">
        <x-section-header title="Riwayat" description="Booking yang selesai atau dibatalkan." />
        @if ($historyBookings->isEmpty())
            <p class="mt-5 text-sm text-ink-muted">Belum ada riwayat booking.</p>
        @else
            <div class="mt-5 divide-y divide-line">
                @foreach ($historyBookings as $booking)
                    <a href="{{ route('bookings.show', $booking) }}" class="flex min-w-0 flex-col gap-2 py-4 sm:flex-row sm:items-center">
                        <div class="min-w-0 flex-1"><p class="font-semibold text-ink">{{ $booking->vehicle->name }}</p><p class="mt-1 text-sm text-ink-muted">{{ $booking->scheduled_at->translatedFormat('j F Y, H.i') }}</p></div>
                        <x-status-badge :status="$booking->status->tone()">{{ $booking->status->label() }}</x-status-badge>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $historyBookings->links() }}</div>
        @endif
    </section>
</x-layouts.app>
