@extends('layouts.app')

@section('content')
@php
    // Default nilai bantu
    $defaultId = $product->id;
    $currencyCode = $product->currency ?? 'IDR';
    $stock = (int) ($product->stock ?? 0);
    $isOutOfStock = $stock <= 0;

    // Rating dari relasi reviews yang sudah di-load
    $avgRating = (float) ($product->reviews()->avg('rating') ?? 0);
    $avgInt = (int) floor($avgRating);
    $reviewsCnt = (int) $product->reviews->count();

    // Harga dari model (base_price)
    $basePrice = (float) ($product->base_price ?? 0);
    $priceFormatted = $basePrice > 0 ? 'Rp ' . number_format($basePrice, 0, ',', '.') : null;
@endphp

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

    {{-- Dynamic Breadcrumb dengan Auto-Generation --}}
    @livewire('components.auto-breadcrumb')

    {{-- Main --}}
    <div class="lg:grid lg:grid-cols-2 lg:gap-12 xl:gap-16">
        {{-- Galeri --}}
        <div class="lg:sticky lg:top-8 self-start">
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden border border-gray-100">
                <img src="{{ asset('storage/'.$product->primaryImageUrl ?? 'images/smartphone.png') }}" alt="{{ $product->name }}"
                     class="w-full h-full object-contain p-8 sm:p-12" loading="eager">
            </div>

            @if($product->media->count() > 1)
            <div class="hidden sm:flex mt-4 gap-3 overflow-x-auto py-2">
                @foreach ($product->media as $idx => $media)
                    <div class="size-16 bg-gray-100 rounded-lg cursor-pointer ring-2 ring-transparent hover:ring-zinc-500 transition border border-gray-200">
                        <img src="{{ asset('storage/'.$media->url) }}" alt="{{ $media->alt_text ?? 'Thumbnail ' . ($idx + 1) }}" class="w-full h-full object-contain p-2">
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Detail + CTA --}}
        <div class="mt-8 lg:mt-0">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight mb-2">{{ $product->name }}</h1>

            {{-- Rating + jumlah ulasan --}}
            <div class="flex items-center space-x-2 mb-4">
                <div class="flex items-center">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $avgInt)
                            <svg class="w-4 h-4 text-yellow-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
                                <path d="M20.924 7.625a1.523 1.523 0 0 0-1.396-1.028H13.765l-2.427-4.896a1.523 1.523 0 0 0-2.78 0L6.035 6.597H1.366A1.523 1.523 0 0 0 .686 7.828l4.47 4.787-1.155 6.4a1.523 1.523 0 0 0 2.3 1.637L12 17.276l5.46 3.092a1.523 1.523 0 0 0 2.3-1.637l-1.155-6.4 4.47-4.787Z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
                                <path d="M20.924 7.625a1.523 1.523 0 0 0-1.396-1.028H13.765l-2.427-4.896a1.523 1.523 0 0 0-2.78 0L6.035 6.597H1.366A1.523 1.523 0 0 0 .686 7.828l4.47 4.787-1.155 6.4a1.523 1.523 0 0 0 2.3 1.637L12 17.276l5.46 3.092a1.523 1.523 0 0 0 2.3-1.637l-1.155-6.4 4.47-4.787Z"/>
                            </svg>
                        @endif
                    @endfor
                </div>
                <span class="text-gray-900 text-sm font-semibold">{{ number_format($avgRating, 1) }}</span>
                <span class="text-gray-400">|</span>
                <a href="#reviews" class="text-sm text-gray-600 hover:text-zinc-800 font-medium">
                    {{ $reviewsCnt }} Ulasan
                </a>
            </div>

            {{-- Harga + stok --}}
            <div class="py-4 border-y border-gray-200 mb-6">
                @if($priceFormatted)
                    <p class="text-4xl font-extrabold text-gray-900">{{ $priceFormatted }}</p>
                @endif

                @if ($stock > 10)
                    <div class="mt-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-medium text-green-800">
                                Stok Tersedia ({{ $stock }}+)
                            </p>
                        </div>
                    </div>
                @elseif ($stock > 0)
                    <div class="mt-3 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-orange-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.299 2.56-1.299 3.325 0l3.5 5.927c.765 1.299-.184 2.974-1.662 2.974H6.418c-1.478 0-2.427-1.675-1.662-2.974l3.5-5.927z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-medium text-orange-800">
                                Stok Terbatas ({{ $stock }} unit)
                            </p>
                        </div>
                    </div>
                @else
                    <div class="mt-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm font-medium text-red-800">
                                Stok Habis
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Action Buttons (Wishlist + Add to Cart) --}}
            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 lg:static lg:p-0 lg:border-t-0 lg:shadow-none z-10" id="productPage" data-stock="{{ $stock }}" data-is-out-of-stock="{{ $isOutOfStock ? 'true' : 'false' }}">
                <div class="flex flex-col sm:flex-row gap-4 max-w-3xl lg:max-w-full mx-auto">
                    {{-- Wishlist --}}
                    <a href="#"
                        class="inline-flex items-center justify-center w-full sm:flex-1 px-6 py-3 text-base font-semibold text-center text-gray-900
                                border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition duration-300">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-.318-.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        Tambah ke Wishlist
                    </a>

                    {{-- Add to Cart Form (progressive: works with or without JS) --}}
                    <form id="add-to-cart-form"
                          action="{{ route('cart.items.store') }}"
                          method="POST"
                          class="contents"
                          data-ajax="true">
                        @csrf

                        {{-- Hidden fields --}}
                        <input type="hidden" name="currency" value="{{ $currencyCode }}">
                        <input type="hidden" id="product_id" name="product_id" value="{{ $defaultId }}">

                        <div class="flex flex-col sm:flex-row items-stretch gap-3">
                            {{-- Qty stepper --}}
                            <div class="flex items-center justify-between sm:justify-start border border-gray-300 rounded-full overflow-hidden sm:flex-none">
                                <button type="button" class="px-4 py-3 text-gray-700 hover:bg-gray-100" data-qty-dec>-</button>
                                <input type="number" name="quantity" id="qty" min="1" value="1"
                                    class="w-16 text-center outline-none border-0 text-base font-medium"
                                    {{ $isOutOfStock ? 'disabled' : '' }}>
                                <button type="button" class="px-4 py-3 text-gray-700 hover:bg-gray-100" data-qty-inc>+</button>
                            </div>
                            {{-- Submit --}}
                            <button type="submit"
                                class="inline-flex items-center justify-center flex-1 w-full px-6 py-3 text-base font-semibold text-center text-white
                                    {{ $isOutOfStock ? 'bg-gray-400 cursor-not-allowed' : 'bg-zinc-900 hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300' }}
                                    rounded-full transition duration-300"
                                {{ $isOutOfStock ? 'disabled' : '' }}
                                title="{{ $isOutOfStock ? 'Produk sedang habis stok' : 'Tambah ke keranjang (Ctrl+A)' }}">
                                @if($isOutOfStock)
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Stok Habis
                                @else
                                    <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    Tambah ke Keranjang
                                @endif
                            </button>
                        </div>

                    </form>
                </div>
                @if($isOutOfStock)
                    <p class="text-sm text-red-600 mt-2">Stok produk ini sedang habis.</p>
                @endif
            </div>

            {{-- Deskripsi & Spesifikasi --}}
            <div class="mt-12 space-y-8">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Deskripsi Singkat</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $product->short_desc }}</p>
                </div>

                @if($product->long_desc)
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Deskripsi Lengkap</h3>
                    <div class="text-gray-700 leading-relaxed prose prose-sm max-w-none">
                        {!! nl2br(e($product->long_desc)) !!}
                    </div>
                </div>
                @endif

                {{-- Informasi Produk --}}
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Informasi Produk</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <tbody class="divide-y divide-gray-200">
                                @if($product->sku)
                                <tr class="bg-white">
                                    <th scope="row" class="py-2 px-0 font-medium text-gray-900 w-1/3">SKU</th>
                                    <td class="py-2 px-0 text-gray-700 w-2/3">{{ $product->sku }}</td>
                                </tr>
                                @endif
                                @if($product->brand)
                                <tr class="bg-white">
                                    <th scope="row" class="py-2 px-0 font-medium text-gray-900 w-1/3">Brand</th>
                                    <td class="py-2 px-0 text-gray-700 w-2/3">{{ $product->brand }}</td>
                                </tr>
                                @endif
                                @if($product->warranty_months)
                                <tr class="bg-white">
                                    <th scope="row" class="py-2 px-0 font-medium text-gray-900 w-1/3">Garansi</th>
                                    <td class="py-2 px-0 text-gray-700 w-2/3">{{ $product->warranty_months }} bulan</td>
                                </tr>
                                @endif
                                @if($product->weight_gram)
                                <tr class="bg-white">
                                    <th scope="row" class="py-2 px-0 font-medium text-gray-900 w-1/3">Berat</th>
                                    <td class="py-2 px-0 text-gray-700 w-2/3">{{ number_format($product->weight_gram) }} gram</td>
                                </tr>
                                @endif
                                @if($product->length_mm || $product->width_mm || $product->height_mm)
                                <tr class="bg-white">
                                    <th scope="row" class="py-2 px-0 font-medium text-gray-900 w-1/3">Dimensi</th>
                                    <td class="py-2 px-0 text-gray-700 w-2/3">
                                        {{ $product->length_mm ?? 0 }} x {{ $product->width_mm ?? 0 }} x {{ $product->height_mm ?? 0 }} mm
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr class="my-12 border-gray-200" />
    {{-- Ulasan Pelanggan Component --}}
    <livewire:components.product-reviews :product="$product" class="mt-10" />
    <hr class="my-12 border-gray-200" />
</div>
@if (!empty($relatedProducts))
@php
    $parsing = $relatedProducts->map(function ($p) {
                    $image = optional($p->primaryMedia)->url ?: asset('images/galaxy-z-flip7-share-image.png');
                    $rawPrice = $p->base_price ?? 0;

                    return [
                        'id'=> $p->id,
                        'sku' => $p->sku,
                        'title' => $p->name,
                        'image' => $image,
                        'price' => 'Rp'.number_format((float) $rawPrice, 0, ',', '.'),
                        'rating' => $p->avg_rating ? round($p->avg_rating, 1) : null,
                    ];
                });
@endphp
    <livewire:components.product-carousel title="Rekomendasi produk untuk anda"
        description="Kami selalu berusaha untuk memberikan produk terbaik untuk memenuhi kebutuhan anda."
        :data="$parsing->toArray()" />
@endif

@endsection
