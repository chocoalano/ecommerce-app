<div class="bg-white py-10">
    <div class="max-w-5/6 mx-auto px-4 sm:px-6 lg:px-8">

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
                <flux:button href="{{ route('category') }}" icon:trailing="arrow-up-right" class="!rounded-full py-6">
                    Lihat Semua
                </flux:button>
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
                        <flux:button href="{{ route('category', ['category'=>$cat['slug']]) }}" variant="primary" color="zinc" class="w-48 h-13 !rounded-full py-3"
                            icon="chevron-right">
                            Lihat Produk
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
