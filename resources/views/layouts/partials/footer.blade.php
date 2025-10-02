<footer class="bg-white border-t border-zinc-300/70">
  <div class="mx-auto w-full max-w-5/6 px-4 sm:px-6 lg:px-8 py-14">
    <!-- Newsletter / Promo -->
    <div class="relative overflow-hidden rounded-2xl bg-gray-100 p-8 sm:p-10">
      <div class="grid items-center gap-6 md:grid-cols-3">
        <div class="md:col-span-2">
          <h3 class="text-2xl sm:text-3xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">
            Dapatkan kabar diskon & produk baru
          </h3>
          <p class="mt-2 text-zinc-600 dark:text-zinc-400">
            Berlangganan newsletter kami. Gratis, bisa berhenti kapan saja.
          </p>
        </div>
        <form class="md:col-span-1" action="#" method="post" aria-label="Form Newsletter">
          @csrf
          <div class="flex gap-3">
            <flux:input
              name="email"
              type="email"
              placeholder="nama@email.com"
              class="w-full"
              sr-only-label="Email"
              required
            />
            <flux:button type="submit" variant="primary" color="zinc" class="whitespace-nowrap">
              Berlangganan
            </flux:button>
          </div>
          <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
            Dengan berlangganan, Anda menyetujui <a href="#" class="underline hover:no-underline">Kebijakan Privasi</a>.
          </p>
        </form>
      </div>
      <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-zinc-200/60 dark:bg-zinc-800/60 blur-3xl"></div>
    </div>

    <!-- Link Columns -->
    <div class="mt-14 grid grid-cols-2 gap-10 sm:grid-cols-3 lg:grid-cols-5">
      <div class="col-span-2 lg:col-span-2">
        <a href="/" class="inline-flex items-center gap-2">
          <!-- Ganti dengan logo Anda -->
          <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 font-bold">SA</span>
          <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Sinergi Abadi</span>
        </a>
        <p class="mt-4 max-w-md text-sm text-zinc-600 dark:text-zinc-400">
          Bahan baku minuman berkualitas untuk HORECA dan UMKM. Cepat, segar, dan terpercaya.
        </p>

        <!-- Kontak -->
        <ul class="mt-6 space-y-2 text-sm">
          <li class="text-zinc-700 dark:text-zinc-300">
            <span class="font-medium">WhatsApp:</span> <a href="https://wa.me/62xxxxxxxxxx" class="underline hover:no-underline">+62 8xx-xxxx-xxxx</a>
          </li>
          <li class="text-zinc-700 dark:text-zinc-300">
            <span class="font-medium">Email:</span> <a href="mailto:support@domain.com" class="underline hover:no-underline">support@domain.com</a>
          </li>
          <li class="text-zinc-700 dark:text-zinc-300">
            <span class="font-medium">Jam Operasional:</span> Senin–Sabtu 09:00–18:00 WIB
          </li>
        </ul>

        <!-- Sosial -->
        <div class="mt-6 flex items-center gap-4">
          <a href="#" aria-label="Instagram" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">
            <!-- lucide-instagram -->
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
          </a>
          <a href="#" aria-label="Facebook" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">
            <!-- lucide-facebook -->
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M13.5 22v-8h2.5l.5-3.5h-3V8.5c0-.9.3-1.5 1.6-1.5H17V3.9C16.6 3.8 15.4 3.7 14 3.7c-2.6 0-4.4 1.6-4.4 4.6v2.2H7v3.5h2.6V22h3.9z"/></svg>
          </a>
          <a href="#" aria-label="TikTok" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">
            <!-- simple tiktok icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M20 8.5a6.5 6.5 0 0 1-4.6-1.9v7.3a5.5 5.5 0 1 1-5.5-5.5c.3 0 .6 0 .9.1v3.1a2.5 2.5 0 1 0 1.6 2.3V2h3.1A6.5 6.5 0 0 0 20 5.5z"/></svg>
          </a>
          <a href="#" aria-label="YouTube" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="currentColor"><path d="M23.5 6.2s-.2-1.6-.8-2.2c-.8-.8-1.6-.8-2-0.9C17.7 2.8 12 2.8 12 2.8h0s-5.7 0-8.7.3c-.4 0-1.2.1-2 .9-.6.6-.8 2.2-.8 2.2S0 8.1 0 10v1.9c0 1.9.2 3.8.2 3.8s.2 1.6.8 2.2c.8.8 1.9.8 2.4.9 1.8.2 7.6.3 8.6.3s8.7 0 8.7-.3c.4 0 1.2-.1 2-.9.6-.6.8-2.2.8-2.2s.2-1.9.2-3.8V10c0-1.9-.2-3.8-.2-3.8zM9.6 13.9V8.9l5.2 2.5-5.2 2.5z"/></svg>
          </a>
        </div>
      </div>

      <nav aria-label="Shop" class="space-y-4">
        <h4 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Belanja</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Matcha & Tea</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Powder Series</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Syrup & Sauce</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Packaging</a></li>
        </ul>
      </nav>

      <nav aria-label="Perusahaan" class="space-y-4">
        <h4 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Perusahaan</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Tentang Kami</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Karier</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Blog</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Press</a></li>
        </ul>
      </nav>

      <nav aria-label="Bantuan" class="space-y-4">
        <h4 class="text-sm font-semibold uppercase tracking-wider text-zinc-900 dark:text-zinc-100">Bantuan</h4>
        <ul class="space-y-2 text-sm">
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Cara Belanja</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Pengiriman</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Retur & Refund</a></li>
          <li><a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Hubungi Kami</a></li>
        </ul>
      </nav>
    </div>

    <!-- Trust / Payments -->
    <div class="mt-12 flex flex-col items-center justify-between gap-6 sm:flex-row">
      <div class="flex items-center gap-4 text-xs text-zinc-500 dark:text-zinc-400">
        <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200/70 dark:border-zinc-800 px-3 py-1">
          <!-- shield-check -->
          <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l7 4v6c0 5-3.6 9.3-7 10-3.4-.7-7-5-7-10V6l7-4zm-1 13l5-5-1.4-1.4L11 12.2 9.4 10.6 8 12l3 3z"/></svg>
          Garansi 7 Hari
        </span>
        <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200/70 dark:border-zinc-800 px-3 py-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M2 7h20v10H2z"/><path d="M6 17V7m12 10V7" stroke="currentColor" stroke-width="2"/></svg>
          COD / Transfer
        </span>
        <span class="inline-flex items-center gap-2 rounded-full border border-zinc-200/70 dark:border-zinc-800 px-3 py-1">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          Aman & Terpercaya
        </span>
      </div>

      <div class="flex flex-wrap items-center justify-center gap-3">
        <!-- Ganti dengan logo png/svg asli payment gateway Anda -->
        <img src="/img/payments/visa.svg" alt="Visa" class="h-6 opacity-80">
        <img src="/img/payments/mastercard.svg" alt="Mastercard" class="h-6 opacity-80">
        <img src="/img/payments/jcb.svg" alt="JCB" class="h-6 opacity-80">
        <img src="/img/payments/amex.svg" alt="Amex" class="h-6 opacity-80">
        <img src="/img/payments/bca.svg" alt="BCA" class="h-6 opacity-80">
        <img src="/img/payments/bri.svg" alt="BRI" class="h-6 opacity-80">
        <img src="/img/payments/qris.svg" alt="QRIS" class="h-6 opacity-80">
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="mt-10 border-t border-zinc-300/70 dark:border-zinc-800 pt-6">
      <div class="flex flex-col-reverse items-center justify-between gap-4 sm:flex-row">
        <p class="text-xs text-zinc-500 dark:text-zinc-400">
          © {{ date('Y') }} Sinergi Abadi. Seluruh hak cipta dilindungi.
        </p>
        <nav class="flex items-center gap-4 text-xs">
          <a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Syarat & Ketentuan</a>
          <span class="h-3 w-px bg-zinc-300 dark:bg-zinc-700"></span>
          <a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Kebijakan Privasi</a>
          <span class="h-3 w-px bg-zinc-300 dark:bg-zinc-700"></span>
          <a href="#" class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Kebijakan Cookie</a>
        </nav>
      </div>
    </div>
  </div>
</footer>
