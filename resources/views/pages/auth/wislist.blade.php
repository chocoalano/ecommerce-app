@extends('layouts.app')

@section('content')

    @php
        // --- DATA DUMMY WISHLIST ---
        $wishlistItems = [
            [
                'id' => 101,
                'name' => 'Galaxy S25 Ultra (Samsung.com only)',
                'price' => 22999000,
                'image_url' => asset('images/galaxy-z-flip7-share-image.png'),
                'in_stock' => true,
            ],
            [
                'id' => 102,
                'name' => 'Galaxy Buds X Pro',
                'price' => 2499000,
                'image_url' => asset('images/galaxy-z-flip7-share-image.png'),
                'in_stock' => false,
            ],
            [
                'id' => 103,
                'name' => 'Portable SSD T9 2TB',
                'price' => 3800000,
                'image_url' => asset('images/galaxy-z-flip7-share-image.png'),
                'in_stock' => true,
            ],
        ];

        // Helper untuk format Rupiah
        $formatRupiah = fn($amount) => 'Rp' . number_format($amount, 0, ',', '.');
    @endphp

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight mb-8">Daftar Keinginan (Wishlist)</h1>

        @if (empty($wishlistItems))
            {{-- Kondisi Wishlist Kosong (Flux Icons & Button diganti) --}}
            <div
                class="flex flex-col items-center justify-center py-20 bg-gray-100 rounded-xl border border-gray-200 shadow-md">
                {{-- SVG Heart Icon (Flowbite Style) --}}
                <svg class="w-16 h-16 text-gray-400 mb-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.5C12 3.8 9.5 2 6.5 2 3.5 2 1 4 1 6.5 1 9.3 4 11 12 19c8-8 11-9.7 11-12.5C23 4 20.5 2 17.5 2c-3 0-5.5 1.8-5.5 4.5z"/>
                </svg>
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Daftar Keinginan Anda Kosong</h2>
                <p class="text-gray-500 mb-6 text-center">Tambahkan item yang Anda suka agar tidak terlupakan!</p>
                {{-- Tombol (Flux Button diganti <a> Flowbite Primary) --}}
                <a href="{{ route('category') }}"
                    class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-center text-white
                           bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                    Jelajahi Produk
                </a>
            </div>
        @else
            {{-- List Item Wishlist dan Banner --}}
            <div class="lg:grid lg:grid-cols-1 xl:grid-cols-3 lg:gap-8">
                
                {{-- KOLOM 1: List Item Wishlist --}}
                <div class="space-y-6 lg:col-span-full xl:col-span-2">
                    @foreach ($wishlistItems as $item)
                        <div
                            class="flex flex-col sm:flex-row items-start bg-white p-4 sm:p-6 rounded-xl border border-gray-200 transition hover:shadow-lg">

                            {{-- Gambar Produk --}}
                            <div class="size-20 sm:size-24 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100 border border-gray-300">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                    class="w-full h-full object-contain p-2">
                            </div>

                            {{-- Detail dan Aksi --}}
                            <div class="ml-0 sm:ml-6 flex-grow w-full mt-4 sm:mt-0">
                                <div class="flex justify-between items-start w-full">
                                    <div class="pr-4">
                                        <a href="#"
                                            class="text-lg font-semibold text-gray-900 hover:text-zinc-700 transition line-clamp-2">{{ $item['name'] }}</a>

                                        {{-- Status Stok (Flowbite Badges) --}}
                                        @if ($item['in_stock'])
                                            <p class="text-sm font-medium text-green-600 mt-1 flex items-center">
                                                {{-- SVG Check Circle Icon --}}
                                                <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Tersedia
                                            </p>
                                        @else
                                            <p class="text-sm font-medium text-red-600 mt-1 flex items-center">
                                                {{-- SVG X Circle Icon --}}
                                                <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Habis
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right ml-4 flex-shrink-0">
                                        <p class="text-xl font-bold text-gray-900">
                                            {{ $formatRupiah($item['price']) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Tombol Aksi --}}
                                <div class="flex items-center space-x-3 mt-4 pt-4 border-t border-gray-200">
                                    {{-- Tombol Tambah ke Keranjang (Flowbite Primary) --}}
                                    <button type="button" @if(!$item['in_stock']) disabled @endif
                                        class="inline-flex items-center justify-center text-sm py-2 px-4 font-semibold text-center text-white
                                               bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300 disabled:bg-gray-400 disabled:cursor-not-allowed">
                                        {{-- SVG Shopping Cart Icon --}}
                                        <svg class="w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        Tambah ke Keranjang
                                    </button>

                                    {{-- Tombol Hapus (Gaya Link Merah) --}}
                                    <button type="button"
                                        class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-800 transition py-2 px-3 rounded-lg hover:bg-red-50/50">
                                        {{-- SVG Trash Icon --}}
                                        <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- KOLOM 2: Banner Promosi (Hanya muncul di XL) --}}
                <div class="mt-8 xl:mt-0 lg:col-span-1 xl:col-span-1">
                    {{-- Banner Promosi yang Lebih Informatif dan Stylish --}}
                    <div
                        class="sticky top-8 bg-gradient-to-br from-indigo-600 to-indigo-800 p-8 rounded-xl shadow-xl border border-indigo-500/50 h-full flex flex-col justify-between text-white">
                        
                        <div class="mb-6">
                            {{-- SVG Tag Icon --}}
                            <svg class="w-10 h-10 text-white mb-3 drop-shadow-md" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.5 2h5a1 1 0 0 1 1 1v2.5a1 1 0 0 1-1 1h-5a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zM9 10h6m-6 4h6m-6 4h4M4 7h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z"/>
                            </svg>
                            <h3 class="text-3xl font-extrabold leading-snug tracking-tight">
                                Diskon Eksklusif Hari Ini
                            </h3>
                            <p class="text-base mt-3 text-indigo-100">
                                Pilih item dari daftar keinginan Anda dan dapatkan **ekstra diskon 10%** saat *checkout* sekarang!
                            </p>
                        </div>

                        <div class="mt-auto">
                            <div class="flex items-center space-x-3 mb-4 p-2 bg-black/20 rounded-lg">
                                {{-- SVG Clock Icon --}}
                                <svg class="w-5 h-5 text-white flex-shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-semibold">Berlaku hanya 24 jam</p>
                            </div>
                            {{-- Tombol Klaim (Flowbite Primary/White) --}}
                            <a href="#"
                                class="inline-flex items-center justify-center w-full py-3 text-base font-extrabold text-center text-indigo-700
                                      bg-white rounded-full hover:bg-indigo-50 focus:ring-4 focus:ring-indigo-300 transition duration-300 shadow-md">
                                Klaim Diskon Sekarang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection