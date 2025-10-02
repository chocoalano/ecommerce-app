@extends('layouts.app')

@section('content')
    <div class="bg-zinc-50 dark:bg-zinc-900 min-h-screen py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 sm:mb-12">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-zinc-900 dark:text-white mb-2">
                    🎉 Transaksi Berhasil!
                </h1>
                <p class="text-lg text-zinc-600 dark:text-zinc-400">
                    Terima kasih atas pesanan Anda. Berikut detail transaksi Anda.
                </p>
                <p class="mt-4 text-sm font-medium text-blue-600 dark:text-blue-400">
                    #ORD-12345678-2025
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <div class="lg:col-span-2 space-y-8">

                    <div class="bg-gray-100 rounded-xl p-6 sm:p-8">
                        <h2
                            class="text-2xl font-semibold text-zinc-900 dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-4 mb-4">
                            Daftar Produk
                        </h2>

                        @for ($i = 0; $i < 2; $i++)
                            <div class="flex items-start py-4 border-b border-zinc-100 last:border-b-0">
                                <img class="w-20 h-20 object-cover rounded-lg mr-4 border border-zinc-200"
                                    src="https://via.placeholder.com/80" alt="Produk {{ $i + 1 }}">
                                <div class="flex-1">
                                    <p class="text-lg font-medium text-zinc-900 dark:text-white">
                                        Nama Produk Fantasi {{ $i + 1 }}
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        Varian: Merah, Ukuran: L
                                    </p>
                                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                        Jumlah: <span class="font-semibold">2</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-zinc-900 dark:text-white">
                                        Rp150.000
                                    </p>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="bg-gray-100 rounded-xl p-6 sm:p-8">
                        <h2
                            class="text-2xl font-semibold text-zinc-900 dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-4 mb-4">
                            Informasi Pengiriman
                        </h2>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Penerima</dt>
                                <dd class="mt-1 text-zinc-900 dark:text-white">Budi Santoso</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Layanan Kurir</dt>
                                <dd class="mt-1 text-zinc-900 dark:text-white">JNE Reguler (Estimasi 3-5 Hari)</dd>
                            </div>
                            <div class="md:col-span-2">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Alamat</dt>
                                <dd class="mt-1 text-zinc-900 dark:text-white">Jl. Merdeka No. 15, Kel. Kebayoran, Kec.
                                    Sawah Besar, Jakarta Pusat, 10120</dd>
                            </div>
                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Nomor Resi</dt>
                                <dd class="mt-1 text-blue-600 dark:text-blue-400 font-medium">
                                    <a href="#" class="hover:underline">JN1234567890</a>
                                </dd>
                            </div>
                            <div>
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        Dalam Pengiriman
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                </div>

                <div class="lg:col-span-1 space-y-8">

                    <div class="bg-gray-100 rounded-xl p-6 sm:p-8 sticky top-8">
                        <h2
                            class="text-2xl font-semibold text-zinc-900 dark:text-white border-b border-zinc-200 dark:border-zinc-700 pb-4 mb-4">
                            Rincian Pembayaran
                        </h2>

                        <dl class="space-y-3">
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-300">
                                <dt class="text-sm">Subtotal (2 Item)</dt>
                                <dd class="text-sm">Rp300.000</dd>
                            </div>
                            <div class="flex justify-between text-zinc-600 dark:text-zinc-300">
                                <dt class="text-sm">Biaya Pengiriman</dt>
                                <dd class="text-sm">Rp25.000</dd>
                            </div>
                            <div
                                class="flex justify-between text-zinc-600 dark:text-zinc-300 border-b border-zinc-100 dark:border-zinc-700/50 pb-3">
                                <dt class="text-sm">Diskon Kupon</dt>
                                <dd class="text-sm text-red-600 dark:text-red-400">- Rp10.000</dd>
                            </div>
                            <div class="flex justify-between pt-3">
                                <dt class="text-lg font-bold text-zinc-900 dark:text-white">Total Pembayaran</dt>
                                <dd class="text-lg font-bold text-blue-600 dark:text-blue-400">Rp315.000</dd>
                            </div>
                        </dl>

                        <hr class="my-6 border-zinc-200 dark:border-zinc-700">

                        <dl class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Metode Bayar</dt>
                                <dd class="text-zinc-900 dark:text-white font-semibold">Transfer Bank (BCA)</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-zinc-500 dark:text-zinc-400">Tanggal Transaksi</dt>
                                <dd class="text-zinc-900 dark:text-white">28 September 2025</dd>
                            </div>
                        </dl>

                        <div class="mt-8 space-y-3">
                            {{-- Tombol Utama (Primary/Accent) untuk Cetak Faktur --}}
                            <flux:button as="a" href="#" variant="primary" color="zinc" class="w-full justify-center">
                                Cetak Faktur (PDF)
                            </flux:button>

                            {{-- Tombol Sekunder (Zinc) untuk Kembali ke Riwayat Pesanan --}}
                            <flux:button as="a" href="#" class="w-full justify-center">
                                Kembali ke Riwayat Pesanan
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
