@props(['vehicle' => null])

<div class="grid gap-5 sm:grid-cols-2">
    <x-input name="name" label="Nama kendaraan" :value="$vehicle?->name" autocomplete="off" required hint="Contoh: Mobil Keluarga atau Motor Harian." />
    <x-input name="plate_number" label="Nomor polisi" :value="$vehicle?->plate_number" autocomplete="off" required placeholder="B 1234 XYZ" />
    <x-input name="brand" label="Merek" :value="$vehicle?->brand" autocomplete="organization" required placeholder="Toyota" />
    <x-input name="model" label="Model" :value="$vehicle?->model" autocomplete="off" required placeholder="Avanza" />
    <x-input name="year" label="Tahun" type="number" :value="$vehicle?->year" min="1900" :max="now()->year" inputmode="numeric" required />
</div>
