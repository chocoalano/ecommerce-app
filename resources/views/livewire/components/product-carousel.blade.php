<div class="bg-white py-10">
    <div class="mx-auto max-w-5/6 px-4 sm:px-6 lg:px-8">
        <div x-data="{
            scrollBy(delta) {
                const el = $refs.track;
                el.scrollBy({ left: delta, behavior: 'smooth' });
            }
        }" class="w-full">
            {{-- Carousel --}}
            <div class="relative">
                {{-- Judul dan Kontrol Navigasi --}}
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 sm:mb-10">
                    <div class="max-w-4xl mb-4 sm:mb-0">
                        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                            {{ $title }}
                        </h2>
                        <p class="mt-2 text-gray-600 text-base">{{ $description ?? '' }}</p>
                    </div>

                    {{-- Kontrol Navigasi dan Tombol Lihat Semua (Disusun Ulang) --}}
                    <div class="flex items-center space-x-4">
                        {{-- Tombol Navigasi (Dikelompokkan) --}}
                        <div class="flex space-x-3">
                            {{-- Tombol Sebelumnya (Flux Icon diganti SVG) --}}
                            <button type="button" x-on:click="scrollBy(-300)"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 transition duration-200"
                                aria-label="Sebelumnya">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M11.77 15.22a.75.75 0 01-1.06 0l-5.75-5.75a.75.75 0 010-1.06l5.75-5.75a.75.75 0 011.06 1.06L6.56 10l5.21 5.21a.75.75 0 010 1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            {{-- Tombol Berikutnya (Flux Icon diganti SVG) --}}
                            <button type="button" x-on:click="scrollBy(300)"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 transition duration-200"
                                aria-label="Berikutnya">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                                    <path fill-rule="evenodd" d="M8.23 4.78a.75.75 0 011.06 0l5.75 5.75a.75.75 0 010 1.06l-5.75 5.75a.75.75 0 01-1.06-1.06L14.44 10 8.23 4.78a.75.75 0 010-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        {{-- Tombol Lihat Semua (Flux Button diganti <a>, Flowbite Primary style) --}}
                        <a href="#"
                            class="inline-flex items-center justify-center px-5 py-3 text-base font-semibold text-center text-white
                                   bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300
                                   transition duration-300">
                            Lihat Semua
                            {{-- SVG Arrow Up Right (menggantikan icon:trailing) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="ml-2 h-4 w-4">
                                <path fill-rule="evenodd" d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v4.275a.75.75 0 001.5 0V5.625a.75.75 0 00-.75-.75h-5.65a.75.75 0 000 1.5h4.275l-7.22 7.22a.75.75 0 000 1.06z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
                {{-- Track --}}
                <div x-ref="track"
                    class="mx-auto py-4
                        flex gap-6 overflow-x-auto overscroll-x-contain
                        snap-x snap-mandatory pb-8"
                    style="scrollbar-width: thin;">
                    @foreach ($data as $i => $p)
                        {{-- Memastikan setiap card memiliki class snap-align jika ingin snap-mandatory berfungsi optimal --}}
                        <livewire:components.card-product :sku="$p['sku']" :title="$p['title']" :price="$p['price']" :image="$p['image']" :rating="$p['rating']" class="snap-start shrink-0"/>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>