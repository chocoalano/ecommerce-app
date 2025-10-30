<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @include('layouts.partials.head') {{-- pastikan di dalamnya sudah ada @fluxAppearance, @vite, @livewireStyles --}}
</head>

<body class="min-h-screen bg-white antialiased">
    {{-- ===== SLOT KONTEN HALAMAN ===== --}}
    @yield('content')

    {{-- Wajib: Livewire sebelum Flux --}}
    @livewireScripts
</body>

</html>
