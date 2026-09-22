<x-layouts.guest
    title="Masuk"
    heading="Selamat datang kembali"
    description="Masuk untuk melihat kendaraan, rekomendasi servis, dan booking Anda."
>
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <x-input name="email" label="Email" type="email" autocomplete="email" required autofocus placeholder="nama@email.com" />
        <x-input name="password" label="Kata sandi" type="password" autocomplete="current-password" required />

        <div class="flex items-center justify-between gap-4">
            <label class="inline-flex items-center gap-2 text-sm text-ink-muted">
                <input name="remember" type="checkbox" value="1" class="size-4 rounded border-line-strong text-brand focus:ring-brand">
                Ingat saya
            </label>
            <a href="{{ route('password.request') }}" class="text-sm font-semibold text-brand hover:text-brand-hover">Lupa kata sandi?</a>
        </div>

        <x-button type="submit" class="w-full">Masuk</x-button>
    </form>

    <p class="mt-7 text-center text-sm text-ink-muted">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-brand hover:text-brand-hover">Daftar sebagai customer</a>
    </p>
</x-layouts.guest>
