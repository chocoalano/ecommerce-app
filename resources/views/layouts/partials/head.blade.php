<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>@yield('title', config('app.name'))</title>

<!-- Default Meta Tags -->
<meta name="description" content="@yield('meta-description', 'Bahan baku minuman berkualitas untuk HORECA dan UMKM. Cepat, segar, dan terpercaya.')">
<meta name="keywords" content="@yield('meta-keywords', 'bahan baku minuman, kopi, teh, matcha, ecommerce F&B, grosir minuman, supplier cafe')">
<meta name="author" content="@yield('meta-author', config('app.name'))">

<!-- Open Graph Meta Tags -->
@php
    $pageTitle = trim(View::yieldContent('title')) ?: config('app.name');
    $pageDescription = trim(View::yieldContent('meta-description')) ?: 'Bahan baku minuman berkualitas untuk HORECA dan UMKM. Cepat, segar, dan terpercaya.';
@endphp
<meta property="og:title" content="@yield('og-title', $pageTitle)">
<meta property="og:description" content="@yield('og-description', $pageDescription)">
<meta property="og:type" content="@yield('og-type', 'website')">
<meta property="og:url" content="@yield('og-url', request()->url())">
<meta property="og:image" content="@yield('og-image', asset('images/logo-puranura-id.png'))">
<meta property="og:site_name" content="{{ config('app.name') }}">

<!-- Twitter Card Meta Tags -->
<meta name="twitter:card" content="@yield('twitter-card', 'summary')">
<meta name="twitter:title" content="@yield('twitter-title', $pageTitle)">
<meta name="twitter:description" content="@yield('twitter-description', $pageDescription)">
<meta name="twitter:image" content="@yield('twitter-image', asset('images/logo-puranura-id.png'))">

<!-- Additional Meta Tags -->
<meta name="robots" content="@yield('meta-robots', 'index, follow')">
<meta name="googlebot" content="@yield('meta-googlebot', 'index, follow')">
<link rel="canonical" href="@yield('canonical', request()->url())">

<!-- Custom Meta Section -->
@yield('meta')

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])

@livewireStyles
@livewireScripts
@stack('styles')
