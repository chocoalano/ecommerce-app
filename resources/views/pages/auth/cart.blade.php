@extends('layouts.app')

@section('content')
@php
    /** @var \App\Models\Cart|null $cart */
    $items = $cart?->cartItems ?? collect();

    // Helper format Rupiah
    $formatRupiah = function ($amount) {
        try { return 'Rp' . number_format((float)$amount, 0, ',', '.'); } catch (\Throwable $e) { return 'Rp0'; }
    };

    // Ambil nilai dari model cart kalau tersedia, kalau belum dihitung, fallback hitung manual dari item
    $subtotal = $cart?->subtotal_amount ?? $items->sum(fn($i) => (float)$i->row_total);
    $shippingFee = $cart?->shipping_amount ?? 25000; // bebas: bisa 0 atau logika ongkir kamu
    $discount = $cart?->discount_amount ?? 0;
    $tax = $cart?->tax_amount ?? 0;
    $grand = $cart?->grand_total ?? max(0, ($subtotal - $discount) + $shippingFee + $tax);
@endphp

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight mb-8">Keranjang Belanja Anda</h1>

    @if ($items->isEmpty())
        {{-- Keranjang Kosong --}}
        <div class="flex flex-col items-center justify-center py-20 bg-gray-50 rounded-xl border border-gray-200 shadow-md">
            <svg class="w-16 h-16 text-gray-400 mb-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-4h10.222m-7.722 0c.264 0 .52.105.707.293l3.19 3.19c.188.188.513.188.701 0l3.188-3.188A.993.993 0 0 1 19 8.5V11m-14 0V8.5a.993.993 0 0 1 .293-.707L8 4"/>
            </svg>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Keranjang Anda Kosong</h2>
            <p class="text-gray-500 mb-6 text-center">Yuk, temukan produk menarik yang ingin Anda beli!</p>
            <a href="{{ route('category') }}"
               class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-center text-white bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                Mulai Belanja Sekarang
            </a>
        </div>
    @else
        {{-- Grid: Items (2/3) + Ringkasan (1/3) --}}
        <div class="lg:grid lg:grid-cols-3 lg:gap-12 xl:gap-16">

            {{-- KOLOM 1-2: Daftar Item --}}
            <div class="lg:col-span-2 space-y-6">
                @foreach ($items as $item)
                    @php
                        /** @var \App\Models\CartItem $item */
                        $variant = $item->variant;
                        $product = $variant?->product;
                        $title = $product?->name ?? $variant?->name ?? ('#' . $item->variant_id);
                        $image = $product?->primary_image_url
                            ?? $product?->productMedia[0]->url
                            ?? asset('images/galaxy-z-flip7-share-image.png');
                        $stock = (int) ($variant->stock ?? 0);
                        $unitPrice = (float) $item->unit_price;
                        $rowTotal = (float) $item->row_total;
                        $qty = (int) $item->qty;
                        $variantText = '';
                        if (is_array($item->meta_json)) {
                            $variantText = collect($item->meta_json)
                                ->map(fn($v, $k) => ucfirst($k) . ': ' . $v)
                                ->implode(', ');
                        }
                    @endphp

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-start bg-gray-100 p-4 sm:p-6 rounded-xl transition hover:shadow-lg border border-gray-200">
                        {{-- Gambar --}}
                        <div class="size-20 sm:size-28 flex-shrink-0 rounded-lg overflow-hidden bg-white mx-auto sm:mx-0 mb-4 sm:mb-0 border border-gray-200">
                            <img src="{{ $image }}" alt="{{ $title }}" class="w-full h-full object-contain p-2">
                        </div>

                        {{-- Detail --}}
                        <div class="ml-0 sm:ml-4 flex-grow flex flex-col justify-between w-full">
                            <div class="flex justify-between items-start w-full">
                                <div class="pr-4">
                                    <a href="{{ route('product.show', $product?->slug ?? $product?->id ?? '#') }}"
                                       class="text-lg font-semibold text-gray-900 hover:text-zinc-700 transition line-clamp-2">
                                        {{ $title }}
                                    </a>
                                    @if ($variantText)
                                        <p class="text-sm text-gray-500 mt-1">{{ $variantText }}</p>
                                    @endif
                                    @if ($qty > $stock && $stock >= 0)
                                        <p class="text-xs text-red-600 font-medium mt-1">Stok tidak mencukupi! Tersisa {{ $stock }}.</p>
                                    @endif
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $formatRupiah($rowTotal) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $formatRupiah($unitPrice) }} / item
                                    </p>
                                </div>
                            </div>

                            {{-- Kontrol Kuantitas & Hapus --}}
                            <div class="flex flex-wrap justify-between items-center mt-4 pt-3 border-t border-gray-200 sm:mt-6 sm:pt-0 sm:border-t-0 w-full">
                                {{-- Form Update Qty --}}
                                <form action="{{ route('cart.items.update', $item->id) }}" method="POST" class="flex items-center gap-1"
                                      onsubmit="return updateQtySubmit(this);">
                                    @csrf
                                    @method('PATCH')

                                    <label for="qty-{{ $item->id }}" class="sr-only">Kuantitas</label>

                                    <button type="button"
                                            class="size-9 leading-9 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-200 transition duration-150"
                                            onclick="stepQty('qty-{{ $item->id }}', -1)">
                                        &minus;
                                    </button>

                                    <input type="number" id="qty-{{ $item->id }}" name="qty" value="{{ $qty }}"
                                           min="1" max="{{ max(1, $stock) }}"
                                           class="h-9 w-16 text-center rounded-lg border border-gray-300 bg-white text-gray-900 text-sm focus:ring-zinc-900 focus:border-zinc-900 p-0 [-moz-appearance:_textfield] [&::-webkit-inner-spin-button]:m-0 [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:m-0 [&::-webkit-outer-spin-button]:appearance-none" />

                                    <button type="button"
                                            class="size-9 leading-9 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-200 transition duration-150"
                                            onclick="stepQty('qty-{{ $item->id }}', 1)">
                                        &plus;
                                    </button>

                                    <button type="submit"
                                            class="ml-2 inline-flex items-center px-3 py-2 text-sm font-semibold text-white bg-zinc-900 rounded-lg hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition">
                                        Perbarui
                                    </button>
                                </form>

                                {{-- Hapus --}}
                                <form action="{{ route('cart.items.destroy', $item->id) }}" method="POST"
                                      onsubmit="return confirm('Hapus item ini dari keranjang?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center text-sm font-medium text-red-600 hover:text-red-800 transition py-2 px-3 rounded-lg hover:bg-gray-200/50">
                                        <svg class="w-4 h-4 mr-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- KOLOM 3: Ringkasan --}}
            <div class="lg:col-span-1 mt-8 lg:mt-0">
                <div class="sticky top-8 bg-gray-100 p-6 rounded-xl border border-gray-200 shadow-md">
                    <h3 class="text-xl font-bold text-gray-900 mb-4 pb-3 border-b border-gray-200">Ringkasan Pesanan</h3>

                    <dl class="space-y-3 text-sm text-gray-700">
                        <div class="flex justify-between">
                            <dt>Subtotal ({{ $items->count() }} Item)</dt>
                            <dd class="font-medium text-gray-900">{{ $formatRupiah($subtotal) }}</dd>
                        </div>
                        @if($discount > 0)
                        <div class="flex justify-between">
                            <dt>Diskon</dt>
                            <dd class="font-medium text-gray-900">-{{ $formatRupiah($discount) }}</dd>
                        </div>
                        @endif
                        @if($tax > 0)
                        <div class="flex justify-between">
                            <dt>Pajak</dt>
                            <dd class="font-medium text-gray-900">{{ $formatRupiah($tax) }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <dt>Biaya Pengiriman</dt>
                            <dd class="font-medium text-gray-900">{{ $formatRupiah($shippingFee) }}</dd>
                        </div>
                        <div class="flex justify-between pt-4 border-t border-gray-300 mt-4">
                            <dt class="text-lg font-bold text-gray-900">Total Pembayaran</dt>
                            <dd class="text-xl font-extrabold text-zinc-900">{{ $formatRupiah($grand) }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('checkout.index') }}"
                       class="inline-flex items-center justify-center w-full py-3 text-base font-semibold text-center text-white bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300 mt-6">
                        <svg class="w-5 h-5 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 10h18M3 14h18M5 18h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zM8 18h8V14H8v4z"/>
                        </svg>
                        Lanjut ke Pembayaran
                    </a>

                    <p class="text-xs text-gray-500 mt-4 text-center">Biaya pengiriman dapat berubah saat checkout.</p>
                </div>
            </div>

        </div>
    @endif
</div>

{{-- JS ringan untuk stepper & submit aman --}}
<script>
function stepQty(id, delta) {
  const el = document.getElementById(id);
  if (!el) return;
  const min = parseInt(el.min || '1', 10);
  const max = parseInt(el.max || '9999', 10);
  const cur = parseInt(el.value || '1', 10);
  const next = Math.max(min, Math.min(max, cur + delta));
  el.value = next;
}

async function updateQtySubmit(form) {
  // Optional: submit via fetch agar tidak reload penuh
  try {
    const fd = new FormData(form);
    const res = await fetch(form.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: fd
    });

    const ct = (res.headers.get('content-type') || '').toLowerCase();
    let data = null;
    if (ct.includes('application/json')) data = await res.json();
    if (!res.ok) {
      alert((data && data.message) ? data.message : 'Gagal memperbarui kuantitas.');
      return false;
    }

    // sukses: refresh ringan
    if (data && data.totals) {
      // kalau controller mengembalikan totals, boleh update sebagian; untuk simpel reload:
      location.reload();
    } else {
      location.reload();
    }
  } catch (e) {
    console.error(e);
    alert('Kesalahan jaringan.');
  }
  return false; // cegah submit default
}
</script>
@endsection
