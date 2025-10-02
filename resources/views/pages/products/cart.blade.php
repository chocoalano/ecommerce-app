@extends('layouts.app')

@section('content')

    @php
        // --- DATA DUMMY KERANJANG BELANJA ---
        $cartItems = [
            [
                'id' => 1,
                'name' => 'Ultra Fast Charging Power Bank 20000mAh',
                'variant' => 'Warna: Hitam',
                'price' => 599000,
                'quantity' => 2,
                'stock' => 5,
                'image_url' => asset('images/galaxy-z-flip7-share-image.png'),
            ],
            [
                'id' => 2,
                'name' => 'Galaxy Watch 8 Classic',
                'variant' => 'Ukuran: 45mm',
                'price' => 5899000,
                'quantity' => 1,
                'stock' => 10,
                'image_url' => asset('images/galaxy-z-flip7-share-image.png'),
            ],
        ];

        // Logika perhitungan
        $subtotal = collect($cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);
        $shippingFee = 25000;
        $total = $subtotal + $shippingFee;

        // Helper untuk format Rupiah
        $formatRupiah = fn($amount) => 'Rp' . number_format($amount, 0, ',', '.');
    @endphp

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight mb-8">Keranjang Belanja Anda</h1>

        @if (empty($cartItems))
            {{-- Kondisi Keranjang Kosong --}}
            <div
                class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-xl border border-gray-200 shadow-md">
                <flux:icon.shopping-cart class="w-16 h-16 text-gray-400 mb-4" />
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Keranjang Anda Kosong</h2>
                <p class="text-gray-500 mb-6">Yuk, temukan produk menarik yang ingin Anda beli!</p>
                <flux:button href="{{ route('category') }}" variant="primary" color="zinc" class="!rounded-full px-6 py-3">
                    Mulai Belanja Sekarang
                </flux:button>
            </div>
        @else
            {{-- Grid Utama: Daftar Item (2/3) dan Ringkasan (1/3) --}}
            <div class="lg:grid lg:grid-cols-3 lg:gap-12 xl:gap-16">

                {{-- KOLOM 1: Daftar Item Keranjang --}}
                <div class="lg:col-span-2 space-y-6">
                    @foreach ($cartItems as $item)
                        <div class="flex items-start bg-gray-100 p-4 sm:p-6 rounded-xl transition hover:shadow-md">
                            {{-- Gambar Produk --}}
                            <div class="size-20 sm:size-28 flex-shrink-0 rounded-lg overflow-hidden bg-white">
                                <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                    class="w-full h-full object-contain p-2">
                            </div>

                            {{-- Detail dan Kontrol --}}
                            <div class="ml-4 flex-grow">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <a href="#"
                                            class="text-lg font-semibold text-gray-900 hover:text-zinc-700 transition">{{ $item['name'] }}</a>
                                        <p class="text-sm text-gray-500 mt-1">{{ $item['variant'] }}</p>
                                        @if ($item['quantity'] > $item['stock'])
                                            <p class="text-xs text-red-600 font-medium mt-1">Stok tidak mencukupi!</p>
                                        @endif
                                    </div>
                                    <div class="text-right ml-4">
                                        {{-- Harga Total Per Item --}}
                                        <p class="text-lg font-bold text-zinc-700">
                                            {{ $formatRupiah($item['price'] * $item['quantity']) }}
                                        </p>
                                        {{-- Harga Satuan --}}
                                        <p class="text-xs text-gray-400 line-through">
                                            {{ $formatRupiah($item['price']) }} / item
                                        </p>
                                    </div>
                                </div>

                                {{-- Kontrol Kuantitas dan Aksi --}}
                                <div class="flex items-center justify-between mt-4 sm:mt-6">
                                    {{-- Kontrol Kuantitas (Asumsi menggunakan input standar) --}}
                                    {{-- <div class="flex items-center space-x-2">
                                        <label for="qty-{{ $item['id'] }}" class="sr-only">Kuantitas</label>
                                        <input type="number" id="qty-{{ $item['id'] }}" value="{{ $item['quantity'] }}"
                                            min="1" max="{{ $item['stock'] }}"
                                            class="w-16 border-gray-300 rounded-lg text-center text-sm focus:border-zinc-500 focus:ring-zinc-500 p-2">
                                    </div> --}}

                                    <div class="flex items-center space-x-2">
                                        <label for="Quantity" class="sr-only"> Quantity </label>

                                        <div class="flex items-center gap-1">
                                            <button type="button"
                                                class="size-10 leading-10 text-gray-600 bg-white transition hover:opacity-75">
                                                &minus;
                                            </button>

                                            <input type="number" id="Quantity" value="1"
                                                class="h-10 w-24 rounded-sm border-gray-200 bg-white [-moz-appearance:_textfield] sm:text-sm [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none" />

                                            <button type="button"
                                                class="size-10 leading-10 text-gray-600 bg-white transition hover:opacity-75">
                                                &plus;
                                            </button>
                                        </div>
                                    </div>


                                    {{-- Tombol Hapus --}}
                                    {{-- FIX: Mengganti variant="link" dengan variant="outline" dan menimpa kelas agar terlihat seperti link. --}}
                                    <flux:button variant="outline"
                                        class="text-sm text-red-600 border-none shadow-none px-0 py-0 hover:bg-transparent">
                                        <flux:icon.trash class="w-4 h-4 mr-1" /> Hapus
                                    </flux:button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- KOLOM 2: Ringkasan Pesanan (Sticky di Desktop) --}}
                <div class="lg:col-span-1 mt-8 lg:mt-0">
                    <div class="sticky top-8 bg-gray-100 p-6 rounded-xl border border-gray-200">
                        <h3 class="text-xl font-bold text-gray-900 mb-4 pb-3 border-b border-gray-200">Ringkasan Pesanan
                        </h3>

                        <dl class="space-y-3 text-sm text-gray-700">
                            <div class="flex justify-between">
                                <dt>Subtotal ({{ count($cartItems) }} Item)</dt>
                                <dd class="font-medium">{{ $formatRupiah($subtotal) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt>Biaya Pengiriman</dt>
                                <dd class="font-medium">{{ $formatRupiah($shippingFee) }}</dd>
                            </div>
                            <div class="flex justify-between pt-4 border-t border-gray-200">
                                <dt class="text-lg font-bold">Total Pembayaran</dt>
                                <dd class="text-lg font-bold text-zinc-700">{{ $formatRupiah($total) }}</dd>
                            </div>
                        </dl>

                        <flux:button href="{{ route('product.checkout') }}" variant="primary" color="zinc" icon="credit-card"
                            class="!rounded-full w-full py-3 text-base font-semibold mt-6">
                            Lanjut ke Pembayaran
                        </flux:button>

                        <p class="text-xs text-gray-500 mt-4 text-center">Biaya pengiriman dihitung saat *checkout*.</p>
                    </div>
                </div>

            </div>
        @endif
    </div>

@endsection
