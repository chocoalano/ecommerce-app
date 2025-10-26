<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('layouts.partials.head')
    @filamentStyles
    @yield('css')
</head>

<body class="min-h-screen bg-white antialiased">

    @livewire('layout.header')
    {{-- ===== SLOT KONTEN HALAMAN ===== --}}
    @yield('content')

    @includeIf('layouts.partials.footer')

    {{-- Toast Notification Component --}}
    @include('components.toast-notification')

    {{-- Wajib: Livewire sebelum Flux --}}
    @stack('script')
    @filamentScripts
    @yield('js')
</body>

</html>
