@extends('layouts.app')

@section('content')
    <div class="bg-gray-50 min-h-screen py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 sm:mb-12">
                {{-- Icon Sukses (Contoh SVG dari Flowbite/Heroicons) --}}
                <svg class="w-16 h-16 text-green-600 mx-auto mb-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.5 11.5 11 14l4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>

                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-2">
                    🎉 Transaksi Berhasil!
                </h1>
                <p class="text-lg text-gray-600">
                    Terima kasih atas pesanan Anda. Berikut detail transaksi Anda.
                </p>
                <p class="mt-4 text-sm font-medium text-indigo-600">
                    #ORD-12345678-2025
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- KOLOM KIRI (2/3): Detail Produk & Pengiriman --}}
                <div class="lg:col-span-2 space-y-6 sm:space-y-8">

                    {{-- 1. Daftar Produk --}}
                    <div class="bg-white rounded-xl p-6 sm:p-8 shadow-lg border border-gray-200">
                        <h2
                            class="text-xl font-bold text-gray-900 border-b border-gray-200 pb-4 mb-4">
                            Daftar Produk
                        </h2>

                        @for ($i = 0; $i < 2; $i++)
                            <div class="flex items-start py-4 border-b border-gray-100 last:border-b-0">
                                <img class="w-20 h-20 object-cover rounded-lg mr-4 border border-gray-300"
                                    src="https://via.placeholder.com/80" alt="Produk {{ $i + 1 }}">
                                <div class="flex-1">
                                    <p class="text-base font-semibold text-gray-900">
                                        Nama Produk Fantasi {{ $i + 1 }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-0.5">
                                        Varian: Merah, Ukuran: L
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Jumlah: <span class="font-bold">2</span>
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-base font-bold text-gray-900">
                                        Rp150.000
                                    </p>
                                </div>
                            </div>
                        @endfor
                    </div>

                    {{-- 2. Informasi Pengiriman --}}
                    <div class="bg-white rounded-xl p-6 sm:p-8 shadow-lg border border-gray-200">
                        <h2
                            class="text-xl font-bold text-gray-900 border-b border-gray-200 pb-4 mb-4">
                            Informasi Pengiriman
                        </h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div>
                                <dt class="font-medium text-gray-500">Penerima</dt>
                                <dd class="mt-1 text-gray-900 font-semibold">Budi Santoso</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Layanan Kurir</dt>
                                <dd class="mt-1 text-gray-900 font-semibold">JNE Reguler (Estimasi 3-5 Hari)</dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="font-medium text-gray-500">Alamat</dt>
                                <dd class="mt-1 text-gray-800 leading-relaxed">Jl. Merdeka No. 15, Kel. Kebayoran, Kec.
                                    Sawah Besar, Jakarta Pusat, 10120</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Nomor Resi</dt>
                                <dd class="mt-1 font-semibold">
                                    <a href="#" class="text-indigo-600 hover:underline">JN1234567890</a>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    {{-- Flowbite Badge/Tag --}}
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 me-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 0a10 10 0 1 0 10 10A10.011 10.011 0 0 0 10 0Zm3.982 7.79a1 1 0 0 1 1.41 1.41l-4 4a1 1 0 0 1-1.41 0l-2-2a1 1 0 0 1 1.41-1.41L10 11.082l3.707-3.707Z"/>
                                        </svg>
                                        Dalam Pengiriman
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                </div>

                {{-- KOLOM KANAN (1/3): Rincian Pembayaran --}}
                <div class="lg:col-span-1 space-y-8">

                    <div class="bg-white rounded-xl p-6 sm:p-8 shadow-lg border border-gray-200 sticky top-8">
                        <h2
                            class="text-xl font-bold text-gray-900 border-b border-gray-200 pb-4 mb-4">
                            Rincian Pembayaran
                        </h2>

                        <dl class="space-y-3">
                            <div class="flex justify-between text-gray-600">
                                <dt class="text-sm">Subtotal (2 Item)</dt>
                                <dd class="text-sm font-medium text-gray-900">Rp300.000</dd>
                            </div>
                            <div class="flex justify-between text-gray-600">
                                <dt class="text-sm">Biaya Pengiriman</dt>
                                <dd class="text-sm font-medium text-gray-900">Rp25.000</dd>
                            </div>
                            <div
                                class="flex justify-between text-gray-600 border-b border-gray-200 pb-3">
                                <dt class="text-sm">Diskon Kupon</dt>
                                <dd class="text-sm text-red-600 font-medium">- Rp10.000</dd>
                            </div>
                            <div class="flex justify-between pt-3">
                                <dt class="text-lg font-bold text-gray-900">Total Pembayaran</dt>
                                <dd class="text-xl font-extrabold text-indigo-700">Rp315.000</dd>
                            </div>
                        </dl>

                        <hr class="my-6 border-gray-200">

                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-500">Metode Bayar</dt>
                                <dd class="text-gray-900 font-semibold">Transfer Bank (BCA)</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-500">Tanggal Transaksi</dt>
                                <dd class="text-gray-900">28 September 2025</dd>
                            </div>
                        </dl>

                        <div class="mt-8 space-y-3">
                            {{-- Tombol Utama (Primary) untuk Cetak Faktur (Flux Button diganti <a> Flowbite Primary) --}}
                            <a href="#"
                                class="inline-flex items-center justify-center w-full py-3 text-base font-semibold text-center text-white
                                      bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                                {{-- SVG Printer Icon --}}
                                <svg class="w-5 h-5 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18V6a2 2 0 012-2h8a2 2 0 012 2v12M6 18h12a2 2 0 002-2v-4H4v4a2 2 0 002 2zM18 14h-4"/>
                                </svg>
                                Cetak Faktur (PDF)
                            </a>

                            {{-- Tombol Sekunder (Outline) untuk Kembali ke Riwayat Pesanan (Flux Button diganti <a> Flowbite Outline) --}}
                            <a href="#"
                                class="inline-flex items-center justify-center w-full py-3 text-base font-semibold text-center text-gray-900
                                      border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition duration-300">
                                Kembali ke Riwayat Pesanan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection