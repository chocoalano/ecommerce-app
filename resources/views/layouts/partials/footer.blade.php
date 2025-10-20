<footer class="bg-white border-t border-gray-200">
    <div class="mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

        {{-- Newsletter Subscription Section (Flowbite Style, Light Background) --}}
        <div class="rounded-xl bg-gray-50 p-6 sm:p-8 border border-gray-200">
            <div class="grid items-center gap-6 lg:grid-cols-3">
                @php
                    $promotion = \App\Models\Promo\Promotion::where('is_active', true)->first();
                @endphp
                <div class="lg:col-span-2">
                    <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
                        {!! $promotion?->title ?? 'Dapatkan kabar diskon & produk baru' !!}
                    </h3>
                    <p class="mt-2 text-gray-600">
                        {!! $promotion?->description ?? 'Berlangganan newsletter kami. Gratis, bisa berhenti kapan saja.' !!}
                    </p>
                </div>
                <form class="lg:col-span-1" action="{{ route('newsletter') }}" method="post"
                    aria-label="Form Newsletter">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input name="email" type="email" placeholder="nama@email.com"
                            class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-full bg-white focus:ring-zinc-900 focus:border-zinc-900"
                            required />
                        <button type="submit"
                            class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-white bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:outline-none focus:ring-zinc-300 transition whitespace-nowrap">
                            Berlangganan
                        </button>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Dengan berlangganan, Anda menyetujui <a href="#"
                            class="underline hover:text-zinc-900 transition">Kebijakan Privasi</a>.
                    </p>
                </form>
            </div>
        </div>

        {{-- Main Footer Links & Company Info --}}
        <div class="mt-10 md:mt-14 grid grid-cols-2 gap-8 sm:grid-cols-4 lg:grid-cols-6">

            @php
                $footer = \App\Models\Footer::first();
            @endphp
            <div class="col-span-2 lg:col-span-2">
                <a href="/" class="flex items-center gap-2">
                    <img src="{{ $footer?->logo_url ? asset($footer->logo_url) : asset('images/logo-puranura-id.png') }}"
                        alt="Logo {{ $footer?->company_name ?? config('app.name') }}"
                        class="h-9 w-9 rounded-xl object-cover" />
                    <span
                        class="text-xl font-extrabold text-gray-900">{{ $footer?->company_name ?? config('app.name') }}</span>
                </a>
                <p class="mt-4 max-w-md text-sm text-gray-600">
                    {{ $footer?->description ?? 'Bahan baku minuman berkualitas untuk HORECA dan UMKM. Cepat, segar, dan terpercaya.' }}
                </p>

                <div class="mt-6 space-y-2 text-sm text-gray-600">
                    <p>
                        <span class="font-semibold text-gray-800">WhatsApp:</span>
                        <a href="https://wa.me/{{ $footer?->whatsapp ?? env('COMPANY_WHATSAPP', '62xxxxxxxxxx') }}"
                            class="hover:text-zinc-900">
                            {{ $footer?->whatsapp ?? env('COMPANY_WHATSAPP', '62xxxxxxxxxx') }}
                        </a>
                    </p>
                    <p>
                        <span class="font-semibold text-gray-800">Email:</span>
                        <a href="mailto:{{ $footer?->email ?? env('COMPANY_EMAIL', 'support@domain.com') }}"
                            class="hover:text-zinc-900">
                            {{ $footer?->email ?? env('COMPANY_EMAIL', 'support@domain.com') }}
                        </a>
                    </p>
                    <p>
                        <span class="font-semibold text-gray-800">Jam Operasional:</span>
                        {{ $footer?->operating_hours ?? env('COMPANY_OPERATING_HOURS', 'Senin–Sabtu 09:00–18:00 WIB') }}
                    </p>
                </div>
            </div>

            <nav aria-label="Shop" class="space-y-4 sm:col-span-1 lg:col-span-1">
                <h4 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">Belanja</h4>
                <ul class="space-y-3 text-sm">
                    @php
                        $categories = Cache::remember('footer_categories', 3600, function () {
                            return \App\Models\Product\Category::select('id', 'name', 'slug')
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get();
                        });
                    @endphp

                    @foreach($categories as $category)
                        <li>
                            <a href="{{ route('products.index', ['category[0]' => $category->slug]) }}"
                                class="text-gray-600 hover:text-zinc-900 transition">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <nav aria-label="Perusahaan" class="space-y-4 sm:col-span-1 lg:col-span-1">
                <h4 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">Perusahaan</h4>
                <ul class="space-y-3 text-sm">
                    @php
                        $companyPages = Cache::remember('footer_company_pages', 3600, function () {
                            return \App\Models\Page::active()
                                ->footer()
                                ->category('company')
                                ->ordered()
                                ->get();
                        });
                    @endphp

                    @foreach($companyPages as $page)
                        <li>
                            <a href="{{ route('page.show', $page->slug) }}"
                                class="text-gray-600 hover:text-zinc-900 transition">
                                {{ $page->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            <nav aria-label="Bantuan" class="space-y-4 sm:col-span-1 lg:col-span-1">
                <h4 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">Bantuan</h4>
                <ul class="space-y-3 text-sm">
                    @php
                        $helpPages = Cache::remember('footer_help_pages', 3600, function () {
                            return \App\Models\Page::active()
                                ->footer()
                                ->category('help')
                                ->ordered()
                                ->get();
                        });
                    @endphp

                    @foreach($helpPages as $page)
                        <li>
                            <a href="{{ route('page.show', $page->slug) }}"
                                class="text-gray-600 hover:text-zinc-900 transition">
                                {{ $page->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </nav>

            @php
                $socials = \App\Models\FooterSocial::where('is_active', true)->orderBy('id')->get();
            @endphp
            <div class="col-span-2 sm:col-span-1 lg:col-span-1 space-y-4">
                <h4 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">Ikuti Kami</h4>
                <div class="flex items-center gap-4">
                    @foreach($socials as $social)
                        <a href="{{ $social->url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($social->platform) }}"
                            class="text-gray-600 hover:text-zinc-900 transition">
                            <img src="{{ $social->icon ? asset('storage/'.$social->icon) : '' }}" alt="{{ ucfirst($social->platform) }} Icon" class="h-6 w-6"/>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Footer Bottom (Guarantee and Payment Icons) --}}
        <div
            class="mt-10 md:mt-12 flex flex-col items-center justify-between gap-6 md:flex-row border-t border-gray-200 pt-6">

            @php
                $guarantees = \App\Models\FooterGuarantee::where('is_active', true)->orderBy('id')->get();
            @endphp
            <div class="flex flex-wrap justify-center md:justify-start items-center gap-3 text-xs text-gray-500">
                @foreach($guarantees as $guarantee)
                    <span class="inline-flex items-center gap-2 text-gray-600 font-medium">
                        @if($guarantee->icon)
                            <img src="{{ asset('storage/'.$guarantee->icon) }}" alt="{{ $guarantee->title }} Icon" class="size-4 text-gray-800" />
                        @endif
                        {{ $guarantee->label }}
                    </span>
                @endforeach
            </div>

            <div class="flex flex-wrap justify-center gap-4">
                <span class="text-sm font-semibold text-gray-800 hidden sm:block">Pembayaran:</span>
                {{-- Payment Icons (Semua kelas dark: telah dihapus) --}}
                @php
                    $payments = \App\Models\FooterPayment::where('is_active', true)->orderBy('id')->get();
                @endphp
                @foreach($payments as $payment)
                    <img src="{{ asset('storage/' . $payment->icon) }}"
                         alt="{{ $payment->label }} Logo"
                         class="h-5 md:h-6 w-auto opacity-80"
                         title="{{ $payment->label }}">
                @endforeach
            </div>
        </div>

        {{-- Copyright & Utility Links --}}
        <div class="mt-8 pt-4">
            <div class="flex flex-col-reverse items-center justify-between gap-4 sm:flex-row">
                <p class="text-xs text-gray-500 order-last sm:order-first">
                    © {{ date('Y') }} {{ $footer?->company_name ?? config('app.name') }}. Seluruh hak cipta dilindungi.
                </p>
                @php
                    $utilityPages = \App\Models\Page::active()
                            ->footer()
                            ->category('other')
                            ->ordered()
                            ->get();
                @endphp
                <nav class="flex flex-wrap items-center justify-center sm:justify-end gap-3 sm:gap-4 text-xs">
                    @foreach($utilityPages as $index => $page)
                        <a href="{{ route('page.show', $page->slug) }}" class="text-gray-600 hover:text-zinc-900 transition">
                            {{ $page->title }}
                        </a>
                        @if($index < $utilityPages->count() - 1)
                            <span class="h-3 w-px bg-gray-300 hidden sm:block"></span>
                        @endif
                    @endforeach
                </nav>
            </div>
        </div>
    </div>
</footer>
