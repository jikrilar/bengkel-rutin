<x-layouts.app title="Tambah Kendaraan">
    <x-page-header title="Tambah Kendaraan" description="Masukkan data kendaraan dan catatan servis terakhir yang Anda ketahui." />

    <form method="POST" action="{{ route('vehicles.store') }}" class="mt-8 max-w-3xl space-y-9" x-data="{ knows: '{{ old('knows_last_service', '1') }}' }">
        @csrf

        <section>
            <x-section-header title="Identitas kendaraan" description="Nomor polisi akan dinormalisasi agar tidak tercatat ganda." />
            <div class="mt-5">
                @include('customer.vehicles._identity-fields')
            </div>
        </section>

        <section class="border-t border-line pt-8">
            <x-section-header title="Odometer saat ini" description="Catatan ini menjadi titik awal histori odometer kendaraan." />
            <div class="mt-5 max-w-sm">
                <x-input name="current_odometer" label="Odometer saat ini" type="number" min="0" inputmode="numeric" required hint="Masukkan angka yang terlihat pada panel kendaraan." />
            </div>
        </section>

        <section class="border-t border-line pt-8">
            <x-section-header title="Baseline servis" description="Data ini dibutuhkan untuk menghitung progres kilometer dan waktu." />

            <fieldset class="mt-5">
                <legend class="text-sm font-semibold text-ink">Apakah Anda mengetahui servis terakhir?</legend>
                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:gap-6">
                    <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-ink">
                        <input type="radio" name="knows_last_service" value="1" x-model="knows" class="size-4 accent-brand" required>
                        Ya, saya mengetahui datanya
                    </label>
                    <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm text-ink">
                        <input type="radio" name="knows_last_service" value="0" x-model="knows" class="size-4 accent-brand" required>
                        Belum tahu
                    </label>
                </div>
                @error('knows_last_service')<p class="mt-1.5 text-sm text-danger">{{ $message }}</p>@enderror
            </fieldset>

            <div x-cloak x-show="knows === '1'" class="mt-5 grid gap-5 sm:grid-cols-2">
                <x-input name="last_service_date" label="Tanggal servis terakhir" type="date" :max="now()->toDateString()" />
                <x-input name="last_service_odometer" label="Odometer saat servis terakhir" type="number" min="0" inputmode="numeric" hint="Nilainya tidak boleh melebihi odometer saat ini." />
            </div>

            <x-alert x-cloak x-show="knows === '0'" class="mt-5">
                Kendaraan tetap dapat disimpan, tetapi rekomendasi belum tersedia sampai baseline servis diverifikasi.
            </x-alert>
        </section>

        <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
            <x-button :href="route('vehicles.index')" variant="ghost">Batal</x-button>
            <x-button type="submit">Simpan Kendaraan</x-button>
        </div>
    </form>
</x-layouts.app>
