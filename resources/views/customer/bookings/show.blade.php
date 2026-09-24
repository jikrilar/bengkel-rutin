<x-layouts.app :title="$booking->booking_code">
    <x-page-header :title="$booking->booking_code" :description="$booking->vehicle->name.' · '.$booking->vehicle->plate_number">
        <x-slot:actions><x-button :href="route('bookings.index')" variant="secondary">Semua Booking</x-button></x-slot:actions>
    </x-page-header>

    <x-flash class="mt-6" />

    <section class="mt-8 rounded-panel border border-line bg-surface p-5 sm:p-7">
        <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
            <div><x-status-badge :status="$booking->status->tone()">{{ $booking->status->label() }}</x-status-badge><h2 class="mt-4 text-2xl font-semibold text-ink">{{ $booking->scheduled_at->translatedFormat('l, j F Y') }}</h2><p class="mt-1 text-lg tabular-nums text-ink-muted">Pukul {{ $booking->scheduled_at->format('H.i') }} · {{ $booking->duration_minutes }} menit</p></div>
            <div class="sm:text-right"><p class="text-sm text-ink-muted">Kendaraan</p><p class="mt-1 font-semibold text-ink">{{ $booking->vehicle->name }}</p><p class="text-sm text-ink-muted">{{ $booking->vehicle->brand }} {{ $booking->vehicle->model }}</p></div>
        </div>
        <div class="mt-6 border-t border-line pt-5"><p class="text-sm font-semibold text-ink">Keluhan / catatan</p><p class="mt-2 whitespace-pre-line text-sm leading-6 text-ink-muted">{{ $booking->complaint ?: 'Tidak ada catatan.' }}</p></div>
        @if ($booking->cancellation_reason)<div class="mt-5 rounded-field bg-danger-soft p-4 text-sm text-danger-ink"><strong>Alasan pembatalan:</strong> {{ $booking->cancellation_reason }}</div>@endif
    </section>

    <section class="mt-10">
        <x-section-header title="Timeline booking" description="Setiap perubahan jadwal dan status tersimpan di sini." />
        <ol class="relative mt-6 ml-3 border-l border-line pl-6">
            @foreach ($booking->events->sortByDesc('created_at') as $event)
                <li class="relative pb-7 last:pb-0">
                    <span class="absolute -left-[1.76rem] top-1.5 size-3 rounded-full border-2 border-canvas bg-brand"></span>
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between"><p class="font-semibold text-ink">{{ $event->event_type->label() }}</p><time class="text-xs text-ink-muted">{{ $event->created_at->translatedFormat('j F Y, H.i') }}</time></div>
                    @if ($event->event_type->value === 'rescheduled')<p class="mt-2 text-sm text-ink-muted">{{ $event->old_scheduled_at?->translatedFormat('j F Y, H.i') }} → <strong class="text-ink">{{ $event->new_scheduled_at?->translatedFormat('j F Y, H.i') }}</strong></p>@endif
                    @if ($event->note)<p class="mt-1 text-sm text-ink-muted">{{ $event->note }}</p>@endif
                </li>
            @endforeach
        </ol>
    </section>

    @if ($booking->status->canBeCancelled())
        <section class="mt-10 border-t border-line pt-8" x-data="{ open: false }">
            <button type="button" class="min-h-11 text-sm font-semibold text-danger" x-on:click="open = ! open" x-bind:aria-expanded="open">Batalkan booking</button>
            <form x-cloak x-show="open" method="POST" action="{{ route('bookings.cancel', $booking) }}" class="mt-4 max-w-xl rounded-panel border border-danger/30 bg-danger-soft p-5">
                @csrf
                <label for="reason" class="block text-sm font-semibold text-ink">Alasan pembatalan</label>
                <textarea id="reason" name="reason" required minlength="5" maxlength="500" rows="3" class="mt-2 w-full rounded-field border border-line-strong bg-surface px-3.5 py-3 text-sm">{{ old('reason') }}</textarea>
                @error('reason')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
                <div class="mt-4 flex flex-wrap gap-3"><x-button type="submit" variant="danger">Ya, Batalkan</x-button><x-button type="button" variant="secondary" x-on:click="open = false">Kembali</x-button></div>
            </form>
        </section>
    @endif
</x-layouts.app>
