<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('layouts.partials.head') {{-- pastikan di dalamnya sudah ada @fluxAppearance, @vite, @livewireStyles --}}
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">

    @includeIf('layouts.partials.header')
    @includeIf('layouts.partials.sidebar')

    {{-- ===== SLOT KONTEN HALAMAN ===== --}}
    @yield('content')

    @includeIf('layouts.partials.footer')

    {{-- Wajib: Livewire sebelum Flux --}}
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
/**
 * Store global untuk cart & wishlist.
 * - Menangkap event:
 *   window.dispatchEvent(new CustomEvent('cart:updated', { detail: { count, totals, items } }))
 *   window.dispatchEvent(new CustomEvent('wishlist:updated', { detail: { count } }))
 * - Menyimpan ke sessionStorage supaya persist antar page (opsional).
 */
(function() {
  const CART_KEY = 'store:cart';
  const WISHLIST_KEY = 'store:wishlist';

  function loadState(key, fallback = {}) {
    try {
      const raw = sessionStorage.getItem(key);
      return raw ? JSON.parse(raw) : fallback;
    } catch (_) { return fallback; }
  }
  function saveState(key, state) {
    try { sessionStorage.setItem(key, JSON.stringify(state)); } catch (_) {}
  }

  document.addEventListener('alpine:init', () => {
    // ---- CART STORE ----
    Alpine.store('cart', {
      count: 0,
      totals: {},    // { subtotal, discount, shipping, tax, grand }
      items: [],     // opsional: array ringkas untuk mini cart

      bootstrap(seedCount = 0) {
        if (this._booted) return;
        const state = loadState(CART_KEY, null);
        if (state && typeof state.count === 'number') {
          this.count  = state.count;
          this.totals = state.totals || {};
          this.items  = state.items  || [];
        } else {
          this.count = Number(seedCount) || 0;
        }
        this._booted = true;
      },

      setFromEvent(detail = {}) {
        if (typeof detail.count === 'number') this.count = detail.count;
        if (detail.totals) this.totals = detail.totals;
        if (Array.isArray(detail.items)) this.items = detail.items;
        saveState(CART_KEY, { count: this.count, totals: this.totals, items: this.items });
      }
    });

    // ---- WISHLIST STORE ----
    Alpine.store('wishlist', {
      count: 0,
      bootstrap(seedCount = 0) {
        if (this._booted) return;
        const state = loadState(WISHLIST_KEY, null);
        if (state && typeof state.count === 'number') {
          this.count = state.count;
        } else {
          this.count = Number(seedCount) || 0;
        }
        this._booted = true;
      },
      setCount(n) {
        this.count = Math.max(0, Number(n) || 0);
        saveState(WISHLIST_KEY, { count: this.count });
      }
    });
  });

  // ---- GLOBAL LISTENERS (tanpa Alpine pun tetap jalan) ----
  window.addEventListener('cart:updated', (e) => {
    const st = (window.Alpine && Alpine.store) ? Alpine.store('cart') : null;
    if (st && st.setFromEvent) {
      st.setFromEvent(e.detail || {});
    } else {
      // fallback bila Alpine belum init — tetap persist
      const detail = e.detail || {};
      const prev = loadState(CART_KEY, { count: 0, totals: {}, items: [] });
      const next = {
        count: typeof detail.count === 'number' ? detail.count : prev.count,
        totals: detail.totals || prev.totals,
        items: Array.isArray(detail.items) ? detail.items : prev.items,
      };
      saveState(CART_KEY, next);
    }
  });

  window.addEventListener('wishlist:updated', (e) => {
    const cnt = (e.detail && typeof e.detail.count === 'number') ? e.detail.count : 0;
    const st = (window.Alpine && Alpine.store) ? Alpine.store('wishlist') : null;
    if (st && st.setCount) st.setCount(cnt);
    else saveState(WISHLIST_KEY, { count: cnt });
  });
})();
</script>
</body>

</html>
