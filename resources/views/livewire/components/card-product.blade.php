<article
    class="group snap-start shrink-0 w-[260px] sm:w-[280px] lg:w-[250px]
                                rounded-2xl bg-white ring-1 ring-gray-200
                                transition duration-300 hover:shadow-md">

    {{-- MEDIA --}}
    <div class="relative p-5">
        {{-- Wishlist floating --}}
        <button type="button"
            class="absolute right-8 top-8 inline-flex size-9 items-center justify-center
                                        rounded-full border border-gray-200 bg-white/90 backdrop-blur
                                        hover:bg-white focus:outline-none focus:ring-2 focus:ring-gray-300"
            aria-label="Tambah ke Wishlist">
            <flux:icon.heart />
        </button>

        {{-- Gambar Produk --}}
        <div class="aspect-[4/3] rounded-xl grid place-items-center overflow-hidden bg-gray-50">
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy"
                class="h-full w-full object-contain transition duration-500 ease-in-out group-hover:scale-110">
        </div>
    </div>

    {{-- BODY --}}
    <div class="px-5 pb-5">
        {{-- Nama --}}
        <h3 class="text-lg !font-bold text-gray-900 leading-tight">
            {{ Str::limit($title ?? '', 25, '…') }}
        </h3>

        {{-- Harga --}}
        <div class="mt-2 flex items-end gap-2">
            <span class="text-sm font-medium text-gray-900">
                {{ $price }}
            </span>
        </div>

        {{-- Footer CTA --}}
        <div class="mt-4 flex gap-2">
            <flux:button variant="outline" color="zinc" class="!rounded-full py-3 px-5 flex-1"
                href="{{ route('product.show', ['id' => $id]) }}">
                Lihat Detail
            </flux:button>
            <flux:button variant="primary" color="zinc" class="!rounded-full py-3 px-5 flex items-center"
                icon="shopping-cart">
            </flux:button>
        </div>
    </div>
</article>
