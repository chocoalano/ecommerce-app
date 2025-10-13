<section x-data="{ isCompact: false }"
         x-init="
            const onScroll = () => { isCompact = window.scrollY > 10 };
            onScroll(); window.addEventListener('scroll', onScroll);
         "
         class="relative isolate overflow-hidden bg-gradient-to-br from-zinc-50 to-white transition-all duration-300 ease-out"
         :class="isCompact ? 'max-w-5/6 mx-auto rounded-xl px-4 sm:px-6 lg:px-8' : 'max-w-none w-full px-20'">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-repeat opacity-5"
         style="background-image:url('data:image/svg+xml;utf8,<svg width=&quot;4&quot; height=&quot;4&quot; viewBox=&quot;0 0 4 4&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><circle cx=&quot;2&quot; cy=&quot;2&quot; r=&quot;1&quot; fill=&quot;%239ca3af&quot;/></svg>');">
    </div>

    <div class="relative transition-all duration-300 ease-out"
         :class="isCompact ? 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8' : 'max-w-none w-full px-0'">
        <div class="text-center transition-all duration-300 ease-out"
             :class="isCompact ? 'py-8 sm:py-12 lg:py-14' : 'py-14 sm:py-20 lg:py-28'">

            <!-- Tagline -->
            <div class="mb-3 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                     class="text-zinc-900 transition-all duration-300"
                     :class="isCompact ? 'w-5 h-5' : 'w-6 h-6'">
                    <path fill-rule="evenodd"
                          d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                          clip-rule="evenodd" />
                </svg>
                <p class="font-bold text-zinc-900 transition-all duration-300 uppercase tracking-widest"
                   :class="isCompact ? 'text-sm sm:text-base' : 'text-base sm:text-lg'">
                    {{ $heroType }}
                </p>
            </div>

            <!-- Title -->
            <h1 class="font-black tracking-tight text-gray-900 transition-all duration-300 leading-tight"
                :class="isCompact ? 'text-4xl sm:text-6xl lg:text-[60px]' : 'text-5xl sm:text-7xl lg:text-[80px]'">
                {{ $heroTitle }}
            </h1>

            <!-- Deskripsi -->
            <p class="mt-4 text-gray-700 transition-all duration-300 font-semibold max-w-3xl mx-auto"
               :class="isCompact ? 'text-md sm:text-lg' : 'text-lg sm:text-2xl'">
                {!! $heroDesc !!}
            </p>

            <!-- Tagline tambahan -->
            @if ($heroTag)
                <p class="mt-2 text-zinc-600 font-extrabold transition-all duration-300"
                   :class="isCompact ? 'text-xl sm:text-2xl' : 'text-2xl sm:text-4xl'">
                    {{ $heroTag }}
                </p>
            @endif

            <!-- CTA -->
            <div class="mt-8 sm:mt-10 flex items-center justify-center gap-4">
                <a href="{{ $heroLink }}"
                   class="inline-flex items-center justify-center bg-zinc-900 text-white hover:bg-zinc-800 rounded-full shadow-lg hover:shadow-xl transition-all duration-300"
                   :class="isCompact ? 'px-6 py-3 text-base font-semibold' : 'px-8 py-4 text-lg font-bold'">
                    Periksa sekarang
                </a>

                @if ($hero)
                    <a href="{{ $heroMore }}"
                       class="inline-flex items-center justify-center text-gray-700 hover:text-zinc-600 font-semibold transition-all duration-300"
                       :class="isCompact ? 'text-sm' : 'text-base'">
                        Detail Promosi →
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Gambar Hero -->
    <div class="relative">
        <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
            <div class="relative mx-auto grid place-items-center overflow-visible">
                <div class="relative w-full max-w-[1200px] overflow-visible"
                     :class="isCompact ? 'max-w-[960px]' : 'max-w-[1500px]'">
                    <div class="w-full overflow-visible rounded-2xl"
                         :class="isCompact ? 'aspect-[16/7]' : 'aspect-[16/6]'">
                        <img src="{{ asset('storage/'.$heroImg) }}"
                            alt="{{ $heroTitle }}"
                            loading="eager"
                            fetchpriority="high"
                            decoding="async"
                            class="h-full w-full object-cover select-none pointer-events-none transition-transform duration-700 ease-[cubic-bezier(.2,.7,.2,1)] will-change-transform drop-shadow-2xl rounded-2xl"
                            :class="isCompact ? 'scale-100 translate-y-0' : 'scale-110 sm:scale-125 translate-y-1'"
                            style="transform-origin:center;" />
                </div>
            </div>
        </div>
    </div>
</section>
