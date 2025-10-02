@extends('layouts.app')

{{-- =========================
SEO Slot
\========================= --}}
@section('title', 'Belanja Minuman & Bahan Baku Terbaik | BrandKamu')
@section('meta')
    <meta name="description"
        content="Belanja minuman serbuk, matcha, dan bahan baku F&B dengan harga grosir. Pengiriman cepat, pembayaran aman, dan promo menarik setiap hari.">
    <meta property="og:title" content="BrandKamu - E-Commerce Bahan Baku & Minuman">
    <meta property="og:description" content="Produk berkualitas, harga bersaing, dan layanan cepat.">
    <meta property="og:type" content="website">
@endsection

@php
    function rupiah($n)
    {
        return 'Rp ' . number_format($n, 0, ',', '.');
    }
@endphp

@section('content')
    {{-- hero:start --}}
    {{-- HERO --}}
    <section x-data="{ isCompact: false }" x-init="const onScroll = () => isCompact = window.scrollY > 10;
    onScroll();
    window.addEventListener('scroll', onScroll);" {{-- Aksen Background & Shadow saat Compact --}}
        class="relative isolate overflow-hidden bg-gradient-to-br from-zinc-50 to-white transition-all duration-300 ease-out"
        x-bind:class="isCompact ? 'max-w-5/6 mx-auto rounded-xl px-4 sm:px-6 lg:px-8' : 'max-w-none w-full px-0'">

        {{-- Overlay/Decor: opsional, menambah tekstur --}}
        <div class="absolute inset-0 bg-repeat opacity-5"
            style="background-image: url('data:image/svg+xml;utf8,<svg width=&quot;4&quot; height=&quot;4&quot; viewBox=&quot;0 0 4 4&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><circle cx=&quot;2&quot; cy=&quot;2&quot; r=&quot;1&quot; fill=&quot;%239ca3af&quot;/></svg>');">
        </div>

        {{-- WRAPPER: full saat top, container saat scroll --}}
        <div class="relative transition-all duration-300 ease-out"
            x-bind:class="isCompact ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' : 'max-w-none w-full px-0'">
            <div class="text-center transition-all duration-300 ease-out"
                x-bind:class="isCompact ? 'py-8 sm:py-12 lg:py-14' : 'py-14 sm:py-20 lg:py-28'">

                {{-- New: Tagline/Type Promosi --}}
                <div class="mb-3 flex items-center justify-center gap-2">
                    <flux:icon name="sparkles" class="text-indigo-600 transition-all duration-300"
                        x-bind:class="isCompact ? 'w-5 h-5' : 'w-6 h-6'" />
                    <p class="font-bold text-indigo-700 transition-all duration-300 uppercase tracking-widest"
                        x-bind:class="isCompact ? 'text-sm sm:text-base' : 'text-base sm:text-lg'">
                        {{-- Menggunakan Optional Chaining (?->) dan Null Coalescing (??) --}}
                        {{ $hero?->type ?? 'Special Offer' }}
                    </p>
                </div>

                {{-- Main Title --}}
                <h1 class="font-black tracking-tight text-gray-900 transition-all duration-300 leading-tight"
                    x-bind:class="isCompact ? 'text-4xl sm:text-6xl lg:text-[60px]' : 'text-5xl sm:text-7xl lg:text-[80px]'">
                    {{-- Menggunakan Optional Chaining (?->) dan Null Coalescing (??) --}}
                    {{ $hero?->name ?? 'Upgrade Ponsel Lama Anda' }}
                </h1>

                {{-- Deskripsi Promosi --}}
                <p class="mt-4 text-gray-700 transition-all duration-300 font-semibold max-w-3xl mx-auto"
                    x-bind:class="isCompact ? 'text-md sm:text-lg' : 'text-lg sm:text-2xl'">
                    {{-- Menggunakan Optional Chaining (?->) dan Null Coalescing (??) --}}
                    {{ $hero?->description ?? 'Dapatkan diskon besar dengan Trade-in eksklusif kami!' }}
                </p>

                {{-- Tambahan Teks Promosi (jika ada) --}}
                @if ($hero && $hero->tagline)
                    <p class="mt-2 text-indigo-600 font-extrabold transition-all duration-300"
                        x-bind:class="isCompact ? 'text-xl sm:text-2xl' : 'text-2xl sm:text-4xl'">
                        {{ $hero->tagline }}
                    </p>
                @endif

                {{-- Call to Action Buttons --}}
                <div class="mt-8 sm:mt-10 flex items-center justify-center gap-4">
                    {{-- PRIMARY CTA: Selalu ada, mengarah ke halaman trade-in atau promosi --}}
                    <flux:button variant="primary"
                        class="rounded-full shadow-lg hover:shadow-xl transition-all duration-300"
                        x-bind:class="isCompact ? 'px-6 py-3 text-base font-semibold' : 'px-8 py-4 text-lg font-bold'"
                        href="{{ $hero?->link_url ?? route('category') }}">
                        <flux:icon name="currency-dollar" class="mr-2 h-5 w-5" />
                        {{ $hero ? 'Cek Harga Trade-in' : 'Lihat Promo Terbaik' }}
                    </flux:button>

                    {{-- Secondary CTA: Learn More / Detail Promosi --}}
                    @if ($hero)
                        <flux:button class="text-gray-700 hover:text-indigo-600 font-semibold transition-all duration-300"
                            x-bind:class="isCompact ? 'text-sm' : 'text-base'" href="{{ $hero->details_url ?? '#' }}">
                            Detail Promosi
                            <flux:icon name="arrow-right" class="ml-1 h-4 w-4" />
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gambar Produk --}}
        <div class="relative">
            <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
                <div class="relative mx-auto grid place-items-center overflow-visible">
                    <div class="relative w-full max-w-[1200px] overflow-visible"
                        x-bind:class="isCompact ? 'max-w-[960px]' : 'max-w-[1500px]'">
                        <div class="w-full overflow-visible rounded-2xl"
                            x-bind:class="isCompact ? 'aspect-[16/7]' : 'aspect-[16/6]'">
                            {{-- Menggunakan gambar promosi jika ada, jika tidak, pakai default --}}
                            <img src="{{ $hero?->image_url ?? asset('images/smartphone.png') }}"
                                alt="{{ $hero?->name ?? 'Produk Highlight' }}" loading="eager" fetchpriority="high"
                                class="h-full w-full object-contain select-none pointer-events-none
                                transition-transform duration-700 ease-[cubic-bezier(.2,.7,.2,1)]
                                will-change-transform drop-shadow-2xl"
                                x-bind:class="isCompact ? 'scale-100 translate-y-0' : 'scale-110 sm:scale-125 translate-y-1'"
                                style="transform-origin:center;" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- hero:end --}}

    {{-- landing category:start --}}
    <livewire:components.landing-category :category="$categoriesFirst" />
    {{-- landing category:end --}}

    {{-- promotion banner:start --}}
    <livewire:components.promotion-hero title="Promo September" subtitle="Smart TV dengan AI terbaru" :primary="['label' => 'Beli Sekarang', 'href' => '#']"
        :secondary="['label' => 'Katalog', 'href' => '#']" :image="'images/galaxy-z-flip7-share-image.png'" imageAlt="Smart TV 2025" />
    {{-- promotion banner:end --}}
    {{-- landing category:start --}}
    <livewire:components.landing-category :category="$categoriesSecond" />
    {{-- landing category:end --}}

    {{-- promotion banner:start --}}
    @php
        // Normalisasi: jika $promoFirst adalah Collection, ambil item pertamanya
        $promo = $promoFirst instanceof \Illuminate\Support\Collection
            ? $promoFirst->first()
            : $promoFirst;

        // Helper kecil untuk ambil nilai dengan default
        $promoName = data_get($promo, 'name');        // null jika tidak ada
        $promoDesc = data_get($promo, 'description'); // null jika tidak ada
        $promoSlug = data_get($promo, 'slug', '');    // '' jika tidak ada
        $promoImage = data_get($promo, 'image');      // null jika tidak ada
    @endphp

    @if ($promo)
        <livewire:components.promotion-hero
            :title="$promoName"
            :subtitle="$promoDesc"
            :primary="[
                'label' => 'Beli Sekarang',
                'href'  => route('category', ['category' => $promoSlug]),
            ]"
            :secondary="[
                'label' => 'Lihat Detail',
                'href'  => route('promotion.show', ['promotion' => $promoSlug ?: '#']),
            ]"
            :image="$promoImage ?? asset('images/default-hero.webp')"
            :imageAlt="$promoName ?? 'Promosi Terbaru'"
        />
    @else
        {{-- FALLBACK --}}
        <livewire:components.promotion-hero
            title="Welcome to Our Store!"
            subtitle="Temukan penawaran terbaik dan produk terbaru kami."
            :primary="['label' => 'Jelajahi Sekarang', 'href' => route('home')]"
            :secondary="['label' => 'Bantuan', 'href' => route('home')]"
            :image="asset('images/default-hero.webp')"
            imageAlt="Default Hero Image"
        />
    @endif

    {{-- promotion banner:end --}}

    <livewire:components.product-carousel class="mt-10 mb-10" title="Rekomendasi produk untuk anda"
        description="Kami selalu berusaha untuk memberikan produk terbaik untuk memenuhi kebutuhan anda."
        :data="[
            [
                'id' => 1,
                'name' => 'Galaxy S25 Ultra',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp22.999.000',
            ],
            [
                'id' => 2,
                'name' => 'Galaxy Z Fold7',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp27.999.000',
            ],
            [
                'id' => 3,
                'name' => 'Galaxy S24 Ultra',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp16.999.000',
            ],
            [
                'id' => 4,
                'name' => 'Galaxy S25 Ultra (Samsung.com only)',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp22.999.000',
            ],
            [
                'id' => 5,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 6,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 7,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 8,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 9,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
            [
                'id' => 10,
                'name' => 'Galaxy A56 5G',
                'image' => asset('images/galaxy-z-flip7-share-image.png'),
                'price' => 'Rp6.199.000',
            ],
        ]" />
@endsection
