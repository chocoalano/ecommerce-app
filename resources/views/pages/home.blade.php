@extends('layouts.app')

{{-- =========================
SEO Slot
\========================= --}}
@section('title', 'Belanja Minuman & Bahan Baku Terbaik | BrandKamu')
@section('meta')
    <meta name="description"
        content="Belanja bahan baku minuman, kopi, teh, dan kebutuhan F&B terbaik di BrandKamu. Dapatkan harga grosir, promo eksklusif, pengiriman cepat, dan pembayaran aman.">
    <meta name="keywords"
        content="bahan baku minuman, kopi, teh, matcha, ecommerce F&B, grosir minuman, supplier cafe, BrandKamu, promo minuman, belanja online">
    <meta property="og:title" content="BrandKamu - Supplier Bahan Baku Minuman & F&B Terpercaya">
    <meta property="og:description"
        content="Temukan produk berkualitas, harga bersaing, dan layanan pengiriman cepat untuk kebutuhan bisnis dan rumah Anda.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/logo-puranura-id.png') }}">
    <meta name="robots" content="index, follow">
@endsection

@section('content')

    {{-- hero:start --}}
    <livewire:components.hero-section :heroType="$heroData['heroType']" :heroTitle="$heroData['heroTitle']"
        :heroDesc="$heroData['heroDesc']" :heroTag="$heroData['heroTag']" :heroImg="$heroData['heroImg']"
        :heroLink="$heroData['heroLink']" :heroMore="$heroData['heroMore']" :hero="$heroData['hero']" />
    {{-- hero:end --}}

    {{-- landing category 1:start --}}
    @if ($categoriesFirst && $categoriesFirst->count() > 0)
        <livewire:components.landing-category :category="$categoriesFirst" />
    @else
        <div class="mt-10 mb-10"></div>
    @endif
    {{-- landing category 1:end --}}

    {{-- promotion banner 1:start --}}
    @php
        $p1Promo = $p1Data['promo']; // Objek Promotion untuk pengecekan
        $p1Slug = $p1Data['slug'];
    @endphp

    @if ($p1Promo)
        <livewire:components.promotion-hero :title="$p1Data['name']" :subtitle="$p1Data['desc']" :primary="[
                    'label' => 'Beli Sekarang',
                    'href' => route('products.index', ['category' => $p1Slug]),
                ]" :secondary="[
                    'label' => 'Katalog',
                    'href' => Route::has('promotion.show') ? route('promotion.show', ['promotion' => $p1Slug]) : '#',
                ]" :image="$p1Data['image']"
            :imageAlt="$p1Data['name']" />
    @else
        <div class="mt-10 mb-10"></div>
    @endif
    {{-- promotion banner 1:end --}}

    {{-- landing category 2:start --}}
    @if ($categoriesSecond && $categoriesSecond->count() > 0)
        <livewire:components.landing-category :category="$categoriesSecond" />
    @else
        <div class="mt-10 mb-10"></div>
    @endif
    {{-- landing category 2:end --}}

    {{-- promotion banner 2:start --}}
    @php
        $p2Promo = $p2Data['promo']; // Objek Promotion untuk pengecekan
        $p2Slug = $p2Data['slug'];
    @endphp

    @if ($p2Promo)
        <livewire:components.promotion-hero :title="$p2Data['name']" :subtitle="$p2Data['desc']" :primary="[
                    'label' => 'Beli Sekarang',
                    'href' => route('products.index', ['category' => $p2Slug]),
                ]" :secondary="[
                    'label' => 'Katalog',
                    'href' => Route::has('promotion.show') ? route('promotion.show', ['promotion' => $p2Slug]) : '#',
                ]" :image="$p2Data['image']"
            :imageAlt="$p2Data['name']" />
    @else
        <div class="mt-10 mb-10"></div>
    @endif
    {{-- promotion banner 2:end --}}

    {{-- Produk rekomendasi:start --}}
    @if ($recommendedProducts && count($recommendedProducts) > 0)
        <livewire:components.product-carousel class="mt-10 mb-10" title="Rekomendasi produk untuk anda"
            description="Kami selalu berusaha untuk memberikan produk terbaik untuk memenuhi kebutuhan anda."
            :data="$recommendedProducts" />
    @endif
    {{-- Produk rekomendasi:end --}}
@endsection
