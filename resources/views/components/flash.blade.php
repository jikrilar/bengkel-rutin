@if (session('success'))
    <x-alert type="success" class="mb-6" role="status">
        {{ session('success') }}
    </x-alert>
@endif
