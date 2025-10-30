// add-to-cart.js
(function () {
  'use strict';

  if (window.addToCartInitialized) return;
  window.addToCartInitialized = true;

  const onReady = (cb) => {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', cb, { once: true });
    } else cb();
  };

  function pingLivewireCartUpdated() {
            try {
                if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                    window.Livewire.dispatch('cartUpdated');
                }
            } catch (e) {
                // no-op
            }
        }

  onReady(() => {
    const root       = document.getElementById('productPage') || document.body;
    const qtyInput   = document.getElementById('qty');
    const decBtn     = document.querySelector('[data-qty-dec]');
    const incBtn     = document.querySelector('[data-qty-inc]');
    const form       = document.getElementById('add-to-cart-form');
    const submitBtn  = (form && form.querySelector('button[type="submit"]')) || null;

    // Ambil stock & OOS dari data-attribute (fallback: Infinity)
    const maxStock      = Number(root?.dataset.stock ?? Number.POSITIVE_INFINITY);
    const isOutOfStock  = String(root?.dataset.isOutOfStock ?? 'false') === 'true';

    if (!qtyInput || !decBtn || !incBtn || !form || !submitBtn) {
      console.warn('Add-to-cart elements missing in DOM');
      return;
    }

    // ------- Helpers -------
    const clamp = (v, min, max) => Math.min(Math.max(v, min), max);
    const toInt = (v, d = 0) => { const n = parseInt(v, 10); return Number.isNaN(n) ? d : n; };

    function setButtonLoading(button, isLoading = true) {
      if (!button) return;
      if (isLoading) {
        if (!button.dataset.originalHtml) button.dataset.originalHtml = button.innerHTML;
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        button.innerHTML = `
          <span class="inline-block w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          Menambahkan...
        `;
      } else {
        button.disabled = false;
        button.classList.remove('opacity-75', 'cursor-not-allowed');
        if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
      }
    }

    function showToast(message, type = 'info') {
      if (typeof window.showToast === 'function') return window.showToast(message, type);
      if (typeof window.toastManager?.show === 'function') return window.toastManager.show(message, type);
      // Fallback
      if (type === 'error') alert(message);
      else console.log(`[${type}] ${message}`);
    }

    function addSuccessAnimation(button) {
      if (!button) return;
      const orig = button.dataset.originalHtml || button.innerHTML;
      button.innerHTML = `
        <svg class="w-5 h-5 mr-2 animate-bounce text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        Berhasil Ditambahkan!
      `;
      button.classList.remove('bg-zinc-900', 'hover:bg-zinc-700', 'focus:ring-zinc-300');
      button.classList.add('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-300');

      setTimeout(() => {
        button.innerHTML = orig;
        button.classList.remove('bg-green-600', 'hover:bg-green-700', 'focus:ring-green-300');
        button.classList.add('bg-zinc-900', 'hover:bg-zinc-700', 'focus:ring-zinc-300');
      }, 1800);
    }

    // ------- Qty Stepper -------
    decBtn.addEventListener('click', () => {
      qtyInput.value = clamp(toInt(qtyInput.value, 1) - 1, 1, Math.max(1, maxStock));
    });
    incBtn.addEventListener('click', () => {
      qtyInput.value = clamp(toInt(qtyInput.value, 1) + 1, 1, Math.max(1, maxStock));
    });
    qtyInput.addEventListener('input', () => {
      qtyInput.value = clamp(toInt(qtyInput.value, 1), 1, Math.max(1, maxStock));
    });
    qtyInput.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowUp') { e.preventDefault(); incBtn.click(); }
      else if (e.key === 'ArrowDown') { e.preventDefault(); decBtn.click(); }
      else if (e.key === 'Enter') {
        e.preventDefault();
        if (form.requestSubmit) form.requestSubmit();
        else form.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });

    // Ctrl+A = add to cart (kecuali fokus di input/textarea/select)
    document.addEventListener('keydown', (e) => {
      const tag = (e.target && e.target.tagName) || '';
      if (e.ctrlKey && e.key.toLowerCase() === 'a' && !['INPUT','TEXTAREA','SELECT'].includes(tag)) {
        e.preventDefault();
        if (!isOutOfStock) {
          if (form.requestSubmit) form.requestSubmit();
          else form.dispatchEvent(new Event('submit', { cancelable: true }));
        }
      }
    });

    // ------- Submit (AJAX) -------
    form.addEventListener('submit', handleAddToCart, { once: false });

    async function handleAddToCart(ev) {
      ev.preventDefault();

      if (isOutOfStock || maxStock <= 0) {
        showToast('Produk ini sedang habis stok.', 'error');
        return;
      }

      const qty = toInt(qtyInput.value, 1);
      if (qty < 1) { showToast('Kuantitas minimal adalah 1.', 'error'); qtyInput.value = 1; return; }
      if (Number.isFinite(maxStock) && qty > maxStock) {
        showToast(`Kuantitas maksimal adalah ${maxStock}.`, 'error');
        qtyInput.value = maxStock;
        return;
      }

      setButtonLoading(submitBtn, true);
      try {
        const formData = new FormData(form);
        const res = await fetch(form.action, {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: formData
        });

        const raw = await res.text();        // tangkap apapun (JSON/HTML)
        let data = null;
        try { data = JSON.parse(raw); } catch (_) {}

        if (!res.ok) {
          const msg = data?.message || `Gagal menambahkan (HTTP ${res.status}).`;
          showToast(msg, 'error');
          if (res.status === 401) {
            window.location.href = '/auth/login';
          }
          return;
        }

        if (data?.success) {
          showToast(data.message || 'Produk berhasil ditambahkan ke keranjang!', 'success');
          qtyInput.value = 1;

          // update badge keranjang (opsional)
          if (data.cart_count && document.getElementById('cartCount')) {
            document.getElementById('cartCount').textContent = data.cart_count;
          }
          pingLivewireCartUpdated();
          addSuccessAnimation(submitBtn);
        } else {
          const msg = data?.message || 'Gagal menambahkan produk ke keranjang.';
          showToast(msg, 'error');
        }
      } catch (err) {
        console.error(err);
        showToast('Koneksi bermasalah. Silakan coba lagi.', 'error');
      } finally {
        setButtonLoading(submitBtn, false);
      }
    }
  });
})();
