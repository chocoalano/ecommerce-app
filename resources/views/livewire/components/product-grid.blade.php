<main class="lg:w-3/4 w-full">
    {{-- Top toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <p class="text-gray-600 text-sm sm:text-base">
            @if ($products->total() > 0)
                Menampilkan {{ ($products->firstItem() ?? 1) }}–{{ ($products->lastItem() ?? $products->count()) }}
                dari {{ $products->total() }} produk
            @else
                Tidak ada produk yang cocok
            @endif
        </p>
        <div class="flex items-center gap-2">
            <label for="sort-select" class="text-gray-600 text-sm whitespace-nowrap">Urutkan:</label>
            <select id="sort-select" wire:model="sort"
                    class="w-full sm:w-auto bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block p-2.5">
                <option value="popular">Paling Populer</option>
                <option value="new">Terbaru</option>
                <option value="price_asc">Harga: Rendah → Tinggi</option>
                <option value="price_desc">Harga: Tinggi → Rendah</option>
            </select>

            <select wire:model="perPage"
                    class="w-full sm:w-auto bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block p-2.5">
                @foreach ([12,24,36,48] as $pp)
                    <option value="{{ $pp }}">{{ $pp }}/hal</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- PRODUCT GRID --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-5 lg:gap-6">
    @forelse($products as $product)
        @php
            $image  = optional($product->media->first())->url ?? asset('/images/galaxy-z-flip7-share-image.png');
            $price  = $product->base_price;
            $rating = $product->avg_rating ? round($product->avg_rating, 1) : null;
        @endphp

        <livewire:components.card-product
            :sku="$product->sku"
            :title="$product->name"
            :price="'Rp ' . number_format((float)$price, 0, ',', '.')"
            :image="asset('storage/'.$image)"
            :rating="$rating"
            :key="$product->id"
        />
    @empty
        <div class="col-span-full">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
                <h3 class="text-lg font-medium text-gray-900">Produk belum tersedia</h3>
                <p class="mt-1 text-gray-500">Coba ubah filter atau kembali beberapa saat lagi.</p>
            </div>
        </div>
    @endforelse
</div>

    {{-- Pagination --}}
    <div class="mt-12">
        {{ $products->onEachSide(1)->links('vendor.pagination.tailwind') }}
    </div>
</main>
