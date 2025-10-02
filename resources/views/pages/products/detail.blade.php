@extends('layouts.app')

{{-- Asumsi data produk dimuat ke variabel $product --}}
@php
    // Data dummy untuk contoh tampilan
    $product = [
        'id' => 1,
        'name' => 'Ultra Fast Charging Power Bank 20000mAh',
        'category' => 'Aksesoris Daya',
        'price' => 'Rp599.000',
        'rating' => 4.7,
        'reviews_count' => 125,
        'stock' => 50,
        'image_url' => asset('images/galaxy-z-flip7-share-image.png'), // Ganti dengan URL gambar sebenarnya
        'description' =>
            'Power bank canggih dengan kapasitas 20000mAh, dilengkapi teknologi pengisian cepat 65W. Ideal untuk mengisi ulang laptop modern dan smartphone dengan kecepatan maksimal. Desain ramping dan ringan, mudah dibawa bepergian. Garansi resmi 1 tahun.',
        'specs' => [
            'Kapasitas' => '20000 mAh',
            'Output Maks' => '65W USB-C PD',
            'Port' => '2x USB-C, 1x USB-A',
            'Warna' => 'Hitam, Putih, Biru Navy',
        ],
        'colors' => [
            ['name' => 'Hitam', 'code' => '#000000'],
            ['name' => 'Putih', 'code' => '#FFFFFF'],
            ['name' => 'Biru', 'code' => '#1D4ED8'],
        ],
    ];

    $reviews = [
        [
            'author' => 'Ahmad R.',
            'date' => '2 minggu lalu',
            'rating' => 5,
            'title' => 'Cepat & Andal!',
            'body' => 'Pengisian cepatnya luar biasa. Bisa mengisi laptop saya saat bepergian. Kualitas build terasa premium, sangat direkomendasikan.',
        ],
        [
            'author' => 'Bunga S.',
            'date' => '1 bulan lalu',
            'rating' => 4,
            'title' => 'Bagus, tapi agak berat',
            'body' => 'Sesuai deskripsi, power outputnya mantap. Hanya saja, ukurannya sedikit lebih besar dari yang saya bayangkan, tapi performanya tidak diragukan.',
        ],
    ];
