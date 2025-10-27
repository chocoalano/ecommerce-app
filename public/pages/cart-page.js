/* global jQuery */
(function ($, window, document) {
  'use strict';
  // ---------- Helpers ----------
  const SEL = {
    subtotal: '[data-cart-subtotal]',
    discount: '[data-cart-discount]',
    shipping: '[data-cart-shipping]',
    tax: '[data-cart-tax]',
    grand: '[data-cart-grand]',
    itemCount: '[data-cart-item-count]',
    itemRow: '[data-cart-item]',
    itemRowTotal: (id) => `[data-item-row-total="${id}"]`,
  };

  const getCsrf = () => $('meta[name="csrf-token"]').attr('content') || '';
  const isFn = (fn) => typeof fn === 'function';
  const toast = (msg, type = 'info', opts = {}) => {
    if (typeof window.showToast === 'function') window.showToast(msg, type, opts);
    else console[(type === 'error' ? 'error' : 'log')](msg);
  };

  const formatRupiah = (amount) =>
    'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(parseFloat(amount) || 0));

  const readNumberFromEl = ($el) => {
    const t = ($el.text() || '').replace(/[^\d]/g, '');
    return parseFloat(t) || 0;
  };

  const animateNumber = ($el, to, formatter = (v) => v, duration = 500) => {
    if (!$el || !$el.length) return;
    const from = readNumberFromEl($el);
    if (from === +to) return;

    // highlight bg
    $el.css({ transition: 'background-color 0.3s ease' }).css('backgroundColor', '#fef3c7');

    $({ val: from }).animate(
      { val: +to },
      {
        duration,
        step(now) {
          $el.text(formatter(now));
        },
        complete() {
          // clear highlight
          setTimeout(() => $el.css('backgroundColor', ''), 200);
        },
      }
    );
  };

  const withScaleFlash = ($el, cb) => {
    if (!$el || !$el.length) return;
    $el.css({ transition: 'all 0.3s ease' }).css({ transform: 'scale(1.05)', color: '#059669' });
    if (isFn(cb)) cb();
    setTimeout(() => $el.css({ transform: '', color: '' }), 600);
  };

  const toggleRowByValue = ($el, value) => {
    const $row = $el.closest('.flex');
    if ($row.length) $row.toggle(!!(parseFloat(value) > 0));
  };

  const setBtnLoading = ($btn, isLoading = true) => {
    if (!$btn || !$btn.length) return;
    if (isLoading) {
      $btn.prop('disabled', true).addClass('opacity-75 cursor-not-allowed');
      if (!$btn.find('.loading-spinner').length) {
        $btn.prepend(
          $('<div/>', {
            class:
              'loading-spinner inline-block w-4 h-4 border-2 border-white border-t-transparent border-solid rounded-full animate-spin mr-2',
          })
        );
      }
    } else {
      $btn.prop('disabled', false).removeClass('opacity-75 cursor-not-allowed');
      $btn.find('.loading-spinner').remove();
    }
  };

  const retry = async (operation, max = 3, delay = 1000) => {
    let lastErr;
    for (let i = 1; i <= max; i++) {
      try {
        return await operation();
      } catch (e) {
        lastErr = e;
        if (i === max) break;
        toast(`Mencoba ulang... (${i}/${max})`, 'info', { duration: 2000 });
        // exponential backoff
        /* eslint-disable no-await-in-loop */
        await new Promise((r) => setTimeout(r, delay * i));
        /* eslint-enable no-await-in-loop */
      }
    }
    throw lastErr;
  };

  // ---------- Cart Manager ----------
  const CartManager = {
    formatRupiah,

    updateCartTotals(totals = {}) {
      // Subtotal
      if (totals.subtotal != null) {
        const $el = $(SEL.subtotal);
        animateNumber($el, totals.subtotal, formatRupiah);
      }

      // Discount
      if (totals.discount != null) {
        const $el = $(SEL.discount);
        animateNumber(
          $el,
          totals.discount,
          (v) => (parseFloat(v) > 0 ? '-' + formatRupiah(v) : formatRupiah(0))
        );
        toggleRowByValue($el, totals.discount);
      }

      // Shipping
      if (totals.shipping != null) {
        const $el = $(SEL.shipping);
        animateNumber($el, totals.shipping, formatRupiah);
      }

      // Tax
      if (totals.tax != null) {
        const $el = $(SEL.tax);
        animateNumber($el, totals.tax, formatRupiah);
        toggleRowByValue($el, totals.tax);
      }

      // Grand total
      if (totals.grand != null) {
        const $el = $(SEL.grand);
        withScaleFlash($el, () => animateNumber($el, totals.grand, formatRupiah));
      }

      // Item count
      if (totals.item_count != null || totals.total_items != null) {
        const count = totals.item_count ?? totals.total_items ?? 0;
        const $el = $(SEL.itemCount);
        $el
          .css({ transition: 'transform 0.2s ease' })
          .css('transform', 'scale(1.2)')
          .text(count);
        setTimeout(() => $el.css('transform', 'scale(1)'), 200);
      }
    },

    updateItemRowTotal(itemId, newQty, unitPrice) {
      const rowTotal = (parseFloat(newQty) || 0) * (parseFloat(unitPrice) || 0);
      const $el = $(SEL.itemRowTotal(itemId));
      if (!$el.length) return;

      $el.css({ transition: 'all 0.3s ease', backgroundColor: '#dcfce7', transform: 'scale(1.05)' });
      $el.text(formatRupiah(rowTotal));
      setTimeout(() => $el.css({ backgroundColor: '', transform: 'scale(1)' }), 500);
    },

    removeItemRow(itemId) {
      const $row = $(`${SEL.itemRow}[data-cart-item="${itemId}"]`);
      if (!$row.length) return;

      $row
        .css('overflow', 'hidden')
        .animate({ opacity: 0, height: 0, paddingTop: 0, paddingBottom: 0, marginBottom: 0 }, 300, function () {
          $(this).remove();
          const remaining = $(SEL.itemRow).length;
          if (remaining === 0) {
            setTimeout(() => location.reload(), 500);
          }
        });
    },

    setButtonLoading($button, isLoading = true) {
      setBtnLoading($button, isLoading);
    },

    stepQty(id, delta) {
      const $el = $('#' + id);
      if (!$el.length) return;

      const min = parseInt($el.attr('min') || '1', 10);
      const max = parseInt($el.attr('max') || '9999', 10);
      const cur = parseInt($el.val() || '1', 10);
      const next = Math.max(min, Math.min(max, cur + delta));

      if (next !== cur) {
        $el
          .val(next)
          .css({ transition: 'all 0.2s ease', backgroundColor: delta > 0 ? '#dcfce7' : '#fef2f2', transform: 'scale(1.05)' });
        setTimeout(() => $el.css({ backgroundColor: '', transform: 'scale(1)' }), 200);
        CartManager.handleQtyInputChange($el[0]); // trigger debounce update
      }
    },

    async updateQtySubmit(form) {
      const $form = $(form);
      const $btn = $form.find('button[type="submit"]');
      const $qty = $form.find('input[name="qty"]');
      const itemId = ($form.attr('action') || '').split('/').pop();

      if (!$qty.length || !itemId) {
        toast('Data item tidak valid.', 'error');
        return false;
      }

      const newQty = parseInt($qty.val(), 10);
      if (newQty < 1) {
        toast('Kuantitas minimal adalah 1.', 'error');
        $qty.val($qty.data('originalValue') || 1);
        return false;
      }

      const originalQty = parseInt($qty.data('originalValue') ?? $qty.val(), 10);
      if (newQty === originalQty) {
        toast('Kuantitas tidak berubah.', 'info');
        return false;
      }

      try {
        CartManager.setButtonLoading($btn, true);
        $qty.prop('disabled', true);

        const doRequest = () =>
          $.ajax({
            url: $form.attr('action'),
            method: 'POST', // Laravel-friendly with override
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': getCsrf(),
              Accept: 'application/json',
              'Content-Type': 'application/json',
            },
            data: JSON.stringify({ qty: newQty, _method: 'PATCH' }),
            dataType: 'json',
          });

        const data = await retry(doRequest, 3, 1000);

        if (data?.totals) {
          CartManager.updateCartTotals(data.totals);

          // Unit price: prefer [data-unit-price], fallback parse "Rp ..."
          const $row = $form.closest(SEL.itemRow);
          let unitPrice = parseFloat($row.find('[data-unit-price]').data('unitPrice'));
          if (!unitPrice || Number.isNaN(unitPrice)) {
            const txt = ($row.find('.text-xs.text-gray-500').text() || '').match(/Rp([\d.,]+)/);
            if (txt && txt[1]) unitPrice = parseFloat((txt[1] || '').replace(/[^\d]/g, '')) || 0;
          }
          if (unitPrice) CartManager.updateItemRowTotal(itemId, newQty, unitPrice);

          $qty.data('originalValue', newQty);
        }

        toast('Kuantitas berhasil diperbarui!', 'success');

        // CustomEvent broadcast (kompatibel dengan komponen lain)
        window.dispatchEvent(
          new CustomEvent('cart:updated', {
            detail: { type: 'quantity_updated', itemId, newQty, totals: data?.totals },
          })
        );
        window.dispatchEvent(
          new CustomEvent('cartUpdated', {
            detail: {
              cartItemCount: data?.totals?.item_count ?? data?.totals?.total_items,
              cartTotal: data?.totals?.grand ?? data?.totals?.grand_total,
              type: 'quantity_updated',
            },
          })
        );
      } catch (err) {
        console.error('Update quantity error:', err);
        toast(err?.responseJSON?.message || err?.message || 'Kesalahan jaringan saat memperbarui kuantitas.', 'error');
        $qty.val($qty.data('originalValue') ?? originalQty);
      } finally {
        CartManager.setButtonLoading($btn, false);
        $qty.prop('disabled', false);
      }

      return false;
    },

    async handleDeleteConfirm(event, form) {
      event.preventDefault();

      const $form = $(form);
      const $btn = $form.find('button[type="submit"]');
      const itemId = ($form.attr('action') || '').split('/').pop();
      const $row = $form.closest(SEL.itemRow);

      if (!itemId) {
        toast('Data item tidak valid.', 'error');
        return false;
      }

      try {
        CartManager.setButtonLoading($btn, true);
        $row.css({ opacity: 0.5, pointerEvents: 'none' });

        const doRequest = () =>
          $.ajax({
            url: $form.attr('action'),
            method: 'POST', // Laravel-friendly with override
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': getCsrf(),
              Accept: 'application/json',
              'Content-Type': 'application/json',
            },
            data: JSON.stringify({ _method: 'DELETE' }),
            dataType: 'json',
          });

        const data = await retry(doRequest, 3, 1000);

        // Broadcast dulu supaya header/cart-indicator ikut update
        window.dispatchEvent(
          new CustomEvent('cart:updated', { detail: { type: 'item_deleted', itemId, totals: data?.totals } })
        );
        window.dispatchEvent(
          new CustomEvent('cartUpdated', {
            detail: {
              cartItemCount: data?.totals?.item_count ?? data?.totals?.total_items,
              cartTotal: data?.totals?.grand ?? data?.totals?.grand_total,
              type: 'item_deleted',
            },
          })
        );

        // Hapus row + cek empty
        CartManager.removeItemRow(itemId);
        toast('Item berhasil dihapus dari keranjang!', 'success');

        setTimeout(() => {
          if (!$(SEL.itemRow).length) {
            toast('Keranjang Anda sekarang kosong.', 'info');
            setTimeout(() => location.reload(), 1500);
          }
        }, 500);
      } catch (err) {
        console.error('Delete item error:', err);
        toast(err?.responseJSON?.message || err?.message || 'Kesalahan jaringan saat menghapus item.', 'error');
        $row.css({ opacity: 1, pointerEvents: 'auto' });
        CartManager.setButtonLoading($btn, false);
      }

      return false;
    },

    // Debounced autosave untuk input qty
    handleQtyInputChange(input) {
      const $input = $(input);
      if (!$input.data('originalValue')) $input.data('originalValue', $input.val());

      // tombol indikasi "Menyimpan..."
      const $form = $input.closest('form');
      const $btn = $form.find('button[type="submit"]');
      if ($btn.length) {
        $btn.text('Menyimpan...').prop('disabled', true).addClass('opacity-75');
      }

      // gunakan timer per-input (disimpan di $.data)
      const oldTimer = $input.data('timer');
      if (oldTimer) clearTimeout(oldTimer);

      const timer = setTimeout(() => {
        const currentValue = parseInt($input.val(), 10);
        const originalValue = parseInt($input.data('originalValue'), 10);

        // reset state tombol
        if ($btn.length) {
          $btn.text('Perbarui').prop('disabled', false).removeClass('opacity-75');
        }

        // hanya update jika valid & berubah
        if ($form.length && currentValue !== originalValue && currentValue >= 1) {
          CartManager.updateQtySubmit($form[0]);
        }
      }, 1500);

      $input.data('timer', timer);
    },

    init() {
      // Inisialisasi nilai awal originalValue untuk semua qty
      $('input[name="qty"]').each(function () {
        $(this).data('originalValue', $(this).val());
      });

      // Event delegation untuk input qty
      $(document)
        .on('input', 'input[name="qty"]', function () {
          CartManager.handleQtyInputChange(this);
        })
        .on('blur', 'input[name="qty"]', function () {
          const $i = $(this);
          const val = parseInt($i.val(), 10);
          const min = parseInt($i.attr('min') || '1', 10);
          const max = parseInt($i.attr('max') || '9999', 10);

          if (val < min) {
            $i.val(min);
            toast(`Kuantitas minimal adalah ${min}`, 'warning');
          } else if (val > max) {
            $i.val(max);
            toast(`Kuantitas maksimal adalah ${max}`, 'warning');
          }
        })
        .on('keydown', 'input[name="qty"]', function (e) {
          const $i = $(this);
          if (e.key === 'Enter') {
            e.preventDefault();
            const $form = $i.closest('form');
            if ($form.length) CartManager.updateQtySubmit($form[0]);
          } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            CartManager.stepQty(this.id, 1);
          } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            CartManager.stepQty(this.id, -1);
          }
        });

      // Simpan unit price ke data-attr (aman bila server belum mengisi data-unit-price)
      $(SEL.itemRow).each(function (idx) {
        try {
          const $row = $(this);
          const $holder = $row.find('[data-unit-price]');
          if ($holder.length && !$holder.data('unitPrice')) {
            // fallback parse dari teks "Rp ..."
            const txt = ($row.find('.text-xs.text-gray-500').text() || '').match(/Rp([\d.,]+)/);
            if (txt && txt[1]) $holder.data('unitPrice', parseFloat(txt[1].replace(/[^\d]/g, '')) || 0);
          }
        } catch (e) {
          console.warn(`Failed to parse unit price for item ${idx}:`, e);
        }
      });

      // Shortcut: Ctrl/Cmd + U -> update semua qty (yang berubah)
      $(document).on('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'u') {
          e.preventDefault();
          $('form[action*="cart/items/"]').each(function () {
            CartManager.updateQtySubmit(this);
          });
          toast('Memperbarui semua kuantitas...', 'info');
        }
      });

      // Network status
      let offlineToastShown = false;
      $(window)
        .on('online', function () {
          offlineToastShown = false;
          toast('Koneksi internet tersambung kembali.', 'success');
        })
        .on('offline', function () {
          if (!offlineToastShown) {
            toast('Koneksi internet terputus. Perubahan akan disimpan saat kembali online.', 'warning', { duration: 0 });
            offlineToastShown = true;
          }
        });

      // Periodic (visible only)
      let syncInterval = null;
      document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
          syncInterval = setInterval(() => {
            const pending = $(`${SEL.itemRow} .opacity-75`);
            if (!pending.length && navigator.onLine) {
              console.log('🔄 Cart sync check');
              // tempatkan optional sync call di sini bila dibutuhkan
            }
          }, 30000);
        } else if (syncInterval) {
          clearInterval(syncInterval);
        }
      });

      // Session pending info
      try {
        const saved = sessionStorage.getItem('cart_pending_updates');
        if (saved) {
          const items = JSON.parse(saved);
          if (Array.isArray(items) && items.length > 0) {
            toast(`Ada ${items.length} perubahan yang belum tersimpan.`, 'info');
          }
        }
      } catch {
        sessionStorage.removeItem('cart_pending_updates');
      }
    },
  };

  // ---------- Bootstrap ----------
  $(function () {
    CartManager.init();
  });

  // ---------- Backward compatibility (fungsi global yang sudah dipakai di view) ----------
  window.CartManager = CartManager;
  window.stepQty = (id, delta) => CartManager.stepQty(id, delta);
  window.updateQtySubmit = (form) => CartManager.updateQtySubmit(form);
  window.handleDeleteConfirm = (event, form) => CartManager.handleDeleteConfirm(event, form);
  window.handleQtyInputChange = (input) => CartManager.handleQtyInputChange(input);

  // Unhandled promise errors khusus cart
  window.addEventListener('unhandledrejection', function (evt) {
    const msg = evt?.reason?.message || '';
    if (msg && msg.toLowerCase().includes('cart')) {
      console.error('Unhandled cart error:', evt.reason);
      toast('Terjadi kesalahan tidak terduga pada keranjang.', 'error');
      evt.preventDefault?.();
    }
  });
})(window.jQuery, window, document);
