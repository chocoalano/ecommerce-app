<footer class="bg-white border-t border-gray-200">
  <div class="mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

    {{-- Newsletter Subscription Section (Flowbite Style, Light Background) --}}
    <div class="rounded-xl bg-gray-50 p-6 sm:p-8 border border-gray-200">
      <div class="grid items-center gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
          <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-900">
            Dapatkan kabar diskon & produk baru
          </h3>
          <p class="mt-2 text-gray-600">
            Berlangganan newsletter kami. Gratis, bisa berhenti kapan saja.
          </p>
        </div>
        <form class="lg:col-span-1" action="#" method="post" aria-label="Form Newsletter">
          @csrf
          <div class="flex flex-col sm:flex-row gap-3">
            <input
              name="email"
              type="email"
              placeholder="nama@email.com"
              class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-full bg-white focus:ring-zinc-900 focus:border-zinc-900"
              required
            />
            <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium text-white bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:outline-none focus:ring-zinc-300 transition whitespace-nowrap">
              Berlangganan
            </button>
          </div>
          <p class="mt-2 text-xs text-gray-500">
            Dengan berlangganan, Anda menyetujui <a href="#" class="underline hover:text-zinc-900 transition">Kebijakan Privasi</a>.
          </p>
        </form>
      </div>
    </div>

    {{-- Main Footer Links & Company Info --}}
    <div class="mt-10 md:mt-14 grid grid-cols-2 gap-8 sm:grid-cols-4 lg:grid-cols-6">

      <div class="col-span-2 lg:col-span-2">
        <a href="/" class="flex items-center gap-2">
          <img src="{{ asset('images/logo-puranura-id.png') }}" alt="Logo Sinergi Abadi" class="h-9 w-9 rounded-xl object-cover" />
          <span class="text-xl font-extrabold text-gray-900">{{ config('app.name') }}</span>
        </a>
        <p class="mt-4 max-w-md text-sm text-gray-600">
          Bahan baku minuman berkualitas untuk HORECA dan UMKM. Cepat, segar, dan terpercaya.
        </p>

        <div class="mt-6 space-y-2 text-sm text-gray-600">
          <p><span class="font-semibold text-gray-800">WhatsApp:</span> <a href="https://wa.me/{{ env('COMPANY_WHATSAPP', '62xxxxxxxxxx') }}" class="hover:text-zinc-900">{{ env('COMPANY_PHONE', '+62 8xx-xxxx-xxxx') }}</a></p>
          <p><span class="font-semibold text-gray-800">Email:</span> <a href="mailto:{{ env('COMPANY_EMAIL', 'support@domain.com') }}" class="hover:text-zinc-900">{{ env('COMPANY_EMAIL', 'support@domain.com') }}</a></p>
          <p><span class="font-semibold text-gray-800">Jam Operasional:</span> {{ env('COMPANY_OPERATING_HOURS', 'Senin–Sabtu 09:00–18:00 WIB') }}</p>
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

      <div class="col-span-2 sm:col-span-1 lg:col-span-1 space-y-4">
        <h4 class="text-sm font-extrabold uppercase tracking-wider text-gray-900">Ikuti Kami</h4>
        <div class="flex items-center gap-4">
          {{-- Icons diganti menjadi warna standar Light Mode --}}
          <a href="#" aria-label="Instagram" class="text-gray-600 hover:text-zinc-900 transition"><svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg></a>
          <a href="#" aria-label="Facebook" class="text-gray-600 hover:text-zinc-900 transition"><svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 22v-8h2.5l.5-3.5h-3V8.5c0-.9.3-1.5 1.6-1.5H17V3.9C16.6 3.8 15.4 3.7 14 3.7c-2.6 0-4.4 1.6-4.4 4.6v2.2H7v3.5h2.6V22h3.9z"/></svg></a>
          <a href="#" aria-label="TikTok" class="text-gray-600 hover:text-zinc-900 transition"><svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="currentColor"><path d="M20 8.5a6.5 6.5 0 0 1-4.6-1.9v7.3a5.5 5.5 0 1 1-5.5-5.5c.3 0 .6 0 .9.1v3.1a2.5 2.5 0 1 0 1.6 2.3V2h3.1A6.5 6.5 0 0 0 20 5.5z"/></svg></a>
          <a href="#" aria-label="YouTube" class="text-gray-600 hover:text-zinc-900 transition"><svg xmlns="http://www.w3.org/2000/svg" class="size-6" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2s-.2-1.6-.8-2.2c-.8-.8-1.6-.8-2-0.9C17.7 2.8 12 2.8 12 2.8h0s-5.7 0-8.7.3c-.4 0-1.2.1-2 .9-.6.6-.8 2.2-.8 2.2S0 8.1 0 10v1.9c0 1.9.2 3.8.2 3.8s.2 1.6.8 2.2c.8.8 1.9.8 2.4.9 1.8.2 7.6.3 8.6.3s8.7 0 8.7-.3c.4 0 1.2-.1 2-.9.6-.6.8-2.2.8-2.2s.2-1.9.2-3.8V10c0-1.9-.2-3.8-.2-3.8zM9.6 13.9V8.9l5.2 2.5-5.2 2.5z"/></svg></a>
        </div>
      </div>
    </div>

    {{-- Footer Bottom (Guarantee and Payment Icons) --}}
    <div class="mt-10 md:mt-12 flex flex-col items-center justify-between gap-6 md:flex-row border-t border-gray-200 pt-6">

      <div class="flex flex-wrap justify-center md:justify-start items-center gap-3 text-xs text-gray-500">
        <span class="inline-flex items-center gap-2 text-gray-600 font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-gray-800" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l7 4v6c0 5-3.6 9.3-7 10-3.4-.7-7-5-7-10V6l7-4zm-1 13l5-5-1.4-1.4L11 12.2 9.4 10.6 8 12l3 3z"/></svg>
          Garansi 7 Hari
        </span>
        <span class="inline-flex items-center gap-2 text-gray-600 font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-gray-800" viewBox="0 0 24 24" fill="currentColor"><path d="M2 7h20v10H2z"/><path d="M6 17V7m12 10V7" stroke="currentColor" stroke-width="2"/></svg>
          COD / Transfer
        </span>
        <span class="inline-flex items-center gap-2 text-gray-600 font-medium">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-4 text-gray-800" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Aman & Terpercaya
        </span>
      </div>

      <div class="flex flex-wrap justify-center gap-4">
        <span class="text-sm font-semibold text-gray-800 hidden sm:block">Pembayaran:</span>
        {{-- Payment Icons (Semua kelas dark: telah dihapus) --}}
        <svg class="h-5 md:h-6 w-auto text-gray-900 opacity-80" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M22.9 8.1c-.2-.6-.6-1.1-1.1-1.5L1.8 1.9c-1.1-.5-2.2-.1-2.9.7-.7.8-.7 2.1-.2 3.2L3.9 14l2.1 4.5c.3.7.8 1.2 1.5 1.4l15.1 3.5c1.1.3 2.2 0 2.9-.7.7-.8.7-2.1.2-3.2L22.9 8.1zM5.3 12.1L2.4 6.8l18.5 5.7L5.3 12.1z"/>
        </svg>

        <svg class="h-5 md:h-6 w-auto text-gray-900 opacity-80" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <circle cx="9" cy="12" r="7.5"/>
            <circle cx="15" cy="12" r="7.5" opacity="0.8"/>
            <path d="M12 4.5h3c.3 0 .6.1.8.3.2.2.3.5.3.8v1.3H12V4.5zM12 19.5h3c.3 0 .6-.1.8-.3.2-.2.3-.5.3-.8v-1.3H12V19.5z"/>
        </svg>

        <svg class="h-5 md:h-6 w-auto text-gray-900 opacity-80" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM4 6h16v12H4V6zm7 3h2v6h-2V9zM7 9h2v6H7V9zm8 0h2v6h-2V9z"/>
        </svg>

        <svg class="h-5 md:h-6 w-auto text-gray-900 opacity-80" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zM4 6h16v12H4V6zm-1 5h2v2H3v-2zm16 0h2v2h-2v-2z"/>
        </svg>

        <svg class="h-5 md:h-6 w-auto text-gray-900 opacity-80" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="2" height="2" rx="0.5"/>
            <rect x="18" y="14" width="2" height="2" rx="0.5"/>
            <rect x="14" y="18" width="2" height="2" rx="0.5"/>
            <rect x="18" y="18" width="2" height="2" rx="0.5"/>
        </svg>
    </div>
    </div>

    {{-- Copyright & Utility Links --}}
    <div class="mt-8 pt-4">
      <div class="flex flex-col-reverse items-center justify-between gap-4 sm:flex-row">
        <p class="text-xs text-gray-500 order-last sm:order-first">
          © {{ date('Y') }} Sinergi Abadi. Seluruh hak cipta dilindungi.
        </p>
        <nav class="flex flex-wrap items-center justify-center sm:justify-end gap-3 sm:gap-4 text-xs">
          <a href="#" class="text-gray-600 hover:text-zinc-900 transition">Syarat & Ketentuan</a>
          <span class="h-3 w-px bg-gray-300 hidden sm:block"></span>
          <a href="#" class="text-gray-600 hover:text-zinc-900 transition">Kebijakan Privasi</a>
          <span class="h-3 w-px bg-gray-300 hidden sm:block"></span>
          <a href="#" class="text-gray-600 hover:text-zinc-900 transition">Kebijakan Cookie</a>
        </nav>
      </div>
    </div>
  </div>
</footer>