@endphp

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <nav class="text-sm mb-6" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center">
                    <a href="#" class="text-gray-500 hover:text-gray-700">Beranda</a>
                    <span class="mx-2 text-gray-400">/</span>
                </li>
                <li class="flex items-center">
                    <a href="#" class="text-gray-500 hover:text-gray-700">{{ $product['category'] }}</a>
                    <span class="mx-2 text-gray-400">/</span>
                </li>
                <li class="text-gray-900 font-medium truncate max-w-xs sm:max-w-none">{{ $product['name'] }}</li>
            </ol>
        </nav>

        {{-- Product Main Layout (Desktop: 2 Kolom, Mobile: Stacked) --}}
        <div class="lg:grid lg:grid-cols-2 lg:gap-12 xl:gap-16">

            {{-- KOLOM 1: Gambar Produk dan Galeri (Sticky di Desktop) --}}
            <div class="lg:sticky lg:top-8 self-start">
                <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden border border-gray-100">
                    <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] }}"
                        class="w-full h-full object-contain p-8 sm:p-12" loading="eager">
                </div>

                {{-- Galeri Thumbnail (Opsional, hanya untuk visual) --}}
                <div class="hidden sm:flex mt-4 gap-3 overflow-x-auto py-8 px-4">
                    {{-- Ganti dengan loop galeri produk Anda --}}
                    @for ($i = 0; $i < 4; $i++)
                        <div
                            class="size-16 bg-gray-100 rounded-lg cursor-pointer hover:ring-2 hover:ring-zinc-500 transition">
                            <img src="{{ $product['image_url'] }}" alt="Thumbnail {{ $i + 1 }}"
                                class="w-full h-full object-contain p-2">
                        </div>
                    @endfor
                </div>
            </div>

            {{-- KOLOM 2: Detail Informasi dan CTA --}}
            <div class="mt-8 lg:mt-0">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight mb-2">{{ $product['name'] }}</h1>

                {{-- Rating dan Ulasan --}}
                <div class="flex items-center space-x-2 mb-4">
                    <span class="text-yellow-500 text-lg">
                        {{-- Contoh komponen rating dari Flux (jika tersedia) --}}
                        {{ str_repeat('★', floor($product['rating'])) }}{{ str_repeat('☆', 5 - floor($product['rating'])) }}
                    </span>
                    <span class="text-gray-600 text-sm font-medium">{{ $product['rating'] }}</span>
                    <span class="text-gray-400">|</span>
                    <a href="#reviews" class="text-sm text-zinc-600 hover:text-zinc-800 font-medium">
                        {{ $product['reviews_count'] }} Ulasan
                    </a>
                </div>

                {{-- Harga --}}
                <div class="py-4 border-y border-gray-100 mb-6">
                    <p class="text-3xl font-bold text-zinc-700">{{ $product['price'] }}</p>
                    @if ($product['stock'] > 10)
                        <p class="mt-1 text-sm text-green-600 font-medium">Stok Tersedia ({{ $product['stock'] }}+)</p>
                    @elseif ($product['stock'] > 0)
                        <p class="mt-1 text-sm text-orange-600 font-medium">Stok Terbatas ({{ $product['stock'] }} unit)
                        </p>
                    @else
                        <p class="mt-1 text-sm text-red-600 font-medium">Stok Habis</p>
                    @endif
                </div>

                {{-- Opsi Produk (Warna/Varian) --}}
                <div class="mb-8" x-data="{ selectedColor: '{{ $product['colors'][0]['name'] ?? '' }}' }">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Warna: <span x-text="selectedColor"
                            class="font-normal text-gray-600"></span></h3>
                    <div class="flex space-x-3">
                        @foreach ($product['colors'] as $color)
                            <button type="button" x-on:click="selectedColor = '{{ $color['name'] }}'"
                                :class="selectedColor === '{{ $color['name'] }}' ? 'ring-2 ring-offset-2 ring-zinc-500' :
                                    'hover:ring-2 hover:ring-gray-300'"
                                class="size-8 rounded-full border border-gray-300 transition duration-150 shadow-sm"
                                style="background-color: {{ $color['code'] }}">
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Action Buttons (Sticky di Mobile) --}}
                <div
                    class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 lg:static lg:p-0 lg:border-t-0 lg:shadow-none">
                    <div class="flex gap-4 max-w-3xl lg:max-w-full mx-auto">
                        <flux:button variant="outline" color="zinc" href="{{ route('product.wislist', ['id'=>$product['id']]) }}"
                            class="!rounded-full px-6 py-3 flex-1 text-base font-semibold">
                            Tambah ke Wishlist
                        </flux:button>
                        <flux:button variant="primary" color="zinc" href="{{ route('product.add_cart', ['id'=>$product['id']]) }}"
                            class="!rounded-full px-6 py-3 flex-1 text-base font-semibold" icon="shopping-cart">
                            Tambah ke Keranjang
                        </flux:button>
                    </div>
                </div>

                {{-- Deskripsi dan Spesifikasi --}}
                <div class="mt-12 space-y-8">
                    {{-- Deskripsi --}}
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Deskripsi Produk</h3>
                        <p class="text-gray-700 leading-relaxed">{{ $product['description'] }}</p>
                    </div>

                    {{-- Spesifikasi Teknis --}}
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Spesifikasi Teknis</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($product['specs'] as $key => $value)
                                        <tr class="bg-white">
                                            <th scope="row" class="py-2 px-0 font-medium text-gray-900 w-1/3">
                                                {{ $key }}
                                            </th>
                                            <td class="py-2 px-0 text-gray-700 w-2/3">
                                                {{ $value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Ulasan Pelanggan --}}
        <div id="reviews" class="pt-16 pb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-200 pb-3">Ulasan Pelanggan
                ({{ $product['reviews_count'] }})</h2>

            <div class="grid lg:grid-cols-3 gap-10">

                {{-- KOLOM 1: Ringkasan Rating & Tulis Ulasan --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-8 bg-gray-50 p-6 rounded-xl shadow-inner">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Ringkasan Rating</h3>

                        {{-- Rata-rata Besar --}}
                        <div class="flex items-center space-x-3 mb-4">
                            <p class="text-5xl font-extrabold text-gray-900">{{ $product['rating'] }}</p>
                            <div>
                                <span class="text-yellow-500 text-2xl">
                                    {{ str_repeat('★', floor($product['rating'])) }}{{ str_repeat('☆', 5 - floor($product['rating'])) }}
                                </span>
                                <p class="text-sm text-gray-500">{{ $product['reviews_count'] }} Total Ulasan</p>
                            </div>
                        </div>

                        {{-- Bar Distribusi Rating (Contoh Sederhana) --}}
                        @php
                            // Asumsi distribusi rating (hanya untuk visual)
                            $ratingDistribution = [5 => 70, 4 => 20, 3 => 5, 2 => 3, 1 => 2];
                        @endphp
                        <div class="space-y-1">
                            @foreach ($ratingDistribution as $star => $percentage)
                                <div class="flex items-center space-x-3">
                                    <span class="text-xs text-gray-600 w-4">{{ $star }} ★</span>
                                    <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                        <div class="h-2 bg-yellow-400 rounded-full" style="width: {{ $percentage }}%">
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-600 w-8 text-right">{{ $percentage }}%</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Tombol Tulis Ulasan --}}
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <p class="text-sm text-gray-700 mb-3">Bagikan pengalaman Anda tentang produk ini.</p>
                            <flux:button variant="primary" color="zinc"
                                class="!rounded-full w-full py-3 text-base font-semibold">
                                Tulis Ulasan Anda
                            </flux:button>
                        </div>
                    </div>
                </div>

                {{-- KOLOM 2 & 3: Filter dan Daftar Ulasan --}}
                <div class="lg:col-span-2">

                    {{-- Filter dan Sortir --}}
                    <div
                        class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-4 mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">{{ count($reviews) }} Ulasan Terkini</h3>
                        <div class="flex items-center space-x-3 mt-3 sm:mt-0">
                            <label for="sort_reviews" class="text-sm text-gray-600 whitespace-nowrap">Urutkan:</label>
                            <select id="sort_reviews"
                                class="border-gray-300 rounded-full text-sm focus:border-zinc-500 focus:ring-zinc-500">
                                <option>Terbaru</option>
                                <option>Rating Tertinggi</option>
                                <option>Rating Terendah</option>
                            </select>
                        </div>
                    </div>

                    {{-- Daftar Ulasan --}}
                    <div class="space-y-8">
                        @foreach ($reviews as $review)
                            <div class="border-b border-gray-100 pb-8 last:border-b-0">
                                {{-- Header Ulasan --}}
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-3">
                                        <div
                                            class="size-8 rounded-full bg-zinc-100 grid place-items-center text-zinc-600 font-semibold text-sm">
                                            {{ substr($review['author'], 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">{{ $review['author'] }}</p>
                                            <span class="text-xs text-gray-500">{{ $review['date'] }}</span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-yellow-500">
                                        {{ str_repeat('★', $review['rating']) }}{{ str_repeat('☆', 5 - $review['rating']) }}
                                    </div>
                                </div>

                                {{-- Body Ulasan --}}
                                <h4 class="text-base font-bold text-gray-800 mt-2 mb-1">{{ $review['title'] }}</h4>
                                <p class="text-gray-700 leading-relaxed text-sm">{{ $review['body'] }}</p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Tombol Muat Lebih Banyak (jika ada pagination) --}}
                    <div class="mt-6 text-center">
                        <flux:button variant="outline" color="zinc" class="!rounded-full px-6 py-3">
                            Muat Ulasan Lainnya ({{ $product['reviews_count'] - count($reviews) }})
                        </flux:button>
                    </div>

                </div>

            </div>
        </div>

    </div>
@endsection
