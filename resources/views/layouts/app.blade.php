<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.partials.head')
    @filamentStyles
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @yield('css')
</head>

<body class="min-h-screen bg-white antialiased">

    @livewire('layout.header')
    {{-- ===== SLOT KONTEN HALAMAN ===== --}}
    @yield('content')

    @includeIf('layouts.partials.footer')

    {{-- Toast Notification Component --}}
    <div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    {{-- Wajib: Livewire sebelum Flux --}}
    @stack('script')
    @filamentScripts
    @yield('js')
</body>

</html>
