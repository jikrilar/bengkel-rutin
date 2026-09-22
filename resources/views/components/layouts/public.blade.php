@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Kelola jadwal servis rutin, rekomendasi perawatan, dan booking bengkel dalam satu tempat.">
        <title>{{ $title ? $title.' | '.config('app.name') : config('app.name') }}</title>
        @unless (app()->environment('testing'))
            @fonts
        @endunless
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="overflow-x-hidden">
        {{ $slot }}
        @livewireScripts
    </body>
</html>
