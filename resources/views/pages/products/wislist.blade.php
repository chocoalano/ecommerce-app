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
            {{-- Kondisi Wishlist Kosong --}}
            <div
                class="flex flex-col items-center justify-center py-20 bg-gray-100 rounded-xl border border-gray-200 shadow-md">
                <flux:icon.heart class="w-16 h-16 text-gray-400 mb-4" />
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Daftar Keinginan Anda Kosong</h2>
                <p class="text-gray-500 mb-6">Tambahkan item yang Anda suka agar tidak terlupakan!</p>
                <flux:button href="{{ route('category') }}" variant="primary" color="zinc" class="!rounded-full px-6 py-3">
                    Jelajahi Produk
                </flux:button>
            </div>
        @else
            {{-- List Item Wishlist --}}
            <div class="lg:grid lg:grid-cols-1 xl:grid-cols-2 lg:gap-8">
                <div class="space-y-6 lg:col-span-full xl:col-span-1">
                    @foreach ($wishlistItems as $item)
                        <div
                            class="flex flex-col sm:flex-row items-start bg-gray-100 p-4 sm:p-6 rounded-2xl border border-gray-100 transition hover:shadow-xl">

                            {{-- Gambar Produk --}}
                            <div class="size-20 sm:size-24 flex-shrink-0 rounded-lg overflow-hidden bg-white">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                    class="w-full h-full object-contain p-2">
                            </div>

                            {{-- Detail dan Aksi --}}
                            <div class="ml-0 sm:ml-6 flex-grow w-full mt-4 sm:mt-0">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <a href="#"
                                            class="text-lg font-semibold text-gray-900 hover:text-zinc-700 transition">{{ $item['name'] }}</a>

                                        {{-- Status Stok --}}
                                        @if ($item['in_stock'])
                                            <p class="text-sm font-medium text-green-600 mt-1 flex items-center">
                                                <flux:icon.check-circle class="w-4 h-4 mr-1" /> Tersedia
                                            </p>
                                        @else
                                            <p class="text-sm font-medium text-zinc-600 mt-1 flex items-center">
                                                <flux:icon.x-circle class="w-4 h-4 mr-1" /> Habis
                                            </p>
                                        @endif
                                    </div>
                                    <div class="text-right ml-4">
                                        <p class="text-xl font-bold text-zinc-700">
                                            {{ $formatRupiah($item['price']) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Tombol Aksi --}}
                                <div class="flex items-center space-x-3 mt-4">
                                    <flux:button variant="primary" color="zinc" icon="shopping-cart"
                                        class="!rounded-full text-sm py-2 px-4">
                                        Tambah ke Keranjang
                                    </flux:button>

                                    {{-- Tombol Hapus (Menggunakan gaya link yang diperbaiki) --}}
                                    <flux:button variant="outline"
                                        class="text-sm text-gray-600 border-none shadow-none px-0 py-0 hover:bg-transparent hover:text-zinc-600 transition">
                                        <flux:icon.trash class="w-4 h-4 mr-1" /> Hapus
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- KOLOM 2: Banner Promosi (Hanya muncul di XL) --}}
                <div class="hidden xl:block">
                    {{-- Banner Promosi yang Lebih Informatif dan Stylish (Gaya Diskon/Sale) --}}
                    <div
                        class="sticky top-8 bg-gradient-to-br from-zinc-500 to-zinc-700 p-8 rounded-3xl shadow-2xl border border-zinc-400/50 h-96 flex flex-col justify-between text-white transform transition duration-300 hover:scale-[1.01] hover:shadow-zinc-500/50">
                        <div class="mb-6">
                            <flux:icon.tag class="w-10 h-10 text-zinc-300 mb-3 drop-shadow-lg" />
                            <h3 class="text-4xl font-black leading-snug tracking-tight">
                                Diskon Eksklusif Hari Ini
                            </h3>
                            <p class="text-base mt-3 text-zinc-100">
                                Pilih item dari daftar keinginan Anda dan dapatkan **ekstra diskon 10%** saat *checkout* sekarang!
                            </p>
                        </div>

                        <div class="mt-auto">
                            <div class="flex items-center space-x-4 mb-4 p-2 bg-black/20 rounded-lg">
                                <flux:icon.clock class="w-5 h-5 text-white flex-shrink-0" />
                                <p class="text-sm font-semibold">Berlaku hanya 24 jam</p>
                            </div>
                            <flux:button href="#" variant="primary" color="zinc"
                                class="!rounded-full w-full py-3 font-extrabold">
                                Klaim Diskon Sekarang
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

@endsection
