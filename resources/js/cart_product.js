(function ($) {
        'use strict';

        // ===== CSRF & AJAX default =====
        const csrfToken = $('meta[name="csrf-token"]').attr('content') || '';
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        // POST JSON helper (kembalikan jqXHR/Promise)
        function postJSON(url, payload) {
            return $.ajax({
                url,
                method: 'POST',
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
            const $badge = $('[data-wishlist-badge]');
            if ($badge.length) {
                $badge.text(count);
                $badge.css('display', count > 0 ? 'flex' : 'none');
            }
        }

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

        function updateCartBadgeQuick(count) {
            const $badge = $('[data-cart-badge]');
            if (!$badge.length) return;
            $badge.text(count > 99 ? '99+' : String(count));
            $badge.css('display', count > 0 ? 'inline-flex' : 'none');
        }

        function pingLivewireCartUpdated() {
            try {
                if (window.Livewire && typeof window.Livewire.dispatch === 'function') {
                    window.Livewire.dispatch('cartUpdated');
                }
            } catch (e) {
                // no-op
            }
        }

        // ===== Wishlist toggle (delegated, namespaced) =====
        $(document).off('click.wishlistToggle')
            .on('click.wishlistToggle', '[data-wishlist-toggle]', async function (e) {
                const $btn = $(this);
                const productId = Number($btn.data('product-id') || 0);
                if (!productId) {
                    console.warn('Wishlist toggle: missing product-id');
                    return;
                }

                $btn.prop('disabled', true);

                try {
                    const res = await postJSON(urlPostItemWishlistToggle, { product_id: productId });

                    // sukses
                    updateWishlistButton($btn, !!res.in_wishlist);
                    if (typeof res.wishlist_count === 'number') {
                        updateWishlistBadge(res.wishlist_count);
                    }
                    (typeof window.showSuccessToast === 'function')
                        ? window.showSuccessToast(res.message || 'Berhasil mengupdate wishlist.')
                        : console.log(res.message || 'Wishlist updated');

                } catch (xhr) {
                    // gagal
                    if (xhr?.status === 401) {
                        window.location.href = "{{ route('auth.login') }}";
                    } else if (typeof window.showErrorToast === 'function') {
                        window.showErrorToast(xhr?.responseJSON?.message || 'Gagal mengupdate wishlist');
                    } else {
                        alert(xhr?.responseJSON?.message || 'Gagal mengupdate wishlist');
                    }
                } finally {
                    $btn.prop('disabled', false);
                }
            });

        // ===== Add to Cart (delegated, namespaced) =====
        $(document).off('click.cartAdd')
            .on('click.cartAdd', '[data-add-to-cart]', async function (e) {
                const $btn = $(this);

                const url = String($btn.data('action') || '');
                const variantId = Number($btn.data('variant-id') || 0); // asumsikan = product_id
                const qty = Math.max(1, parseInt($btn.data('qty') || '1', 10));
                const currency = String($btn.data('currency') || 'IDR');
                let meta = {};

                const metaRaw = $btn.attr('data-meta');
                if (metaRaw) {
                    try { meta = JSON.parse(metaRaw); } catch (_) { }
                }

                if (!url || !variantId) {
                    console.warn('Add-to-cart: missing url/variant-id');
                    return;
                }

                $btn.prop('disabled', true);

                try {
                    const payload = {
                        product_id: variantId,
                        quantity: qty,
                        currency,
                        meta_json: meta
                    };

                    const res = await postJSON(url, payload);

                    const n = normalizeDetail(res);

                    // 1) feedback cepat (opsional) bila API kirim count
                    if (typeof n.count === 'number') {
                        updateCartBadgeQuick(n.count);
                    }

                    // 2) minta Livewire re-render CartIndicator
                    pingLivewireCartUpdated();

                    // 3) toast
                    (typeof window.showSuccessToast === 'function')
                        ? window.showSuccessToast(n.message || 'Berhasil ditambahkan ke keranjang.')
                        : console.log(n.message || 'Added to cart');

                } catch (xhr) {
                    if (xhr?.status === 401) {
                        window.location.href = "{{ route('auth.login') }}";
                    } else if (typeof window.showFriendlyError === 'function') {
                        window.showFriendlyError({ status: xhr?.status }, xhr?.responseJSON);
                    } else if (typeof window.showErrorToast === 'function') {
                        window.showErrorToast('Gagal menambahkan ke keranjang.');
                    } else {
                        alert('Gagal menambahkan ke keranjang.');
                    }
                } finally {
                    $btn.prop('disabled', false);
                }
            });

        // ===== Inisialisasi status wishlist sekali saja =====
        $(function () {
            if (window.__wishlistStatusInit) return;
            window.__wishlistStatusInit = true;

            const $btns = $('[data-wishlist-toggle]');
            if (!$btns.length) return;

            const ids = $btns.map(function () {
                return $(this).data('product-id');
            }).get().filter(Boolean);

            if (!ids.length) return;

            postJSON(urlPostItemWishlistStatus, { product_ids: ids })
                .done(function (res) {
                    const inWishlistIds = Array.isArray(res?.items) ? res.items : [];
                    $btns.each(function () {
                        const $btn = $(this);
                        const id = Number($btn.data('product-id') || 0);
                        const inWishlist = inWishlistIds.includes(id);
                        updateWishlistButton($btn, inWishlist);
                    });
                    if (typeof res?.count === 'number') {
                        updateWishlistBadge(res.count);
                    }
                })
                .fail(function (xhr) {
                    console.error('Failed to load wishlist status:', xhr);
                });
        });

        // Opsional: livewire:load hook
        document.addEventListener('livewire:load', function () {
            // siap menerima Livewire.dispatch('cartUpdated')
        });

    })(jQuery);
