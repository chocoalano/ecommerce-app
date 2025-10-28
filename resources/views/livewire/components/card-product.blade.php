@php
    $data = \App\Models\Product\Product::where('sku', $sku)->first();
@endphp
<article class="group snap-start shrink-0
           w-full max-w-sm-[280px]
           h-full rounded-xl bg-white border border-gray-200 overflow-hidden
           transition duration-300 hover:shadow-xl hover:-translate-y-0.5">

    {{-- MEDIA & INTERAKSI --}}
    <div class="relative p-4">
        {{-- Wishlist floating --}}
        <button type="button" data-wishlist-toggle data-product-id="{{ $data->id ?? null }}" class="absolute right-6 top-6 inline-flex size-8 items-center justify-center
                   rounded-full border border-gray-300 bg-white/70 backdrop-blur-sm z-10
                   text-gray-400 hover:text-red-500 hover:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-300/50
                   transition-all duration-200" aria-label="Tambah ke Wishlist">
            {{-- SVG Heart Icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 transition duration-200">
                <path
                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                </path>
            </svg>
        </button>

        {{-- Gambar Produk (dengan efek zoom hover) --}}
        <div class="aspect-[4/3] rounded-lg grid place-items-center overflow-hidden bg-gray-50/70">
            <img src="{{ $image }}" alt="{{ $title }}" loading="lazy"
                class="w-full h-full object-contain transition duration-500 ease-in-out group-hover:scale-105">
        </div>
    </div>

    {{-- BODY & CTA --}}
    <div class="px-4 pt-2 pb-5">
        {{-- Nama: Pastikan judul terpotong dengan baik di lebar manapun --}}
        <h3 class="text-base font-semibold text-gray-800 leading-snug h-10 overflow-hidden mb-1" title="{{ $title }}">
            {{ Str::limit($title ?? '', 45, '…') }}
        </h3>

        {{-- Harga (Dibuat Lebih Menonjol) --}}
        <div class="flex items-center gap-2 mb-4">
            <span class="text-xl font-extrabold text-zinc-900">
                {{ $price }}
            </span>
            {{-- Opsional: Tambahkan diskon atau rating di sini --}}
        </div>

        {{-- Footer CTA (Tombol Lebih Terpadu) --}}
        <div class="flex gap-2">
            {{-- Tombol Detail (Secondary/Outline) --}}
            <a href="{{ route('products.show', ['sku' => $sku]) }}" class="inline-flex items-center justify-center flex-1 px-3 py-2 text-sm font-medium text-center text-gray-700
                       border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100
                       transition duration-300">
                Detail
            </a>

            {{-- Tombol Keranjang (Primary/Icon Button) --}}
            <button type="button" class="inline-flex items-center justify-center w-12 h-10 p-0 text-white bg-zinc-900
           rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 shadow-md transition duration-300
           disabled:opacity-60 disabled:cursor-not-allowed" aria-label="Tambah ke keranjang" {{--===Data utk JS===--}}
                data-add-to-cart data-action="{{ route('cart.items.store') }}" data-variant-id="{{ $data->id ?? null }}"
                data-qty="1" data-currency="IDR" data-meta='@json(["source" => "card", "sku" => $sku])'>
                {{-- SVG Shopping Cart Icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                    <path fill-rule="evenodd"
                        d="M7.5 6v.75H5.513c-.96 0-1.76.756-1.76 1.716v3.248A2.32 2.32 0 004 12.75a2.32 2.32 0 002.507 2.234l.03-.004c.002 0 .004-.002.006-.002h.008l1.096.794a3.3 3.3 0 003.882 0l1.096-.794h.016l.004-.002c.002 0 .004-.002.006-.002A2.32 2.32 0 0020 12.75a2.32 2.32 0 00-.008-2.502v-3.248c0-.96-.8-1.716-1.76-1.716H16.5V6a4.5 4.5 0 10-9 0zM12 9a.75.75 0 01.75.75v3a.75.75 0 01-1.5 0V9.75A.75.75 0 0112 9z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>
</article>

<script>
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
                    const res = await postJSON('{{ route('wishlist.toggle') }}', { product_id: productId });

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

            postJSON('{{ route('wishlist.status') }}', { product_ids: ids })
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
</script>
