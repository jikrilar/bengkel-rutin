<x-layouts.app title="Edit Kendaraan">
    <x-page-header title="Edit Kendaraan" description="Perbarui identitas kendaraan tanpa mengubah baseline atau histori servis." />

    <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" class="mt-8 max-w-3xl space-y-8">
        @csrf
        @method('PUT')

        <x-alert>
            Profil servis, baseline, dan data historis dikunci untuk menjaga integritas rekomendasi.
        </x-alert>

        @include('customer.vehicles._identity-fields', ['vehicle' => $vehicle])

        <div class="flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:justify-end">
            <x-button :href="route('vehicles.show', $vehicle)" variant="ghost">Batal</x-button>
            <x-button type="submit">Simpan Perubahan</x-button>
        </div>
    </form>
</x-layouts.app>
