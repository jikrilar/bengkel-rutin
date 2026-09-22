<x-layouts.app title="Profil">
    <x-page-header title="Profil" description="Kelola informasi kontak dan keamanan akun Anda." />

    <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-14">
        <section>
            <x-section-header title="Informasi akun" description="Email ini digunakan untuk login dan pemulihan password." />

            @if (session('success'))
                <x-alert type="success" class="mt-5" role="status">{{ session('success') }}</x-alert>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PATCH')
                <x-input name="name" label="Nama" :value="$user->name" autocomplete="name" required />
                <x-input name="email" label="Email" type="email" :value="$user->email" autocomplete="email" required />
                <x-input name="phone" label="Nomor telepon" type="tel" :value="$user->phone" autocomplete="tel" hint="Opsional. Digunakan untuk informasi terkait servis." />
                <div class="pt-1"><x-button type="submit">Simpan Profil</x-button></div>
            </form>
        </section>

        <section class="border-t border-line pt-9 lg:border-t-0 lg:border-l lg:pt-0 lg:pl-10">
            <x-section-header title="Ubah password" description="Gunakan password yang unik dan tidak dipakai di layanan lain." />

            @if (session('password_success'))
                <x-alert type="success" class="mt-5" role="status">{{ session('password_success') }}</x-alert>
            @endif

            <form method="POST" action="{{ route('profile.password.update') }}" class="mt-5 space-y-5">
                @csrf
                @method('PUT')
                <x-input name="current_password" label="Password saat ini" type="password" autocomplete="current-password" required />
                <x-input name="password" label="Password baru" type="password" autocomplete="new-password" required hint="Minimal 8 karakter." />
                <x-input name="password_confirmation" label="Konfirmasi password baru" type="password" autocomplete="new-password" required />
                <div class="pt-1"><x-button type="submit">Ubah Password</x-button></div>
            </form>
        </section>
    </div>
</x-layouts.app>
