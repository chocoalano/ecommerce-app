<div class="bg-white py-10">
    <div class="mx-auto max-w-5/6"> {{-- max-w-5/6 diganti 7xl untuk standar Tailwind --}}
        <div x-data="{
            scrollBy(delta) {
                const el = $refs.track;
                el.scrollBy({ left: delta, behavior: 'smooth' });
            }
        }" class="w-full">
            {{-- Carousel --}}
            <div class="relative">
                {{-- Judul dan Kontrol Navigasi --}}
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between mb-8 sm:mb-10">
                    <div class="max-w-4xl mb-4 sm:mb-0">
                        <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                            {{ $title }}
                        </h2>
                        <p class="mt-2 text-gray-600 text-base">{{ $description ?? '' }}</p>
                    </div>

                    {{-- Kontrol Navigasi dan Tombol Lihat Semua (Disusun Ulang) --}}
                    <div class="flex items-center space-x-4">
                        {{-- Tombol Navigasi (Dikelompokkan) --}}
                        <div class="hidden sm:flex space-x-3"> {{-- Sembunyikan navigasi tombol di mobile jika carousel
                            bisa di-scroll --}}
                            {{-- Tombol Sebelumnya --}}
                            <button type="button" x-on:click="scrollBy(-300)"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 transition duration-200"
                                aria-label="Sebelumnya">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="w-5 h-5">
                                    <path fill-rule="evenodd"
                                        d="M11.77 15.22a.75.75 0 01-1.06 0l-5.75-5.75a.75.75 0 010-1.06l5.75-5.75a.75.75 0 011.06 1.06L6.56 10l5.21 5.21a.75.75 0 010 1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            {{-- Tombol Berikutnya --}}
                            <button type="button" x-on:click="scrollBy(300)"
                                class="inline-flex size-10 items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 transition duration-200"
                                aria-label="Berikutnya">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                    class="w-5 h-5">
                                    <path fill-rule="evenodd"
                                        d="M8.23 4.78a.75.75 0 011.06 0l5.75 5.75a.75.75 0 010 1.06l-5.75 5.75a.75.75 0 01-1.06-1.06L14.44 10 8.23 4.78a.75.75 0 010-1.06z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        {{-- Tombol Lihat Semua --}}
                        <a href="#" class="inline-flex items-center justify-center px-5 py-3 text-base font-semibold text-center text-white
                                   bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300
                                   transition duration-300">
                            Lihat Semua
                            {{-- SVG Arrow Up Right --}}
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                class="ml-2 h-4 w-4">
                                <path fill-rule="evenodd"
                                    d="M5.22 14.78a.75.75 0 001.06 0l7.22-7.22v4.275a.75.75 0 001.5 0V5.625a.75.75 0 00-.75-.75h-5.65a.75.75 0 000 1.5h4.275l-7.22 7.22a.75.75 0 000 1.06z"
                                    clip-rule="evenodd" />
                            </svg>
                        </a>
                    </div>
                </div>
                {{-- Track --}}
                <div x-ref="track" class="mx-auto py-4
                        flex gap-6 overflow-x-auto overscroll-x-contain
                        snap-x snap-mandatory pb-8" style="scrollbar-width: thin;">
                    @foreach ($data as $i => $p)
                        {{-- Gunakan data dari $p untuk card --}}
                        @php
                            $id = $p['id'] ?? null;
                            $sku = $p['sku'] ?? 'default_sku';
                            $title_card = $p['title'] ?? 'Nama Produk';
                            $price = $p['price'] ?? 'Rp 0';
                            $image = asset('storage/' . ($p['image'] ?? 'default.svg'));
                        @endphp

                        <article class="group snap-start shrink-0
                                    {{-- PERBAIKAN: Menggunakan lebar kustom yang valid dan responsif untuk carousel --}}
                                    w-[280px] sm:w-[288px] lg:w-[260px]
                                    h-full rounded-xl bg-white ring-1 ring-gray-100 overflow-hidden
                                    transition duration-300 hover:shadow-xl hover:-translate-y-0.5">

                            {{-- MEDIA & INTERAKSI --}}
                            <div class="relative p-4">
                                {{-- Wishlist floating --}}
                                <button type="button"
                                    class="absolute right-6 top-6 inline-flex size-8 items-center justify-center
                                               rounded-full border border-gray-300 bg-white/70 backdrop-blur-sm z-10
                                               text-gray-400 hover:text-red-500 hover:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-300/50"
                                    aria-label="Tambah ke Wishlist">
                                    {{-- SVG Heart Icon --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="w-4 h-4 transition duration-200">
                                        <path
                                            d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                        </path>
                                    </svg>
                                </button>

                                {{-- Gambar Produk (dengan efek zoom hover) --}}
                                <div class="aspect-[4/3] rounded-lg grid place-items-center overflow-hidden bg-gray-50/70">
                                    <img src="{{ $image }}" alt="{{ $title_card }}" loading="lazy"
                                        class="w-full h-full object-contain transition duration-500 ease-in-out group-hover:scale-105">
                                </div>
                            </div>

                            {{-- BODY & CTA --}}
                            <div class="px-4 pt-2 pb-5">
                                {{-- Nama: Pastikan judul terpotong dengan baik di lebar manapun --}}
                                <h3 class="text-base font-semibold text-gray-800 leading-snug h-10 overflow-hidden mb-1"
                                    title="{{ $title_card }}">
                                    {{ Str::limit($title_card, 45, '…') }}
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
                                    <button type="button"
                                        class="inline-flex items-center justify-center w-12 h-10 p-0 text-white bg-zinc-900
                                                   rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 shadow-md transition duration-300"
                                        aria-label="Tambah ke keranjang" data-add-to-cart
                                        data-action="{{ route('cart.items.store') }}"
                                        data-variant-id="{{ $id }}" data-qty="1" data-currency="IDR"
                                        data-meta='@json(["source" => "card", "sku" => $sku])'>
                                        {{-- SVG Shopping Cart Icon --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                                            class="w-5 h-5">
                                            <path fill-rule="evenodd"
                                                d="M7.5 6v.75H5.513c-.96 0-1.76.756-1.76 1.716v3.248A2.32 2.32 0 004 12.75a2.32 2.32 0 002.507 2.234l.03-.004c.002 0 .004-.002.006-.002h.008l1.096.794a3.3 3.3 0 003.882 0l1.096-.794h.016l.004-.002c.002 0 .004-.002.006-.002A2.32 2.32 0 0020 12.75a2.32 2.32 0 00-.008-2.502v-3.248c0-.96-.8-1.716-1.76-1.716H16.5V6a4.5 4.5 0 10-9 0zM12 9a.75.75 0 01.75.75v3a.75.75 0 01-1.5 0V9.75A.75.75 0 0112 9z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        function getCsrfToken() {
            const el = document.querySelector('meta[name="csrf-token"]');
            return el ? el.getAttribute('content') : '';
        }
        if (window.__cartClickBound) return; // hindari double bind
        window.__cartClickBound = true;

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
            // Backend kamu bisa kirim "count" atau "cart_count"
            const count =
                typeof data.count === 'number' ? data.count :
                    (typeof data.cart_count === 'number' ? data.cart_count : undefined);

            return {
                count,
                totals: data.totals,
                items: Array.isArray(data.items) ? data.items : undefined,
            };
        }

        // Klik tombol "Tambah ke Keranjang" di card
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

            // state loading
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
                    // pesan error ramah (pakai helper kamu)
                    if (typeof showFriendlyError === 'function') {
                        showFriendlyError({ status }, parsed.body);
                    } else {
                        showErrorToast('Gagal menambahkan ke keranjang.');
                    }
                    console.error('Add-to-cart error', status, parsed.body);
                    if (status === 401) {
                        window.location.href = "{{ route('auth.login') }}";
                    }
                    return;
                }
                // Redirect ke halaman keranjang setelah sukses
                window.location.href = "{{ route('cart.index') }}";
            } catch (err) {
                console.error(err.message, err.stack, err.status);

                // Check if it's a 401 authentication error
                if (err.status === 401) {
                    window.location.href = "{{ route('auth.login') }}";
                    return;
                }

                showErrorToast('Gagal terhubung ke server. Periksa koneksi internet Anda.');
            } finally {
                btn.disabled = false;
            }
        });
    })();
</script>
