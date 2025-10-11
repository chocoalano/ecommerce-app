@extends('layouts.app')

@section('title', $metaTitle)
@section('meta-description', $metaDescription)

@section('meta')
    @php
        $keywords = '';
        if($page->category == 'company') {
            $keywords = 'perusahaan, tentang kami, profil perusahaan, ' . config('app.name');
        } elseif($page->category == 'help') {
            $keywords = 'bantuan, panduan, cara belanja, customer service, ' . config('app.name');
        } else {
            $keywords = $page->title . ', informasi, ' . config('app.name');
        }
    @endphp
    <meta name="keywords" content="{{ $keywords }}">
    <meta property="og:type" content="article">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ route('page.show', $page->slug) }}">
    <meta property="article:published_time" content="{{ $page->created_at->toISOString() }}">
    <meta property="article:modified_time" content="{{ $page->updated_at->toISOString() }}">
    <meta property="article:section" content="{{ $page->category_label }}">
    <link rel="canonical" href="{{ route('page.show', $page->slug) }}">
@endsection

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
                    {{ $page->title }}
                </h1>
                @if($page->excerpt)
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        {{ $page->excerpt }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="p-8 md:p-12">
                <!-- Page Content -->
                <div class="prose prose-lg max-w-none">
                    @if($page->content)
                        {!! $page->content !!}
                    @else
                        <p class="text-gray-600">Konten sedang dalam proses pengembangan.</p>
                    @endif
                </div>

                <!-- Back to Home -->
                <div class="mt-12 pt-8 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('home') }}"
                           class="inline-flex items-center text-gray-600 hover:text-zinc-900 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Kembali ke Beranda
                        </a>

                        <!-- Category Badge -->
                        <span class="inline-flex items-center px-3 py-1 text-sm font-medium bg-gray-100 text-gray-800 rounded-full">
                            {{ $page->category_label }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
