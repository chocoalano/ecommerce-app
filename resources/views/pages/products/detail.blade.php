@extends('layouts.app')

@section('content')
@php
    // Default nilai bantu
    $variants = $product->productVariants ?? [];
    $defaultVariantId = $product->productVariants[0]->id ?? ($variants[0]->id ?? null);
    $currencyCode     = $currency ?? 'IDR';
    $stock            = (int) ($product->total_stock ?? 0);
    $isOutOfStock     = $stock <= 0;

    // Rating
    $avgRating  = (float) ($product->avg_rating ?? 0);
    $avgInt     = (int) floor($avgRating);
    $reviewsCnt = (int) ($product->reviews_count ?? 0);

    // Harga sudah disiapkan di controller seperti $priceFormatted (opsional)
@endphp

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">

    {{-- Breadcrumb --}}
    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="list-none p-0 inline-flex items-center">
            <li class="flex items-center">
                <a href="{{ url('/') }}" class="text-gray-500 hover:text-gray-700">Beranda</a>
                <svg class="w-3 h-3 text-gray-400 mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                </svg>
            </li>
            @if($primaryCategory)
                <li class="flex items-center">
                    <a href="{{ route('category', ['category' => $primaryCategory->slug]) }}" class="text-gray-500 hover:text-gray-700">
                        {{ $primaryCategory->name }}
                    </a>
                    <svg class="w-3 h-3 text-gray-400 mx-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                    </svg>
                </li>
            @endif
            <li class="text-gray-900 font-medium truncate max-w-xs sm:max-w-none">{{ $product->name }}</li>
        </ol>
    </nav>

    {{-- Main --}}
    <div class="lg:grid lg:grid-cols-2 lg:gap-12 xl:gap-16">
        {{-- Galeri --}}
        <div class="lg:sticky lg:top-8 self-start">
            <div class="aspect-square bg-gray-100 rounded-2xl overflow-hidden border border-gray-100">
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}"
                     class="w-full h-full object-contain p-8 sm:p-12" loading="eager">
            </div>

            @if($gallery->count() > 1)
            <div class="hidden sm:flex mt-4 gap-3 overflow-x-auto py-2">
                @foreach ($gallery as $idx => $thumb)
                    <div class="size-16 bg-gray-100 rounded-lg cursor-pointer ring-2 ring-transparent hover:ring-zinc-500 transition border border-gray-200">
                        <img src="{{ $thumb }}" alt="Thumbnail {{ $idx + 1 }}" class="w-full h-full object-contain p-2">
                    </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Detail + CTA --}}
        <div class="mt-8 lg:mt-0">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 leading-tight mb-2">{{ $product->name }}</h1>

            {{-- Rating + jumlah ulasan --}}
            <div class="flex items-center space-x-2 mb-4">
                <div class="flex items-center">
                    @for ($i = 1; $i <= 5; $i++)
                        @if ($i <= $avgInt)
                            <svg class="w-4 h-4 text-yellow-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
                                <path d="M20.924 7.625a1.523 1.523 0 0 0-1.396-1.028H13.765l-2.427-4.896a1.523 1.523 0 0 0-2.78 0L6.035 6.597H1.366A1.523 1.523 0 0 0 .686 7.828l4.47 4.787-1.155 6.4a1.523 1.523 0 0 0 2.3 1.637L12 17.276l5.46 3.092a1.523 1.523 0 0 0 2.3-1.637l-1.155-6.4 4.47-4.787Z"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 20">
                                <path d="M20.924 7.625a1.523 1.523 0 0 0-1.396-1.028H13.765l-2.427-4.896a1.523 1.523 0 0 0-2.78 0L6.035 6.597H1.366A1.523 1.523 0 0 0 .686 7.828l4.47 4.787-1.155 6.4a1.523 1.523 0 0 0 2.3 1.637L12 17.276l5.46 3.092a1.523 1.523 0 0 0 2.3-1.637l-1.155-6.4 4.47-4.787Z"/>
                            </svg>
                        @endif
                    @endfor
                </div>
                <span class="text-gray-900 text-sm font-semibold">{{ number_format($avgRating, 1) }}</span>
                <span class="text-gray-400">|</span>
                <a href="#reviews" class="text-sm text-gray-600 hover:text-zinc-800 font-medium">
                    {{ $reviewsCnt }} Ulasan
                </a>
            </div>

            {{-- Harga + stok --}}
            <div class="py-4 border-y border-gray-200 mb-6">
                @if(!empty($priceFormatted))
                    <p class="text-4xl font-extrabold text-gray-900">{{ $priceFormatted }}</p>
                @endif

                @if ($stock > 10)
                    <p class="mt-1 text-sm text-green-600 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Stok Tersedia ({{ $stock }}+)
                    </p>
                @elseif ($stock > 0)
                    <p class="mt-1 text-sm text-orange-600 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.299 2.56-1.299 3.325 0l3.5 5.927c.765 1.299-.184 2.974-1.662 2.974H6.418c-1.478 0-2.427-1.675-1.662-2.974l3.5-5.927z" clip-rule="evenodd"/></svg>
                        Stok Terbatas ({{ $stock }} unit)
                    </p>
                @else
                    <p class="mt-1 text-sm text-red-600 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        Stok Habis
                    </p>
                @endif
            </div>

            {{-- Opsi warna (opsional) --}}
            @if($colors->count())
            <div class="mb-8" x-data="{ selectedColor: '{{ $colors[0]['name'] ?? '' }}' }">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Warna:
                    <span x-text="selectedColor" class="font-normal text-gray-600"></span>
                </h3>
                <div class="flex space-x-3">
                    @foreach ($colors as $color)
                        <button type="button" x-on:click="selectedColor = '{{ $color['name'] }}'"
                            :class="selectedColor === '{{ $color['name'] }}' ? 'ring-2 ring-offset-2 ring-zinc-900' :
                                'hover:ring-2 hover:ring-gray-300'"
                            class="size-8 rounded-full border border-gray-300 transition duration-150"
                            @if($color['code']) style="background-color: {{ $color['code'] }}" @endif
                            title="{{ $color['name'] }}">
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Opsi varian (opsional; render jika $variants tersedia) --}}
            @if(!empty($variants) && count($variants))
            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-800 mb-2">Pilih Varian</label>
                <div class="flex flex-col gap-2">
                    @foreach ($variants as $v)
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="radio"
                                   name="variant_id"
                                   id="variant_id_{{ $v->id }}"
                                   value="{{ $v->id }}"
                                   {{ $loop->first ? 'checked' : '' }}
                                   class="form-radio text-zinc-900 focus:ring-zinc-900">
                            <span>
                                {{ $v->name ?? ('Varian #' . $v->variant_sku) }}
                                @if(isset($v->stock)) — Nama Varian: {{ $v->name }}@endif
                                @if(isset($v->stock)) — Stok: {{ $v->stock }}@endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Action Buttons (Wishlist + Add to Cart) --}}
            <div class="fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 p-4 lg:static lg:p-0 lg:border-t-0 lg:shadow-none z-10">
                <div class="flex flex-col sm:flex-row gap-4 max-w-3xl lg:max-w-full mx-auto">

                    {{-- Wishlist --}}
                    @php
                        $wishlistRoute = \Illuminate\Support\Facades\Route::has('product.wishlist') ? 'product.wishlist' : 'product.wislist';
                    @endphp
                    <a href="{{ route($wishlistRoute, ['id' => $product->id]) }}"
                       class="inline-flex items-center justify-center sm:flex-1 px-6 py-3 text-base font-semibold text-center text-gray-900
                              border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition duration-300">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-.318-.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                        Tambah ke Wishlist
                    </a>

                    {{-- Add to Cart Form (progressive: works with or without JS) --}}
                    <form id="add-to-cart-form"
                          action="{{ route('cart.items.store') }}"
                          method="POST"
                          class="contents"
                          data-ajax="true">
                        @csrf

                        {{-- Hidden fields --}}
                        <input type="hidden" name="currency" value="{{ $currencyCode }}">
                        <input type="hidden" id="variant_id_hidden" name="variant_id" value="{{ $defaultVariantId }}">
                        <input type="hidden" id="meta_color_hidden" name="meta_json[color]" value="">

                        <div class="flex items-stretch gap-3">
                            {{-- Qty stepper --}}
                            <div class="flex items-center border border-gray-300 rounded-full overflow-hidden">
                                <button type="button" class="px-3 py-2 text-gray-700 hover:bg-gray-100" data-qty-dec>-</button>
                                <input type="number" name="qty" id="qty" min="1" value="1"
                                       class="w-16 text-center outline-none border-0"
                                       {{ $isOutOfStock ? 'disabled' : '' }}>
                                <button type="button" class="px-3 py-2 text-gray-700 hover:bg-gray-100" data-qty-inc>+</button>
                            </div>

                            {{-- Submit --}}
                            <button type="submit"
                                    class="inline-flex items-center justify-center flex-1 px-6 py-3 text-base font-semibold text-center text-white
                                           bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300 disabled:opacity-60"
                                    {{ $isOutOfStock ? 'disabled' : '' }}>
                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                Tambah ke Keranjang
                            </button>
                        </div>

                        @if($isOutOfStock)
                            <p class="text-sm text-red-600 mt-2">Stok produk ini sedang habis.</p>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Deskripsi & Spesifikasi --}}
            <div class="mt-12 space-y-8">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Deskripsi Produk</h3>
                    <p class="text-gray-700 leading-relaxed">{{ $product->description }}</p>
                </div>

                @if(isset($product->specs) && is_array($product->specs))
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Spesifikasi Teknis</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($product->specs as $key => $value)
                                    <tr class="bg-white">
                                        <th scope="row" class="py-2 px-0 font-medium text-gray-900 w-1/3">
                                            {{ $key }}
                                        </th>
                                        <td class="py-2 px-0 text-gray-700 w-2/3">
                                            {{ $value }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <hr class="my-12 border-gray-200" />

    {{-- Ulasan Pelanggan --}}
    <div id="reviews" class="pt-4 pb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b border-gray-200 pb-3">
            Ulasan Pelanggan ({{ $reviewsCnt }})
        </h2>

        <div class="lg:grid lg:grid-cols-3 lg:gap-10">
            {{-- Ringkasan Rating --}}
            <div class="lg:col-span-1">
                <div class="sticky top-8 bg-gray-50 p-6 rounded-xl">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Ringkasan Rating</h3>

                    <div class="flex items-center space-x-3 mb-4">
                        <p class="text-5xl font-extrabold text-gray-900">{{ number_format($avgRating, 1) }}</p>
                        <div>
                            <div class="flex items-center text-2xl text-yellow-500">
                                {{ str_repeat('★', $avgInt) }}{{ str_repeat('☆', 5 - $avgInt) }}
                            </div>
                            <p class="text-sm text-gray-500">{{ $reviewsCnt }} Total Ulasan</p>
                        </div>
                    </div>

                    <div class="space-y-1">
                        @foreach ($ratingDistribution as $star => $percentage)
                            <div class="flex items-center space-x-3">
                                <span class="text-xs text-gray-600 w-4 font-medium">{{ $star }} ★</span>
                                <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                    <div class="h-2 bg-yellow-400 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 w-8 text-right font-medium">{{ $percentage }}%</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm text-gray-700 mb-3">Bagikan pengalaman Anda tentang produk ini.</p>
                        <button type="button"
                            class="w-full inline-flex items-center justify-center py-3 text-base font-semibold text-center text-white
                              bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                            Tulis Ulasan Anda
                        </button>
                    </div>
                </div>
            </div>

            {{-- Daftar Ulasan --}}
            <div class="lg:col-span-2">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-200 pb-4 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $reviews->count() }} Ulasan Terkini</h3>
                    <div class="flex items-center space-x-3 mt-3 sm:mt-0">
                        <label for="sort_reviews" class="text-sm text-gray-600 whitespace-nowrap">Urutkan:</label>
                        <select id="sort_reviews"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block p-2">
                            <option>Terbaru</option>
                            <option>Rating Tertinggi</option>
                            <option>Rating Terendah</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-8">
                    @forelse ($reviews as $review)
                        <div class="border-b border-gray-100 pb-8 last:border-b-0">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center space-x-3">
                                    <div class="size-8 rounded-full bg-zinc-100 grid place-items-center text-zinc-600 font-semibold text-sm shrink-0">
                                        {{ strtoupper(substr($review->author_name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $review->author_name ?? 'User' }}</p>
                                        <span class="text-xs text-gray-500">{{ optional($review->created_at)->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="text-sm text-yellow-500 shrink-0">
                                    {{ str_repeat('★', (int)$review->rating) }}{{ str_repeat('☆', 5 - (int)$review->rating) }}
                                </div>
                            </div>

                            <h4 class="text-base font-bold text-gray-800 mt-2 mb-1">{{ $review->title }}</h4>
                            <p class="text-gray-700 leading-relaxed text-sm">{{ $review->body }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Belum ada ulasan.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Scripts: sinkron varian/warna, qty stepper, dan submit AJAX --}}
<script>
(function() {
  // --- helpers ---
  function getCsrfToken() {
    const el = document.querySelector('meta[name="csrf-token"]');
    return el ? el.getAttribute('content') : '';
  }

  async function parseResponse(res) {
    const ct = (res.headers.get('content-type') || '').toLowerCase();
    if (ct.includes('application/json')) {
      return { kind: 'json', body: await res.json() };
    }
    // fallback text (kemungkinan HTML)
    return { kind: 'text', body: await res.text() };
  }

function showFriendlyError(res, payload) {
    // Payload bisa string (HTML/text) atau object {message, errors}
    let msg = 'Terjadi kesalahan.';
    if (typeof payload === 'object' && payload !== null) {
        msg = payload.message || msg;
    } else if (typeof payload === 'string') {
        // Cek beberapa pola umum
        if (res.status === 419 || payload.toLowerCase().includes('page expired')) {
            msg = 'Sesi kedaluwarsa (419). Muat ulang halaman, lalu coba lagi.';
        } else if (res.status === 404) {
            msg = 'Endpoint tidak ditemukan (404). Pastikan route cart.items.store ada.';
        } else if (res.status === 401 || res.status === 403 || /login/i.test(payload)) {
            msg = 'Perlu login/izin untuk melanjutkan. Silakan login terlebih dahulu.';
            // Redirect ke halaman login Laravel
            window.location.href = "{{ route('auth.login') }}";
            return;
        } else {
            msg = 'Server mengirim respons non-JSON. Coba muat ulang halaman.';
        }
    }
    alert(msg);
}

  // --- sinkron warna -> hidden meta_json[color] (opsional) ---
  const colorButtons = document.querySelectorAll('[x-data] button[title]');
  const colorHidden  = document.getElementById('meta_color_hidden');
  colorButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      if (!colorHidden) return;
      const colorName = btn.getAttribute('title');
      colorHidden.value = colorName || '';
    });
  });

  // --- sinkron varian select -> hidden input ---
  const variantSelect = document.getElementById('variant_id');
  const variantHidden = document.getElementById('variant_id_hidden');
  if (variantSelect && variantHidden) {
    variantHidden.value = variantSelect.value;
    variantSelect.addEventListener('change', () => {
      variantHidden.value = variantSelect.value;
    });
  }

  // --- qty stepper ---
  const dec = document.querySelector('[data-qty-dec]');
  const inc = document.querySelector('[data-qty-inc]');
  const qty = document.getElementById('qty');
  if (dec && inc && qty) {
    dec.addEventListener('click', () => {
      const v = Math.max(1, (parseInt(qty.value || '1', 10) - 1));
      qty.value = v;
    });
    inc.addEventListener('click', () => {
      const v = Math.max(1, (parseInt(qty.value || '1', 10) + 1));
      qty.value = v;
    });
  }

  // --- submit via fetch (robust) ---
  const form = document.getElementById('add-to-cart-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    // Hapus baris ini jika ingin fallback non-AJAX saat error:
    e.preventDefault();

    // Konstruksi payload dari FormData (dukungan nested meta_json)
    const fd = new FormData(form);
    const payload = {};
    for (const [key, val] of fd.entries()) {
      if (key.startsWith('meta_json[')) {
        const inner = key.replace(/^meta_json\[(.+)\]$/, '$1');
        payload.meta_json = payload.meta_json || {};
        payload.meta_json[inner] = val;
      } else {
        payload[key] = val;
      }
    }

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        credentials: 'same-origin',            // kirim cookie session
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': getCsrfToken(),
          'X-Requested-With': 'XMLHttpRequest', // biar Laravel paham ini AJAX
        },
        body: JSON.stringify(payload),
        redirect: 'follow',
      });

      const parsed = await parseResponse(res);

      if (!res.ok) {
        showFriendlyError(res, parsed.body);
        console.error('Fetch error', { status: res.status, body: parsed.body });
        return;
      }

      if (parsed.kind === 'json') {
        const data = parsed.body;
        // Sukses
        alert(data.message || 'Item ditambahkan.');
        window.dispatchEvent(new CustomEvent('cart:updated', { detail: data }));
      } else {
        // Server balas HTML (mis. login page). Tampilkan pesan ramah & log untuk dev.
        showFriendlyError(res, parsed.body);
        console.warn('Non-JSON response:', parsed.body.slice(0, 300));
      }

    } catch (err) {
      console.error(err);
      alert('Gagal terhubung ke server. Periksa koneksi internet Anda.');
    }
  });
})();
</script>

@endsection
