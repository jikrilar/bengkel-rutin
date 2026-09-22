<x-layouts.guest
    title="Daftar"
    heading="Mulai catat perawatan"
    description="Buat akun customer untuk menyimpan kendaraan dan jadwal servis Anda."
>
    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf
        <x-input name="name" label="Nama lengkap" autocomplete="name" required autofocus />
        <x-input name="email" label="Email" type="email" autocomplete="email" required placeholder="nama@email.com" />
        <x-input name="phone" label="Nomor telepon" type="tel" autocomplete="tel" required placeholder="08xxxxxxxxxx" />
        <x-input name="password" label="Kata sandi" type="password" autocomplete="new-password" required hint="Gunakan sedikitnya 8 karakter." />
        <x-input name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" required />

        <x-button type="submit" class="w-full">Buat akun customer</x-button>
    </form>

    <p class="mt-7 text-center text-sm text-ink-muted">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-brand hover:text-brand-hover">Masuk</a>
    </p>
</x-layouts.guest>
