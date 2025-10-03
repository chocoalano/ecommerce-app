<article
    class="group snap-start shrink-0 w-[260px] sm:w-[280px] lg:w-[250px] h-full
           rounded-xl bg-white ring-1 ring-gray-100 overflow-hidden
           transition duration-300 hover:shadow-xl hover:-translate-y-0.5">

    {{-- MEDIA & INTERAKSI --}}
    <div class="relative p-4">
        {{-- Wishlist floating (dengan ikon yang lebih jelas) --}}
        <button type="button"
            class="absolute right-6 top-6 inline-flex size-8 items-center justify-center
                   rounded-full border border-gray-300 bg-white/70 backdrop-blur-sm z-10
                   text-gray-400 hover:text-red-500 hover:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-300/50"
            aria-label="Tambah ke Wishlist">
            {{-- SVG Heart Icon (lebih sederhana) --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition duration-200">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </button>

        {{-- Gambar Produk (dengan efek zoom hover) --}}
        <div class="aspect-[4/3] rounded-lg grid place-items-center overflow-hidden bg-gray-50/70">
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy"
                class="w-full h-full object-contain transition duration-500 ease-in-out group-hover:scale-105">
        </div>
    </div>

    {{-- BODY & CTA --}}
    <div class="px-4 pt-2 pb-5">
        {{-- Nama --}}
        <h3 class="text-base font-semibold text-gray-800 leading-snug h-10 overflow-hidden mb-1">
            {{ Str::limit($title ?? '', 45, '…') }}
        </h3>

        {{-- Harga (Dibuat Lebih Menonjol) --}}
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl font-extrabold text-zinc-900">
                {{ $price }}
            </span>
            {{-- Opsional: Tambahkan diskon atau rating di sini --}}
        </div>

        {{-- Footer CTA (Tombol Lebih Terpadu) --}}
        <div class="flex gap-2">
            {{-- Tombol Detail (Secondary/Outline) --}}
            <a href="{{ route('product.show', ['sku' => $sku]) }}"
                class="inline-flex items-center justify-center flex-1 px-3 py-2 text-sm font-medium text-center text-gray-700
                       border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100
                       transition duration-300">
                Detail
            </a>

            {{-- Tombol Keranjang (Primary/Icon Button) --}}
            <button type="button"
                class="inline-flex items-center justify-center w-12 h-10 p-0 text-white bg-zinc-900
                       rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 shadow-md"
                aria-label="Tambah ke keranjang">
                {{-- SVG Shopping Cart Icon (sedikit lebih kecil untuk estetika) --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd" d="M7.5 6v.75H5.513c-.96 0-1.76.756-1.76 1.716v3.248A2.32 2.32 0 004 12.75a2.32 2.32 0 002.507 2.234l.03-.004c.002 0 .004-.002.006-.002h.008l1.096.794a3.3 3.3 0 003.882 0l1.096-.794h.016l.004-.002c.002 0 .004-.002.006-.002A2.32 2.32 0 0020 12.75a2.32 2.32 0 00-.008-2.502v-3.248c0-.96-.8-1.716-1.76-1.716H16.5V6a4.5 4.5 0 10-9 0zM12 9a.75.75 0 01.75.75v3a.75.75 0 01-1.5 0V9.75A.75.75 0 0112 9z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</article>