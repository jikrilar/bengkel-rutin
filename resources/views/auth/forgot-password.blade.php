<x-layouts.guest
    title="Lupa kata sandi"
    heading="Atur ulang kata sandi"
    description="Masukkan email akun. Kami akan mengirim tautan reset melalui email."
>
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <x-input name="email" label="Email" type="email" autocomplete="email" required autofocus placeholder="nama@email.com" />
        <x-button type="submit" class="w-full">Kirim tautan reset</x-button>
    </form>

    <p class="mt-7 text-center text-sm">
        <a href="{{ route('login') }}" class="font-semibold text-brand hover:text-brand-hover">Kembali ke halaman masuk</a>
    </p>
</x-layouts.guest>
