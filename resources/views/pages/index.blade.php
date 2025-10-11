@extends('layouts.app')

@section('title', 'Sitemap - ' . config('app.name'))
@section('meta-description', 'Jelajahi semua halaman dan informasi yang tersedia di ' . config('app.name') . '. Temukan bantuan, informasi perusahaan, dan panduan lengkap.')

@section('meta')
    <meta name="keywords" content="sitemap, peta situs, navigasi, bantuan, perusahaan, panduan, {{ config('app.name') }}, bahan baku minuman, F&B">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Peta Situs - {{ config('app.name') }}">
    <meta property="og:description" content="Jelajahi semua halaman dan informasi yang tersedia di {{ config('app.name') }}. Temukan bantuan, informasi perusahaan, dan panduan lengkap.">
    <meta property="og:url" content="{{ route('page.index') }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('page.index') }}">
@endsection
        content="Temukan produk berkualitas, harga bersaing, dan layanan pengiriman cepat untuk kebutuhan bisnis dan rumah Anda.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/logo-puranura-id.png') }}">
    <meta name="robots" content="index, follow">
@endsection
@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                    Peta Situs
                </h1>
                <p class="text-lg text-gray-600">
                    Jelajahi semua halaman dan informasi yang tersedia di situs kami.
                </p>
            </div>
        </div>
    </div>

    <!-- Pages by Category -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        @foreach($pagesByCategory as $category => $pages)
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    {{ \App\Models\Page::getCategories()[$category] ?? $category }}
                </h2>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($pages as $page)
                        <div class="bg-white rounded-lg border border-gray-200 hover:shadow-md transition duration-300">
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                    {{ $page->title }}
                                </h3>

                                @if($page->excerpt)
                                    <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                                        {{ $page->excerpt }}
                                    </p>
                                @endif

                                <a href="{{ route('page.show', $page->slug) }}"
                                   class="inline-flex items-center text-zinc-900 hover:text-zinc-700 font-medium text-sm transition">
                                    Lihat Halaman
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Back to Home -->
        <div class="mt-12 text-center">
            <a href="{{ route('home') }}"
               class="inline-flex items-center text-gray-600 hover:text-zinc-900 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Beranda
            </a>
        </div>
    </div>
</div>
@endsection
