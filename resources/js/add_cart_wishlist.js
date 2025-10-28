// resources/js/ecommerce-logic.js
// Logika E-commerce (Wishlist & Cart) Gabungan dan Dioptimalkan

(function ($) {
    'use strict';

    // =========================================================
    // 1. KONFIGURASI DAN INIITALISASI DASAR
    // =========================================================

    // Pastikan variabel URL global sudah didefinisikan di Blade/HTML Anda.
    // Contoh:
    // const urlPostItemWishlistToggle = '/api/wishlist/toggle';
    // const urlPostItemWishlistStatus = '/api/wishlist/status';
    // Gunakan satu set variabel URL global yang konsisten.

    const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';

    // Setup AJAX global
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    });

    // =========================================================
    // 2. HELPER (AJAX & TOAST)
    // =========================================================

    /** Helper POST JSON (kembalikan jqXHR/Promise) */
    function postJSON(url, payload) {
        return $.ajax({
            url,
            method: 'POST',
            data: JSON.stringify(payload || {}),
            contentType: 'application/json; charset=UTF-8',
            dataType: 'json'
        });
    }

    /** Toast wrapper: menggunakan fungsi global yang diekspos oleh toast-manager.js */
    function toastSuccess(msg) {
        (window.showSuccessToast || console.log)(msg);
    }
    function toastError(msg) {
        // Menggunakan window.showErrorToast dari toast-manager atau fallback ke alert
        (window.showErrorToast || alert)(msg);
    }

    // =========================================================
    // 3. UTILS (BADGE & LIVEWIRE PING)
    // =========================================================

    /** Normalisasi data respons API Wishlist/Cart */
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

    /** Update badge Wishlist di Header */
    function updateWishlistBadge(count) {
        const $badge = $('[data-wishlist-badge]');
        if ($badge.length) {
            $badge.text(count);
            $badge.css('display', count > 0 ? 'flex' : 'none');
        }
    }

    /** Update tampilan tombol Wishlist (warna & border) */
    function updateWishlistButton($btn, inWishlist) {
        const $svg = $btn.find('svg');
        if (inWishlist) {
            $btn.addClass('text-red-500 border-red-500')
                .removeClass('text-gray-400 border-gray-300');
            $svg.attr('fill', 'currentColor');
        } else {
            $btn.removeClass('text-red-500 border-red-500')
                .addClass('text-gray-400 border-gray-300');
            $svg.attr('fill', 'none');
        }
    }

    /** Update badge Cart secara cepat */
    function updateCartBadgeQuick(count) {
        const $badge = $('[data-cart-badge]');
        if (!$badge.length) return;
        $badge.text(count > 99 ? '99+' : String(count));
        $badge.css('display', count > 0 ? 'inline-flex' : 'none');
    }

    /** Kirim event ke Livewire untuk sinkronisasi komponen Cart */
    function pingLivewireCartUpdated() {
        try {
            if (window.Livewire?.dispatch) {
                window.Livewire.dispatch('cartUpdated');
            }
        } catch (_) {
            // Livewire mungkin belum di-load.
        }
    }

    // =========================================================
    // 4. EVENT HANDLER: WISHLIST TOGGLE
    // =========================================================

    // Delegated click handler untuk tombol Wishlist
    $(document).off('click.wishlistToggle')
        .on('click.wishlistToggle', '[data-wishlist-toggle]', async function (e) {
            const $btn = $(this);
            const productId = Number($btn.data('product-id') || 0);

            // Memastikan variabel URL tersedia secara global
            if (typeof urlPostItemWishlistToggle === 'undefined') {
                console.error('Missing global variable: urlPostItemWishlistToggle');
                return;
            }
            if (!productId) {
                return console.warn('Wishlist toggle: missing product-id');
            }

            $btn.prop('disabled', true);

            try {
                // Menggunakan urlPostItemWishlistToggle (versi non-carousel)
                const res = await postJSON(urlPostItemWishlistToggle, { product_id: productId });

                updateWishlistButton($btn, !!res.in_wishlist);
                if (typeof res.wishlist_count === 'number') {
                    updateWishlistBadge(res.wishlist_count);
                }
                toastSuccess(res.message || 'Berhasil mengupdate wishlist.');

            } catch (xhr) {
                if (xhr?.status === 401) {
                    // Pastikan route('auth.login') sudah didefinisikan sebagai variabel global
                    window.location.href = "{{ route('auth.login') }}";
                } else {
                    toastError(xhr?.responseJSON?.message || 'Gagal mengupdate wishlist');
                }
            } finally {
                $btn.prop('disabled', false);
            }
        });

    // =========================================================
    // 5. EVENT HANDLER: ADD TO CART
    // =========================================================

    // Delegated click handler untuk tombol Add to Cart
    $(document).off('click.cartAdd')
        .on('click.cartAdd', '[data-add-to-cart]', async function (e) {
            const $btn = $(this);
            const url = String($btn.data('action') || ''); // URL Add-to-Cart
            const variantId = Number($btn.data('variant-id') || 0);
            const qty = Math.max(1, parseInt($btn.data('qty') || '1', 10));
            const currency = String($btn.data('currency') || 'IDR');

            let meta = {};
            const metaRaw = $btn.attr('data-meta');
            if (metaRaw) { try { meta = JSON.parse(metaRaw); } catch (_) { } }

            if (!url || !variantId) {
                return console.warn('Add-to-cart: missing url/variant-id');
            }

            $btn.prop('disabled', true);

            try {
                const payload = { product_id: variantId, quantity: qty, currency, meta_json: meta };
                const res = await postJSON(url, payload);
                const n = normalizeDetail(res);

                // 1) feedback cepat
                if (typeof n.count === 'number') {
                    updateCartBadgeQuick(n.count);
                }

                // 2) sinkronkan Livewire
                pingLivewireCartUpdated();

                // 3) toast
                toastSuccess(n.message || 'Berhasil ditambahkan ke keranjang.');

            } catch (xhr) {
                if (xhr?.status === 401) {
                    window.location.href = "{{ route('auth.login') }}";
                } else if (typeof window.showFriendlyError === 'function') {
                    // Gunakan showFriendlyError (jika ada)
                    window.showFriendlyError({ status: xhr?.status }, xhr?.responseJSON);
                } else {
                    toastError(xhr?.responseJSON?.message || 'Gagal menambahkan ke keranjang.');
                }
            } finally {
                $btn.prop('disabled', false);
            }
        });

    // =========================================================
    // 6. INISIALISASI STATUS WISHLIST (PADA LOAD HALAMAN)
    // =========================================================

    // Hanya dijalankan sekali saat DOM Ready
    $(function () {
        if (window.__wishlistStatusInit) return;
        window.__wishlistStatusInit = true;

        const $btns = $('[data-wishlist-toggle]');
        if (!$btns.length) return;

        // Memastikan variabel URL status tersedia secara global
        if (typeof urlPostItemWishlistStatus === 'undefined') {
            console.error('Missing global variable: urlPostItemWishlistStatus');
            return;
        }

        const ids = $btns.map(function () {
            return Number($(this).data('product-id') || 0);
        }).get().filter(Boolean);

        if (!ids.length) return;

        // Menggunakan urlPostItemWishlistStatus (versi non-carousel)
        postJSON(urlPostItemWishlistStatus, { product_ids: ids })
            .done(function (res) {
                const inWishlistIds = Array.isArray(res?.items) ? res.items : [];
                $btns.each(function () {
                    const $btn = $(this);
                    const id = Number($btn.data('product-id') || 0);
                    updateWishlistButton($btn, inWishlistIds.includes(id));
                });
                if (typeof res?.count === 'number') {
                    updateWishlistBadge(res.count);
                }
            })
            .fail(function (xhr) {
                console.error('Failed to load wishlist status:', xhr);
            });
    });

    // =========================================================
    // 7. LIVEWIRE HOOKS
    // =========================================================

    // Opsional: livewire:load hook (Untuk kode yang sangat bergantung pada Livewire)
    // document.addEventListener('livewire:load', function () {
    //     // Kode inisialisasi tambahan Livewire di sini
    // });

})(jQuery);
