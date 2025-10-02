@props([
    // Konten dinamis
    'title' => '2025 AI TVs',
    'subtitle' => 'Explore new AI TVs',
    'primary' => ['label' => 'Lebih detail', 'href' => '#'],
    'secondary' => ['label' => 'Lihat semua', 'href' => '#'],
    'image' => null,
    'imageAlt' => 'Hero image',
    'containerClass' => '',
])

@php
    // Perbaikan: Tailwind tidak punya kelas max-w-5/6 ⇒ gunakan width kustom via arbitrary value
    $outerClass = 'relative max-w-5/6 mx-auto h-full max-h-dvh bg-gray-100 text-gray-900 flex items-center justify-center p-16 overflow-hidden rounded-xl';
    $computedImage = $image
        ? (Str::startsWith($image, ['http://', 'https://', '/']) ? $image : asset($image))
        : asset('images/galaxy-z-flip7-share-image.png');
@endphp

<div {{ $attributes->class($outerClass)->merge(['class' => $containerClass]) }}>
    <div class="w-full max-w-[83.333%] md:max-w-[80%] lg:max-w-[72rem]">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-24 items-center">
            <div class="flex flex-col space-y-6 md:space-y-8">
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold leading-tight tracking-tight">
                    {{ $title }}
                </h1>

                @if(!empty($subtitle))
                    <p class="text-lg md:text-xl lg:text-2xl text-gray-600">
                        {{ $subtitle }}
                    </p>
                @endif

                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 mt-6">
                    @if(!empty($primary['href']) && !empty($primary['label']))
                        <a href="{{ $primary['href'] }}"
                           class="px-6 py-3 border border-gray-900 text-gray-900 font-medium rounded-full hover:bg-gray-200 transition duration-300 text-center">
                            {{ $primary['label'] }}
                        </a>
                    @endif

                    @if(!empty($secondary['href']) && !empty($secondary['label']))
                        <a href="{{ $secondary['href'] }}"
                           class="px-6 py-3 text-gray-600 font-medium hover:text-gray-900 transition duration-300 text-center">
                            {{ $secondary['label'] }}
                        </a>
                    @endif
                </div>
            </div>

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

    <div class="absolute bottom-8 left-8 text-gray-400" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </div>
</div>
