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

@once
@php
    // Helper uang
    if (! function_exists('rupiah')) {
        function rupiah($n) {
            return 'Rp ' . number_format((float)$n, 0, ',', '.');
        }
    }

    /**
     * Ambil promo berdasarkan slot, dengan cache 5 menit
     */
    if (! function_exists('getPromotionSlot')) {
        function getPromotionSlot(string $slot): ?\App\Models\Promotion {
            return \Illuminate\Support\Facades\Cache::remember("promo:slot:{$slot}", 300, function () use ($slot) {
                return \App\Models\Promotion::query()
                    ->where('is_active', true)
                    ->where('show_on', $slot)
                    ->orderByDesc('priority')
                    ->first();
            });
        }
    }
@endphp
@endonce

@php
    // Data HERO
    $heroTitle = $hero?->name         ?? 'Upgrade Ponsel Lama Anda';
    $heroType  = $hero?->type         ?? 'Special Offer';
    $heroDesc  = $hero?->description  ?? 'Dapatkan diskon besar dengan Trade-in eksklusif kami!';
    $heroTag   = $hero?->tagline      ?? null;
    $heroImg   = $hero?->image_url    ?? asset('images/smartphone.png');
    $heroLink  = $hero?->link_url     ?? route('category');
    $heroMore  = $hero?->details_url  ?? '#';

    // Promo
    $promo1 = getPromotionSlot('HERO_1') ?? getPromotionSlot('HERO');

    $promo2 = getPromotionSlot('HERO_2')
        ?? \Illuminate\Support\Facades\Cache::remember('promo:slot:HERO:second', 300, function () use ($promo1) {
            $q = \App\Models\Promotion::query()
                ->where('is_active', true)
                ->where('show_on', 'HERO')
                ->orderByDesc('priority');

            if ($promo1) { $q->whereKeyNot($promo1->getKey()); }
            return $q->first();
        });

    // Produk rekomendasi
    $recommendedProducts = \Illuminate\Support\Facades\Cache::remember('products:recommended:10', 300, function () {
        return \App\Models\Product::query()
            ->select(['id', 'sku', 'name', 'slug', 'created_at']) // => sertakan 'id' agar eager load jalan
            ->with([
                'productMedia:id,product_id,url',
                'productVariants:id,product_id,base_price',
                'productReviews:id,product_id,rating',
            ])
            ->withAvg('productReviews as avg_rating', 'rating') // optional
            ->where('is_active', true)
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($p) {
                // Ambil gambar pertama (fallback aman)
                $image = optional($p->productMedia->first())->url ?: asset('storage/images/galaxy-z-flip7-share-image.png');

                // Pilih varian dengan harga terendah (sale_price > price > base_price)
                $variant = $p->productVariants
                    ->sortBy(function ($v) {
                        return $v->sale_price ?? $v->price ?? $v->base_price ?? PHP_INT_MAX;
                    })
                    ->first();

                $rawPrice = optional($variant)->sale_price ?? optional($variant)->price ?? optional($variant)->base_price ?? 0;

                return [
                    // Jika produk tidak punya SKU, fallback ke SKU varian
                    'sku'     => $p->sku ?: optional($variant)->sku,
                    'title'   => $p->name,
                    'image'   => $image,
                    'price'   => rupiah((float) $rawPrice), // helper rupiah milikmu
                    'rating'  => $p->avg_rating ? round($p->avg_rating, 1) : null, // optional
                ];
            })
            ->toArray();
    });
@endphp

