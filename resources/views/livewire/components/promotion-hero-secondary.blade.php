<section class="{{ $sectionClass }}">
    <div class="flex flex-col md:flex-row items-center">
        {{-- Text --}}
        <div class="md:w-1/2 p-6 sm:p-8 lg:p-12 order-2 md:order-1 text-center md:text-left">
            {{-- Badge kecil (optional) --}}
            @if($data['badge'] ?? null)
                <span class="inline-flex items-center px-3 py-1 mb-3 text-xs font-semibold rounded-full bg-zinc-200 text-zinc-800">
                    {{ $data['badge'] }}
                </span>
            @endif

            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-gray-900 mb-4">
                {{ $data['title'] ?? '' }}
            </h1>

            <p class="text-base sm:text-lg text-gray-600 mb-6 max-w-lg mx-auto md:mx-0">
                {{ $data['desc'] ?? '' }}
            </p>

            <div class="flex items-center gap-3 justify-center md:justify-start">
                @if(!empty($data['ctaLabel']) && !empty($data['ctaUrl']))
                    <a href="{{ $data['ctaUrl'] }}"
                       class="inline-flex items-center justify-center px-6 py-3 text-sm sm:text-base font-semibold text-white
                              bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                        {{ $data['ctaLabel'] }}
                    </a>
                @endif

                @if(!empty($data['secondaryLabel']) && !empty($data['secondaryUrl']))
                    <a href="{{ $data['secondaryUrl'] }}"
                       class="inline-flex items-center justify-center px-6 py-3 text-sm sm:text-base font-semibold text-zinc-900
                              border border-zinc-300 rounded-full hover:bg-zinc-100 focus:ring-4 focus:ring-zinc-100 transition duration-300">
                        {{ $data['secondaryLabel'] }}
                    </a>
                @endif
            </div>
        </div>

        {{-- Image --}}
        <div class="md:w-1/2 order-1 md:order-2">
            <img src="{{ $data['image'] ?? '' }}" alt="{{ $data['imageAlt'] ?? '' }}"
                 loading="eager" fetchpriority="high" decoding="async"
                 class="w-full h-auto object-cover max-h-64 md:max-h-full drop-shadow-md">
        </div>
    </div>
</section>
