<x-layouts.guest
    title="Reset kata sandi"
    heading="Buat kata sandi baru"
    description="Pilih kata sandi baru yang kuat untuk melindungi akun Anda."
>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <x-input name="email" label="Email" type="email" :value="old('email', $request->email)" autocomplete="email" required autofocus />
        <x-input name="password" label="Kata sandi baru" type="password" autocomplete="new-password" required />
        <x-input name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password" required />
        <x-button type="submit" class="w-full">Simpan kata sandi baru</x-button>
    </form>
</x-layouts.guest>
