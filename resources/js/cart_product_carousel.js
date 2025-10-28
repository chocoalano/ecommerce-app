(function ($) {
  'use strict';

  // ===== CSRF global =====
  const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
  $.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
  });

  // ===== Toast wrapper: selalu pakai yang SUDAH ada =====
  function toastSuccess(msg) {
    (window.ToastManager?.success || window.showSuccessToast || console.log)(msg);
  }
  function toastError(msg) {
    (window.ToastManager?.error || window.showErrorToast || alert)(msg);
  }

  // ===== Helper AJAX JSON =====
  function postJSON(url, payload) {
    return $.ajax({
      url, method: 'POST',
      data: JSON.stringify(payload || {}),
      contentType: 'application/json; charset=UTF-8',
      dataType: 'json'
    });
  }

  // ===== Utils =====
  function normalizeDetail(data) {
    const count = (typeof data?.count === 'number')
      ? data.count
      : (typeof data?.cart_count === 'number' ? data.cart_count : undefined);
    return {
      count,
      totals: data?.totals,
      items: Array.isArray(data?.items) ? data.items : undefined,
      message: data?.message
    };
  }

  function updateWishlistBadge(count) {
    const $b = $('[data-wishlist-badge]');
    if ($b.length) { $b.text(count).css('display', count > 0 ? 'flex' : 'none'); }
  }
  function updateWishlistButton($btn, inWishlist) {
    const $svg = $btn.find('svg');
    if (inWishlist) {
      $btn.addClass('text-red-500 border-red-500').removeClass('text-gray-400 border-gray-300');
      $svg.attr('fill', 'currentColor');
    } else {
      $btn.removeClass('text-red-500 border-red-500').addClass('text-gray-400 border-gray-300');
      $svg.attr('fill', 'none');
    }
  }
  function updateCartBadgeQuick(count) {
    const $b = $('[data-cart-badge]');
    if ($b.length) { $b.text(count > 99 ? '99+' : String(count)).css('display', count > 0 ? 'inline-flex' : 'none'); }
  }
  function pingLivewireCartUpdated() {
    try { if (window.Livewire?.dispatch) window.Livewire.dispatch('cartUpdated'); } catch (_) {}
  }

  // ===== Wishlist toggle =====
  $(document).off('click.wishlistToggle')
    .on('click.wishlistToggle', '[data-wishlist-toggle]', async function () {
      const $btn = $(this);
      const productId = Number($btn.data('product-id') || 0);
      if (!productId) return console.warn('Wishlist toggle: missing product-id');

      $btn.prop('disabled', true);
      try {
        const res = await postJSON(urlPostItemWishlistToggleCarousel, { product_id: productId });
        updateWishlistButton($btn, !!res.in_wishlist);
        if (typeof res.wishlist_count === 'number') updateWishlistBadge(res.wishlist_count);
        toastSuccess(res.message || 'Wishlist diperbarui.');
      } catch (xhr) {
        if (xhr?.status === 401) return (window.location.href = "{{ route('auth.login') }}");
        toastError(xhr?.responseJSON?.message || 'Gagal mengupdate wishlist');
      } finally {
        $btn.prop('disabled', false);
      }
    });

  // ===== Add to Cart =====
  $(document).off('click.cartAdd')
    .on('click.cartAdd', '[data-add-to-cart]', async function () {
      const $btn = $(this);
      const url = String($btn.data('action') || '');
      const variantId = Number($btn.data('variant-id') || 0);
      const qty = Math.max(1, parseInt($btn.data('qty') || '1', 10));
      const currency = String($btn.data('currency') || 'IDR');

      let meta = {};
      const metaRaw = $btn.attr('data-meta'); if (metaRaw) { try { meta = JSON.parse(metaRaw); } catch {} }

      if (!url || !variantId) return console.warn('Add-to-cart: missing url/variant-id');

      $btn.prop('disabled', true);
      try {
        const res = await postJSON(url, { product_id: variantId, quantity: qty, currency, meta_json: meta });
        const n = normalizeDetail(res);
        if (typeof n.count === 'number') updateCartBadgeQuick(n.count); // feedback cepat (opsional)
        pingLivewireCartUpdated(); // sinkronkan komponen Livewire CartIndicator
        toastSuccess(n.message || 'Berhasil ditambahkan ke keranjang.');
        // Jika ingin redirect:
        // window.location.href = "{{ route('cart.index') }}";
      } catch (xhr) {
        if (xhr?.status === 401) return (window.location.href = "{{ route('auth.login') }}");
        const msg = xhr?.responseJSON?.message || 'Gagal menambahkan ke keranjang.';
        (window.showFriendlyError ? window.showFriendlyError({ status: xhr?.status }, xhr?.responseJSON) : toastError(msg));
      } finally {
        $btn.prop('disabled', false);
      }
    });

  // ===== Init sekali: status wishlist awal =====
  $(function () {
    if (window.__wishlistStatusInit) return; window.__wishlistStatusInit = true;

    const $btns = $('[data-wishlist-toggle]'); if (!$btns.length) return;
    const ids = $btns.map(function(){ return $(this).data('product-id'); }).get().filter(Boolean);
    if (!ids.length) return;

    postJSON(urlPostItemWishlistStatusCarousel, { product_ids: ids })
      .done(function (res) {
        const inWishlistIds = Array.isArray(res?.items) ? res.items : [];
        $btns.each(function () {
          const $btn = $(this);
          const id = Number($btn.data('product-id') || 0);
          updateWishlistButton($btn, inWishlistIds.includes(id));
        });
        if (typeof res?.count === 'number') updateWishlistBadge(res.count);
      })
      .fail(function (xhr) {
        console.error('Failed to load wishlist status:', xhr);
      });
  });

})(jQuery);
