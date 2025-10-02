@extends('layouts.app')

@section('content')

@php
    // --- DATA DUMMY KERANJANG UNTUK RINGKASAN ---
    $cartItems = [
        [
            'name' => 'Galaxy S25 Ultra',
            'quantity' => 1,
            'price' => 22999000,
        ],
        [
            'name' => 'Galaxy Buds X Pro',
            'quantity' => 1,
            'price' => 2499000,
        ],
    ];

    // --- KALKULASI RINGKASAN ---
    $subtotal = array_sum(array_map(fn($item) => $item['quantity'] * $item['price'], $cartItems));
    $shippingFee = 25000;
    $tax = $subtotal * 0.10; // 10% PPN
    $total = $subtotal + $shippingFee + $tax;

    // Helper untuk format Rupiah
    $formatRupiah = fn($amount) => 'Rp' . number_format($amount, 0, ',', '.');
@endphp

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight mb-8">Selesaikan Pembelian Anda</h1>

    <div class="lg:grid lg:grid-cols-3 lg:gap-12">

        {{-- KOLOM KIRI: FORMULIR CHECKOUT (2/3 Lebar) --}}
        <div class="lg:col-span-2 space-y-10">

            {{-- Breadcrumb/Langkah (Contoh Sederhana) --}}
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <span class="text-zinc-700 font-semibold">1. Pengiriman</span>
                <flux:icon.chevron-right class="w-4 h-4" />
                <span>2. Pembayaran</span>
                <flux:icon.chevron-right class="w-4 h-4" />
                <span>3. Konfirmasi</span>
            </div>

            {{-- 1. INFORMASI PENGIRIMAN --}}
            <section class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <flux:icon.map-pin class="w-6 h-6 mr-3 text-zinc-500"/> Alamat Pengiriman
                </h2>
                <form class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input name="first_name" label="Nama Depan" placeholder="Misalnya: Budi" required />
                        <flux:input name="last_name" label="Nama Belakang" placeholder="Misalnya: Santoso" required />
                    </div>
                    <flux:input name="phone" type="tel" label="Nomor Telepon" placeholder="Contoh: 081234567890" required />
                    <flux:input name="address" label="Alamat Lengkap" placeholder="Jalan Raya No. 12" required />
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <flux:input name="city" label="Kota/Kabupaten" required />
                        <flux:input name="province" label="Provinsi" required />
                        <flux:input name="zip" label="Kode Pos" required />
                    </div>
                </form>
            </section>

            {{-- 2. METODE PEMBAYARAN --}}
            <section class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <flux:icon.credit-card class="w-6 h-6 mr-3 text-zinc-500"/> Pilih Metode Pembayaran
                </h2>
                <div class="space-y-4">
                    {{-- Pilihan Pembayaran (Gaya Kartu) --}}
                    @foreach (['Bank Transfer', 'Kartu Kredit / Debit', 'E-Wallet (Gopay/OVO)', 'COD (Bayar di Tempat)'] as $method)
                        <label for="{{ Str::slug($method) }}" class="flex items-center justify-between p-4 border border-gray-200 rounded-xl cursor-pointer hover:border-zinc-500 transition duration-150">
                            <div class="flex items-center space-x-3">
                                <input type="radio" id="{{ Str::slug($method) }}" name="payment_method" value="{{ $method }}" class="size-5 text-zinc-600 focus:ring-zinc-500">
                                <span class="font-semibold text-gray-800">{{ $method }}</span>
                            </div>
                            @if ($method === 'Kartu Kredit / Debit')
                                <flux:icon.credit-card class="w-8 h-8 text-gray-400" />
                            @elseif ($method === 'E-Wallet (Gopay/OVO)')
                                <flux:icon.device-phone-mobile class="w-6 h-6 text-green-500" />
                            @endif
                        </label>
                    @endforeach
                </div>
            </section>

            {{-- Tombol Lanjut (Hanya muncul di mobile) --}}
            <div class="lg:hidden mt-8">
                 <flux:button variant="primary" color="zinc" icon:trailing="lock-closed" class="!rounded-full w-full py-3">
                    Bayar Sekarang ({{ $formatRupiah($total) }})
                </flux:button>
            </div>
        </div>

        {{-- KOLOM KANAN: RINGKASAN PESANAN (1/3 Lebar) --}}
        <div class="mt-10 lg:mt-0 lg:col-span-1">
            <div class="sticky top-8 bg-white p-6 rounded-2xl shadow-xl border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-4">Ringkasan Pesanan</h2>

                {{-- Daftar Item --}}
                <div class="space-y-4 mb-6">
                    @foreach ($cartItems as $item)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-700">{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                            <span class="font-medium text-gray-800">{{ $formatRupiah($item['quantity'] * $item['price']) }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Subtotal dan Biaya --}}
                <div class="space-y-3 pt-4 border-t border-dashed">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal Produk:</span>
                        <span>{{ $formatRupiah($subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Biaya Pengiriman:</span>
                        <span>{{ $formatRupiah($shippingFee) }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Pajak (PPN 10%):</span>
                        <span>{{ $formatRupiah($tax) }}</span>
                    </div>
                </div>

                {{-- Total --}}
                <div class="flex justify-between font-bold text-lg mt-6 pt-4 border-t border-gray-300">
                    <span class="text-gray-900">Total Pembayaran:</span>
                    <span class="text-red-600">{{ $formatRupiah($total) }}</span>
                </div>

                {{-- Tombol Lanjut (Hanya muncul di desktop) --}}
                <div class="hidden lg:block mt-6">
                    <flux:button variant="primary" color="zinc" icon:trailing="lock-closed" class="!rounded-full w-full py-3 font-bold" href="{{ route('product.transaction') }}">
                        Bayar Sekarang ({{ $formatRupiah($total) }})
                    </flux:button>
                </div>

                {{-- Jaminan Keamanan --}}
                <div class="flex items-center justify-center space-x-2 mt-4 text-xs text-gray-500">
                    <flux:icon.shield-check class="w-4 h-4 text-green-500"/>
                    <p>Pembayaran aman & terenkripsi.</p>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
