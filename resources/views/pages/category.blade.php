@extends('layouts.app')

@section('title', $categoryName . ' - ' . config('app.name'))
@section('meta-description', 'Jelajahi halaman ' . strtolower($categoryName) . ' kami. Temukan informasi lengkap mengenai ' . strtolower($categoryName) . ' di ' . config('app.name') . '.')

@section('meta')
    @php
        $keywords = strtolower($categoryName);
        if($category == 'company') {
            $keywords .= ', perusahaan, tentang kami, profil perusahaan';
        } elseif($category == 'help') {
            $keywords .= ', bantuan, panduan, cara belanja, customer service, FAQ';
        }
        $keywords .= ', ' . config('app.name');
    @endphp
    <meta name="keywords" content="{{ $keywords }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $categoryName }} - {{ config('app.name') }}">
    <meta property="og:description" content="Jelajahi halaman {{ strtolower($categoryName) }} kami. Temukan informasi lengkap mengenai {{ strtolower($categoryName) }} di {{ config('app.name') }}.">
    <meta property="og:url" content="{{ route('page.category', $category) }}">
    <link rel="canonical" href="{{ route('page.category', $category) }}">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                    {{ $categoryName }}
                </h1>
                <p class="text-lg text-gray-600">
                    Temukan informasi lengkap mengenai {{ strtolower($categoryName) }} kami.
                </p>
            </div>
        </div>
    </div>

    <!-- Pages List -->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($pages as $page)
                <div class="bg-white rounded-xl border border-gray-200 hover:shadow-md transition duration-300">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">
                            {{ $page->title }}
                        </h3>

                        @if($page->excerpt)
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {{ $page->excerpt }}
                            </p>
                        @endif

                        <a href="{{ route('page.show', $page->slug) }}"
                           class="inline-flex items-center text-zinc-900 hover:text-zinc-700 font-medium transition">
                            Baca Selengkapnya
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

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
