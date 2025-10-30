<section x-data="{ isCompact: false }" x-init="
            const onScroll = () => { isCompact = window.scrollY > 10 };
            onScroll(); window.addEventListener('scroll', onScroll);
         "
    class="relative isolate overflow-hidden bg-gradient-to-br from-zinc-50 to-white transition-all duration-300 ease-out"
    :class="isCompact ? 'max-w-5/6 mx-auto rounded-xl px-4 sm:px-6 lg:px-8' : 'max-w-none w-full px-20'">

    <!-- Overlay pattern latar -->
    <div class="absolute inset-0 bg-repeat opacity-5 -z-10"
        style="background-image:url('data:image/svg+xml;utf8,<svg width=&quot;4&quot; height=&quot;4&quot; viewBox=&quot;0 0 4 4&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;><circle cx=&quot;2&quot; cy=&quot;2&quot; r=&quot;1&quot; fill=&quot;%239ca3af&quot;/></svg>');">
    </div>

    <!-- Wrapper gambar + overlay teks -->
    <div class="relative">
        <div class="mx-auto w-full max-w-7xl px-0 sm:px-6 lg:px-8">
            <div class="relative mx-auto grid place-items-center overflow-visible">
                <div class="relative w-full overflow-visible" :class="isCompact ? 'max-w-[960px]' : 'max-w-[1500px]'">

                    <!-- Rasio dan gambar -->
                    <div class="relative w-full overflow-visible rounded-2xl"
                        :class="isCompact ? 'aspect-[16/7]' : 'aspect-[16/6]'">

                        <!-- Gambar -->
                        <img src="{{ asset('storage/' . $heroImg) }}" alt="{{ $heroTitle }}" loading="eager"
                            fetchpriority="high" decoding="async"
                            class="h-full w-full object-cover select-none pointer-events-none transition-transform duration-700 ease-[cubic-bezier(.2,.7,.2,1)] will-change-transform drop-shadow-2xl rounded-2xl"
                            :class="isCompact ? 'scale-100 translate-y-0' : 'scale-110 sm:scale-125 translate-y-1'"
                            style="transform-origin:center;" />

                        <!-- Overlay gradient untuk kontras universal -->
                        <div class="absolute inset-0 rounded-2xl z-10
                        bg-gradient-to-b from-black/50 via-black/30 to-black/10">
                        </div>

                        <!-- TEKS: overlay tengah dengan panel blur -->
                        <div class="absolute inset-0 z-20 grid place-items-center text-center px-4 sm:px-6 lg:px-8">
                            <div class="transition-all duration-300 ease-out
                          text-white
                          rounded-2xl mx-auto"
                                :class="isCompact ? 'p-6 sm:p-8 lg:p-10 max-w-3xl' : 'p-8 sm:p-12 lg:p-14 max-w-4xl'">

                                <!-- Tagline -->
                                <div class="mb-3 flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                        class="text-white/90 transition-all duration-300"
                                        :class="isCompact ? 'w-5 h-5' : 'w-6 h-6'">
                                        <path fill-rule="evenodd"
                                            d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    <p class="font-bold uppercase tracking-widest text-white/90 transition-all duration-300"
                                        :class="isCompact ? 'text-sm sm:text-base' : 'text-base sm:text-lg'">
                                        {{ $heroType }}
                                    </p>
                                </div>

                                <!-- Title -->
                                <h1 class="font-black tracking-tight leading-tight transition-all duration-300 text-white"
                                    :class="isCompact ? 'text-4xl sm:text-6xl lg:text-[60px]' : 'text-5xl sm:text-7xl lg:text-[80px]'">
                                    {{ $heroTitle }}
                                </h1>

                                <!-- Deskripsi -->
                                <p class="mt-4 font-semibold max-w-3xl mx-auto transition-all duration-300 text-white/90"
                                    :class="isCompact ? 'text-md sm:text-lg' : 'text-lg sm:text-2xl'">
                                    {!! $heroDesc !!}
                                </p>

                                <!-- Tagline tambahan -->
                                @if ($heroTag)
                                    <p class="mt-2 font-extrabold transition-all duration-300 text-white"
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
                                            class="inline-flex items-center justify-center text-white/90 hover:text-white font-semibold transition-all duration-300"
                                            :class="isCompact ? 'text-sm' : 'text-base'">
                                            Detail Promosi →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <!-- END overlay teks -->
                    </div> <!-- end aspect box -->
                </div>
            </div>
        </div>
    </div>
</section>
