<div class="bg-white py-10">
    <div class="max-w-5/6 mx-auto">

        {{-- Judul row kategori --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-10">
            <div class="max-w-4xl">
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                    Kategori Produk
                </h2>
                <p class="mt-2 text-gray-600 text-base">
                    Temukan berbagai kategori pilihan yang kami sediakan untuk memenuhi kebutuhan bisnis dan gaya hidup
                    Anda.
                </p>
            </div>
            <div class="mt-4 sm:mt-0">
                {{-- Flux Button 'Lihat Semua' diganti dengan tag <a> --}}
                <a href="{{ route('products.index') }}"
                    class="inline-flex items-center justify-center
                           border border-gray-300 bg-white text-gray-800 hover:bg-gray-50
                           text-base font-semibold transition duration-300
                           !rounded-full px-6 py-3">
                    Lihat Semua
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                        class="ml-2 h-5 w-5">
                        <path fill-rule="evenodd"
                            d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v4.275a.75.75 0 001.5 0V5.625a.75.75 0 00-.75-.75h-5.65a.75.75 0 000 1.5h4.275l-7.22 7.22a.75.75 0 000 1.06z"
                            clip-rule="evenodd" />
                    </svg>
                </a>
            </div>
        </div>

        {{-- Grid kategori --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach ($category as $cat)
                <div
                    class="group relative flex flex-col items-center rounded-2xl bg-gray-100
         ring-1 ring-gray-200/70 p-5 sm:p-6 text-center
         transition duration-300 hover:-translate-y-0.5 hover:bg-gray-50">

                    {{-- Nama kategori --}}
                    <h3 class="!text-2xl font-extrabold sm:text-lg text-gray-900 mb-4">
                        {{ $cat['name'] }}
                    </h3>

                    {{-- Gambar kategori --}}
                    <div class="w-full text-center aspect-square max-w-[200px] sm:max-w-[220px] overflow-hidden">
                        <img src="{{ asset($cat['image']) }}" alt="Kategori {{ $cat['name'] }}" loading="lazy"
                            class="h-full w-full object-cover transition duration-500 ease-in-out group-hover:scale-110" />
                    </div>

                    {{-- Footer CTA --}}
                    <div
                        class="mt-2 w-full opacity-0 translate-y-5 transition duration-300 group-hover:opacity-100 group-hover:translate-y-0">
                        {{-- Flux Button 'Lihat Produk' diganti dengan tag <a> --}}
                        <a href="{{ route('products.index', ['category'=>$cat['slug']]) }}"
                            class="inline-flex items-center justify-center
                                   w-48 h-13 bg-zinc-900 text-white hover:bg-zinc-800
                                   font-semibold transition duration-300
                                   !rounded-full px-4 py-3 text-base">
                            Lihat Produk
                            {{-- Icon chevron-right diganti dengan SVG --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="ml-2 h-5 w-5">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
