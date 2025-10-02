<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('layouts.partials.head') {{-- pastikan di dalamnya sudah ada @fluxAppearance, @vite, @livewireStyles --}}
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">

    @includeIf('layouts.partials.header')
    @includeIf('layouts.partials.sidebar')

    {{-- ===== SLOT KONTEN HALAMAN ===== --}}
    @yield('content')

    @includeIf('layouts.partials.footer')

    {{-- Wajib: Livewire sebelum Flux --}}
    @livewireScripts
    @fluxScripts
</body>

</html>
