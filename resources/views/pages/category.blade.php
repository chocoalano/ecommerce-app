@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12"> {{-- 1. Penyesuaian Padding Container --}}

        {{-- Hero Section --}}
        <section class="bg-gray-100 rounded-2xl overflow-hidden mb-10 lg:mb-12 ring-1 ring-gray-200/60">
            <div class="flex flex-col md:flex-row items-center">
                {{-- Text Content --}}
                <div class="md:w-1/2 p-6 sm:p-8 lg:p-12 order-2 md:order-1"> {{-- 2. Penyesuaian Padding & Order --}}
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Aksesoris Ponsel</h1> {{-- 3. Penyesuaian Ukuran Teks --}}
                    <p class="text-base sm:text-lg text-gray-600 mb-6">Temukan aksesoris terbaik untuk melengkapi pengalaman
                        ponsel Anda.
                        Dari casing hingga power bank—semuanya ada.</p>
                    <flux:button variant="primary" color="zinc" class="!rounded-full px-6 py-3 text-sm sm:text-base">Jelajahi
                        Sekarang
                    </flux:button>
                </div>
                {{-- Image --}}
                <div class="md:w-1/2 order-1 md:order-2"> {{-- Menggunakan order untuk mobile/desktop --}}
                    <img src="{{ asset('images/galaxy-z-flip7-share-image.png') }}" alt="Aksesoris Ponsel"
                        class="w-full h-auto object-cover max-h-64 md:max-h-full"> {{-- Batasan tinggi pada mobile --}}
                </div>
            </div>
        </section>

        {{-- Toolbar + Layout --}}
        <div class="flex flex-col lg:flex-row gap-8 lg:gap-10"> {{-- 4. Penyesuaian Gap --}}

            {{-- SIDEBAR FILTERS --}}
            {{-- Tambahkan kelas `hidden lg:block` jika ingin sidebar tersembunyi di mobile --}}
            <aside class="lg:w-1/4 sticky top-4 self-start"> {{-- 5. Menambah sticky untuk filter --}}
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">

                    {{-- Filter Header --}}
                    <div class="px-5 py-4 sm:px-6 sm:py-5 border-b border-gray-200"> {{-- Penyesuaian padding --}}
                        <h2 class="text-lg font-semibold text-gray-900">Filter</h2> {{-- Ukuran teks lebih konsisten --}}
                    </div>

                    {{-- Filter Body --}}
                    <div class="p-5 sm:p-6 space-y-6"> {{-- Penyesuaian padding --}}
                        {{-- Search --}}
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-gray-700">Cari</label>
                            <flux:input type="search" placeholder="Cari produk..." />
                        </div>

                        {{-- Price --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-900">Harga</h3>
                            <div class="flex items-center gap-3">
                                <flux:input type="number" placeholder="Min" />
                                <span class="text-gray-400">—</span>
                                <flux:input type="number" placeholder="Max" />
                            </div>
                        </div>

                        {{-- Rating --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-900">Rating</h3>
                            <div class="space-y-2">
                                @foreach ([5, 4, 3, 2, 1] as $r)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <flux:checkbox />
                                        <span class="text-sm text-gray-700">{{ str_repeat('★', $r) }}<span
                                                class="text-gray-300">{{ str_repeat('★', 5 - $r) }}</span> &nbsp;
                                            {{ $r }}+</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Availability & Promo --}}
                        <div class="grid gap-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <flux:checkbox /><span class="text-sm text-gray-700">Stok tersedia</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <flux:checkbox /><span class="text-sm text-gray-700">Sedang promo</span>
                            </label>
                        </div>

                        {{-- Features --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-900">Fitur</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['Fast Charging', 'Bluetooth', 'Water Resistant', 'NFC', 'Wi‑Fi'] as $feat)
                                    <flux:badge class="cursor-pointer">{{ $feat }}</flux:badge>
                                    {{-- Badge yang bisa diklik --}}
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100 mt-4 !space-y-0">
                            <flux:button variant="outline" color="zinc" class="w-full !rounded-full">Reset</flux:button>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- MAIN CONTENT --}}
            <main class="lg:w-3/4">
                {{-- Top toolbar --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <p class="text-gray-600 text-sm sm:text-base"> {{-- Ukuran teks lebih kecil di mobile --}}
                        @isset($products)
                            Menampilkan 1–{{ count($products) }} dari {{ count($products) }} produk
                        @endisset
                    </p>
                    <div class="flex items-center gap-2">
                        <span class="text-gray-600 text-sm">Urutkan:</span>
                        <flux:select class="w-full sm:w-auto"> {{-- Select mengisi lebar di mobile --}}
                            <option value="popular">Paling Populer</option>
                            <option value="new">Terbaru</option>
                            <option value="price_asc">Harga: Rendah → Tinggi</option>
                            <option value="price_desc">Harga: Tinggi → Rendah</option>
                        </flux:select>
                    </div>
                </div>

                {{-- PRODUCT GRID --}}
                {{-- 6. Menyesuaikan grid agar lebih padat di layar besar --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @forelse(($products ?? []) as $product)
                        @php
                            $image = $product['image'] ?? null;
                            $url = 'http://localhost:8000/category';
                        @endphp

                        {{-- Catatan: Pastikan Livewire component 'card-product' sudah responsif di dalamnya --}}
                        <livewire:components.card-product :id="$product['id']" :title="$product['name']" :price="$product['price']" :image="$image" />
                    @empty
                        <div class="col-span-full">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
                                <h3 class="text-lg font-medium text-gray-900">Produk belum tersedia</h3>
                                <p class="mt-1 text-gray-500">Coba ubah filter atau kembali beberapa saat lagi.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
                @if ($product)
                    <div class="mt-12">
                        {{-- Pastikan variabel $products adalah hasil dari ->paginate() --}}
                        {{ $products->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
@endsection
