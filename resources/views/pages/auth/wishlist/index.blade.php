@extends('layouts.app')

@section('title', 'Wishlist Saya')
@php
    function format_currency($number)
    {
        return 'Rp. ' . number_format($number, 2, ',', '.');
    }
@endphp
@section('content')
    <div class="max-w-4xl mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Wishlist Saya</h1>

        @if($items->isEmpty())
            <div class="p-6 text-center text-gray-500">
                Belum ada produk di wishlist Anda.
            </div>
        @else
            <div class="gap-6">
                @foreach($items as $item)
                    @php
                        $product = $item->product;
                        $imageUrl = $product?->media?->first()?->url
                            ? asset('storage/' . $product->media->first()->url)
                            : asset('images/no-image.png');
                    @endphp

                    <div
                        class="flex flex-col sm:flex-row items-stretch sm:items-start bg-gray-100 p-4 sm:p-6 rounded-xl transition hover:shadow-lg border border-gray-200">
                        {{-- Gambar Produk --}}
                        <div
                            class="size-20 sm:size-28 flex-shrink-0 rounded-lg overflow-hidden bg-white mx-auto sm:mx-0 mb-4 sm:mb-0 border border-gray-200">
                            <img src="{{ $imageUrl }}" alt="{{ $product?->name ?? 'Produk' }}"
                                class="w-full h-full object-contain p-2">
                        </div>

                        {{-- Informasi Produk --}}
                        <div class="ml-0 sm:ml-4 flex-grow flex flex-col justify-between w-full">
                            <div class="flex justify-between items-start w-full">
                                <div class="pr-4">
                                    <a href="{{ route('products.show', $product?->slug ?? $product?->id) }}"
                                        class="text-lg font-semibold text-gray-900 hover:text-zinc-700 transition line-clamp-2">
                                        {{ $product?->name ?? 'Produk Tidak Diketahui' }}
                                    </a>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $product?->sku ?? '-' }}
                                    </p>
                                </div>

                                <div class="text-right flex-shrink-0">
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ format_currency($product?->base_price ?? 0) }}/Item
                                    </p>
                                </div>
                            </div>

                            {{-- Tombol Aksi --}}
                            <div
                                class="flex flex-wrap justify-between items-center mt-4 pt-3 border-t border-gray-200 sm:mt-6 sm:pt-0 sm:border-t-0 w-full">
                                <form method="POST" action="{{ route('wishlist.remove', $item->id) }}"
                                    onsubmit="return confirm('Hapus produk ini dari wishlist?')">
                                    @csrf
                                    <button type="submit"
                                        class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-800 transition py-2 px-3 rounded-lg hover:bg-gray-200/50">
                                        <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
        {{ $items->links() }}
    </div>
@endsection
