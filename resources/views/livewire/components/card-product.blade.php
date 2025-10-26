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
        <button type="button"
            data-wishlist-toggle
            data-product-id="{{ $data->id ?? null }}"
            class="absolute right-6 top-6 inline-flex size-8 items-center justify-center
                   rounded-full border border-gray-300 bg-white/70 backdrop-blur-sm z-10
                   text-gray-400 hover:text-red-500 hover:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-300/50
                   transition-all duration-200"
            aria-label="Tambah ke Wishlist">
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
                data-add-to-cart data-action="{{ route('cart.items.store') }}"
                data-variant-id="{{ $data->id ?? null }}" data-qty="1" data-currency="IDR"
                data-meta='@json(["source" => "card", "sku" => $sku])'>
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
    (function () {
        function getCsrfToken() {
            const el = document.querySelector('meta[name="csrf-token"]');
            return el ? el.getAttribute('content') : '';
        }

        async function postJSON(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload),
                redirect: 'follow',
            });
            const parsed = (typeof parseResponse === 'function')
                ? await parseResponse(res)
                : { kind: 'json', body: await res.json().catch(() => ({})) };

            return { ok: res.ok, status: res.status, parsed };
        }

        function normalizeDetail(data) {
            const count =
                typeof data.count === 'number' ? data.count :
                    (typeof data.cart_count === 'number' ? data.cart_count : undefined);

            return {
                count,
                totals: data.totals,
                items: Array.isArray(data.items) ? data.items : undefined,
            };
        }

        // Update wishlist badge
        function updateWishlistBadge(count) {
            // Update badge di header jika ada
            const badge = document.querySelector('[data-wishlist-badge]');
            if (badge) {
                badge.textContent = count;
                badge.style.display = count > 0 ? 'flex' : 'none';
            }
        }

        // Update wishlist button state
        function updateWishlistButton(button, inWishlist) {
            const svg = button.querySelector('svg');
            if (inWishlist) {
                button.classList.add('text-red-500', 'border-red-500');
                button.classList.remove('text-gray-400', 'border-gray-300');
                svg.setAttribute('fill', 'currentColor');
            } else {
                button.classList.remove('text-red-500', 'border-red-500');
                button.classList.add('text-gray-400', 'border-gray-300');
                svg.setAttribute('fill', 'none');
            }
        }

        // Wishlist toggle handler
        document.addEventListener('click', async (e) => {
            const btn = e.target.closest('[data-wishlist-toggle]');
            if (!btn) return;

            const productId = btn.getAttribute('data-product-id');
            if (!productId) {
                console.warn('Wishlist toggle: missing product-id');
                return;
            }

            btn.disabled = true;

            try {
                const { ok, status, parsed } = await postJSON('{{ route('wishlist.toggle') }}', {
                    product_id: Number(productId)
                });

                if (!ok) {
                    if (status === 401) {
                        // Redirect to login
                        window.location.href = "{{ route('auth.login') }}";
                        return;
                    }

                    if (typeof showErrorToast === 'function') {
                        showErrorToast(parsed.body?.message || 'Gagal mengupdate wishlist');
                    } else {
                        alert(parsed.body?.message || 'Gagal mengupdate wishlist');
                    }
                    return;
                }

                // Update button state
                const data = parsed.body;
                updateWishlistButton(btn, data.in_wishlist);
                updateWishlistBadge(data.wishlist_count);

                // Show success message
                if (typeof showSuccessToast === 'function') {
                    showSuccessToast(data.message);
                } else {
                    console.log(data.message);
                }

            } catch (err) {
                console.error('Wishlist error:', err);
                if (typeof showErrorToast === 'function') {
                    showErrorToast('Gagal terhubung ke server');
                }
            } finally {
                btn.disabled = false;
            }
        });

        // Add to cart handler
        if (!window.__cartClickBound) {
            window.__cartClickBound = true;

            document.addEventListener('click', async (e) => {
                const btn = e.target.closest('[data-add-to-cart]');
                if (!btn) return;

                const url = btn.getAttribute('data-action');
                const variantId = btn.getAttribute('data-variant-id');
                const qty = parseInt(btn.getAttribute('data-qty') || '1', 10);
                const currency = btn.getAttribute('data-currency') || 'IDR';
                const metaStr = btn.getAttribute('data-meta');
                const meta = metaStr ? JSON.parse(metaStr) : {};

                if (!url || !variantId) {
                    console.warn('Add-to-cart: missing url/variant-id');
                    return;
                }

                btn.disabled = true;

                try {
                    const payload = {
                        product_id: Number(variantId),
                        quantity: Math.max(1, qty),
                        currency,
                        meta_json: meta,
                    };

                    const { ok, status, parsed } = await postJSON(url, payload);

                    if (!ok) {
                        console.log(ok, status, parsed);
                        if (typeof showFriendlyError === 'function') {
                            showFriendlyError({ status }, parsed.body);
                        } else if (typeof showErrorToast === 'function') {
                            showErrorToast('Gagal menambahkan ke keranjang.');
                        }
                        if (status === 401) {
                            window.location.href = "{{ route('auth.login') }}";
                        }
                        return;
                    }

                    window.location.href = "{{ route('cart.index') }}";
                } catch (err) {
                    console.log(err.message, err.code);
                    if (typeof showErrorToast === 'function') {
                        showErrorToast('Gagal terhubung ke server. Periksa koneksi internet Anda.');
                    }
                } finally {
                    btn.disabled = false;
                }
            });
        }

        // Load wishlist status on page load
        document.addEventListener('DOMContentLoaded', async () => {
            const wishlistButtons = document.querySelectorAll('[data-wishlist-toggle]');
            if (wishlistButtons.length === 0) return;

            const productIds = Array.from(wishlistButtons)
                .map(btn => btn.getAttribute('data-product-id'))
                .filter(id => id);

            if (productIds.length === 0) return;

            try {
                const { ok, parsed } = await postJSON('{{ route('wishlist.status') }}', {
                    product_ids: productIds.map(Number)
                });

                if (ok && parsed.body) {
                    const data = parsed.body;
                    const inWishlistIds = data.items || [];

                    // Update all buttons
                    wishlistButtons.forEach(btn => {
                        const productId = Number(btn.getAttribute('data-product-id'));
                        const inWishlist = inWishlistIds.includes(productId);
                        updateWishlistButton(btn, inWishlist);
                    });

                    // Update badge
                    if (data.count !== undefined) {
                        updateWishlistBadge(data.count);
                    }
                }
            } catch (err) {
                console.error('Failed to load wishlist status:', err);
            }
        });
    })();
</script>
