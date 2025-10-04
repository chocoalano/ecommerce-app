@php
    // Perbaikan: Tailwind tidak punya kelas max-w-5/6 ⇒ gunakan width kustom via arbitrary value
    // Menggunakan warna background dan skema yang umum di Flowbite (gray/indigo)
    $outerClass = 'mx-auto max-w-5/6 px-4 mx-auto h-full max-h-dvh bg-gray-50 text-gray-900 flex items-center justify-center p-16 overflow-hidden rounded-xl';
    $computedImage = $image
        ? (Str::startsWith($image, ['http://', 'https://', '/']) ? $image : asset($image))
        : asset('storage/images/galaxy-z-flip7-share-image.png');
@endphp

<div class="{{ $outerClass }} {{ $containerClass }}">
    {{-- Container utama (diperluas untuk Dark Mode) --}}
    <div class="w-full max-w-[83.333%] md:max-w-[80%] lg:max-w-[72rem] z-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 lg:gap-x-24 items-center">
            {{-- Konten Teks --}}
            <div class="flex flex-col space-y-6 md:space-y-8">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-tight tracking-tight text-gray-900">
                    {{ $title }}
                </h1>

                @if(!empty($subtitle))
                    <p class="text-lg md:text-xl lg:text-2xl text-gray-600">
                        {{ $subtitle }}
                    </p>
                @endif

                {{-- Call to Action Buttons (Menggunakan styling Flowbite) --}}
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 mt-6">
                    @if(!empty($primary['href']) && !empty($primary['label']))
                        {{-- Tombol Primer (Flowbite Default/Indigo Style) --}}
                        <a href="{{ $primary['href'] }}"
                           class="inline-flex items-center justify-center px-5 py-3 text-base font-medium text-center text-white
                                  bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300
                                  transition duration-300">
                            {{ $primary['label'] }}
                        </a>
                    @endif

                    @if(!empty($secondary['href']) && !empty($secondary['label']))
                        {{-- Tombol Sekunder (Flowbite Outline/Grey Style) --}}
                        <a href="{{ $secondary['href'] }}"
                           class="inline-flex items-center justify-center px-5 py-3 text-base font-medium text-center text-gray-900
                                  border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition duration-300">
                            {{ $secondary['label'] }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Gambar --}}
            <div class="relative flex items-center justify-center mt-10 md:mt-0">
                <img
                    src="{{ $computedImage }}"
                    alt="{{ $imageAlt }}"
                    class="w-full h-auto object-contain z-10"
                    loading="eager"
                    decoding="async"
                />
            </div>
        </div>
    </div>

    {{-- Detail/Decorative Icon (Flowbite style, disamakan dengan Dark Mode) --}}
    <div class="absolute bottom-8 left-8 text-gray-400 z-10" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
</div>