@section('content')
    {{-- hero:start --}}
    <section x-data="{ isCompact: false }"
             x-init="
                const onScroll = () => { isCompact = window.scrollY > 10 };
                onScroll(); window.addEventListener('scroll', onScroll);
             "
             class="relative isolate overflow-hidden bg-gradient-to-br from-zinc-50 to-white transition-all duration-300 ease-out"
             :class="isCompact ? 'max-w-5/6 mx-auto rounded-xl px-4 sm:px-6 lg:px-8' : 'max-w-none w-full px-0'">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-repeat opacity-5"
             style="background-image:url('data:image/svg+xml;utf8,<svg width=&quot;4&quot; height=&quot;4&quot; viewBox=&quot;0 0 4 4&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><circle cx=&quot;2&quot; cy=&quot;2&quot; r=&quot;1&quot; fill=&quot;%239ca3af&quot;/></svg>');">
        </div>

        <div class="relative transition-all duration-300 ease-out"
             :class="isCompact ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' : 'max-w-none w-full px-0'">
            <div class="text-center transition-all duration-300 ease-out"
                 :class="isCompact ? 'py-8 sm:py-12 lg:py-14' : 'py-14 sm:py-20 lg:py-28'">

                {{-- Tagline --}}
                <div class="mb-3 flex items-center justify-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                         class="text-zinc-900 transition-all duration-300"
                         :class="isCompact ? 'w-5 h-5' : 'w-6 h-6'">
                        <path fill-rule="evenodd"
                              d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                              clip-rule="evenodd" />
                    </svg>
                    <p class="font-bold text-zinc-900 transition-all duration-300 uppercase tracking-widest"
                       :class="isCompact ? 'text-sm sm:text-base' : 'text-base sm:text-lg'">
                        {{ $heroType }}
                    </p>
                </div>

                {{-- Title --}}
                <h1 class="font-black tracking-tight text-gray-900 transition-all duration-300 leading-tight"
                    :class="isCompact ? 'text-4xl sm:text-6xl lg:text-[60px]' : 'text-5xl sm:text-7xl lg:text-[80px]'">
                    {{ $heroTitle }}
                </h1>

                {{-- Deskripsi --}}
                <p class="mt-4 text-gray-700 transition-all duration-300 font-semibold max-w-3xl mx-auto"
                   :class="isCompact ? 'text-md sm:text-lg' : 'text-lg sm:text-2xl'">
                    {{ $heroDesc }}
                </p>

                {{-- Tagline tambahan --}}
                @if ($heroTag)
                    <p class="mt-2 text-zinc-600 font-extrabold transition-all duration-300"
                       :class="isCompact ? 'text-xl sm:text-2xl' : 'text-2xl sm:text-4xl'">
                        {{ $heroTag }}
                    </p>
                @endif

                {{-- CTA --}}
                <div class="mt-8 sm:mt-10 flex items-center justify-center gap-4">
                    <a href="{{ $heroLink }}"
                       class="inline-flex items-center justify-center bg-zinc-900 text-white hover:bg-zinc-800 rounded-full shadow-lg hover:shadow-xl transition-all duration-300"
                       :class="isCompact ? 'px-6 py-3 text-base font-semibold' : 'px-8 py-4 text-lg font-bold'">
                        Cek Harga Trade-in
                    </a>

                    @if ($hero)
                        <a href="{{ $heroMore }}"
                           class="inline-flex items-center justify-center text-gray-700 hover:text-zinc-600 font-semibold transition-all duration-300"
                           :class="isCompact ? 'text-sm' : 'text-base'">
                            Detail Promosi →
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Gambar Hero --}}
        <div class="relative">
            <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
                <div class="relative mx-auto grid place-items-center overflow-visible">
                    <div class="relative w-full max-w-[1200px] overflow-visible"
                         :class="isCompact ? 'max-w-[960px]' : 'max-w-[1500px]'">
                        <div class="w-full overflow-visible rounded-2xl"
                             :class="isCompact ? 'aspect-[16/7]' : 'aspect-[16/6]'">
                            <img src="{{ $heroImg }}"
                                 alt="{{ $heroTitle }}"
                                 loading="eager"
                                 fetchpriority="high"
                                 decoding="async"
                                 class="h-full w-full object-contain select-none pointer-events-none transition-transform duration-700 ease-[cubic-bezier(.2,.7,.2,1)] will-change-transform drop-shadow-2xl"
                                 :class="isCompact ? 'scale-100 translate-y-0' : 'scale-110 sm:scale-125 translate-y-1'"
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

    {{-- promotion banner 1 --}}
    @php
        $p1Name  = data_get($promo1, 'name', 'Promo Unggulan');
        $p1Desc  = data_get($promo1, 'description', 'Penawaran terbaik minggu ini.');
        $p1Slug  = data_get($promo1, 'landing_slug', '');
        $p1Image = data_get($promo1, 'image') ?: asset('images/galaxy-z-flip7-share-image.png');
    @endphp

    <livewire:components.promotion-hero
        :title="$p1Name"
        :subtitle="$p1Desc"
        :primary="['label' => 'Beli Sekarang','href'  => $promo1 ? route('category',['category'=>$p1Slug]) : '#']"
        :secondary="['label' => 'Katalog','href'  => ($promo1 && Route::has('promotion.show')) ? route('promotion.show',['promotion'=>$p1Slug ?: '#']) : '#']"
        :image="$p1Image"
        :imageAlt="$p1Name ?? 'Promo'"
    />

    {{-- landing category kedua --}}
    <livewire:components.landing-category :category="$categoriesSecond" />

    {{-- promotion banner 2 --}}
    @php
        $p2Name  = data_get($promo2, 'name', 'Welcome to Our Store!');
        $p2Desc  = data_get($promo2, 'description', 'Temukan penawaran terbaik dan produk terbaru kami.');
        $p2Slug  = data_get($promo2, 'landing_slug', '');
        $p2Image = data_get($promo2, 'image') ?: asset('images/default-hero.webp');
    @endphp

    <livewire:components.promotion-hero
        :title="$p2Name"
        :subtitle="$p2Desc"
        :primary="['label' => $promo2 ? 'Beli Sekarang' : 'Jelajahi Sekarang','href' => $promo2 ? route('category',['category'=>$p2Slug]) : route('home')]"
        :secondary="['label' => $promo2 ? 'Katalog' : 'Bantuan','href' => $promo2 ? (Route::has('promotion.show') ? route('promotion.show',['promotion'=>$p2Slug ?: '#']) : '#') : route('home')]"
        :image="$p2Image"
        :imageAlt="$p2Name ?? 'Promosi Terbaru'"
    />

    {{-- Produk rekomendasi --}}
    <livewire:components.product-carousel
        class="mt-10 mb-10"
        title="Rekomendasi produk untuk anda"
        description="Kami selalu berusaha untuk memberikan produk terbaik untuk memenuhi kebutuhan anda."
        :data="$recommendedProducts"
    />
@endsection
