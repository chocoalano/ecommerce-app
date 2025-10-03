@extends('layouts.app')

@section('content')
@php
    $items = $cart->cartItems;
    $fmt = fn($a) => 'Rp' . number_format((float)$a, 0, ',', '.');

    $subtotal = $cart->subtotal_amount ?? $items->sum(fn($i) => (float)$i->row_total);
    $shipping = $cart->shipping_amount ?? 25000;
    $discount = $cart->discount_amount ?? 0;
    $tax      = $cart->tax_amount ?? round(max(0, $subtotal - $discount) * 0.10, 0);
    $total    = $cart->grand_total ?? max(0, ($subtotal - $discount) + $shipping + $tax);
@endphp

<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight mb-8">Selesaikan Pembelian Anda</h1>

    <div class="lg:grid lg:grid-cols-3 lg:gap-12">

        <div class="lg:col-span-2 space-y-8 lg:space-y-10 order-2 lg:order-1">
            <ol class="flex items-center w-full text-sm font-medium text-center text-gray-500 sm:text-base">
                <li class="flex md:w-full items-center text-zinc-900 sm:after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-4 after:inline-block">
                    <span class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-gray-200">1. Pengiriman</span>
                </li>
                <li class="flex md:w-full items-center after:content-[''] after:w-full after:h-1 after:border-b after:border-gray-200 after:border-4 after:inline-block">
                    <span class="flex items-center after:content-['/'] sm:after:hidden after:mx-2 after:text-gray-200">2. Pembayaran</span>
                </li>
                <li class="flex items-center"><span>3. Konfirmasi</span></li>
            </ol>

            <form id="checkout-form" action="{{ route('checkout.place') }}" method="POST" class="space-y-8">
                @csrf

                {{-- ALAMAT PENGIRIMAN --}}
                <section class="bg-white p-6 rounded-xl border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-zinc-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                        </svg>
                        Alamat Pengiriman
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Nama Depan <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" value="{{ old('first_name') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Nama Belakang <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" value="{{ old('last_name') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mt-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Nomor Telepon <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block mb-2 text-sm font-medium text-gray-900">Alamat Lengkap <span class="text-red-500">*</span></label>
                        <textarea name="address" rows="3"
                                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" required>{{ old('address') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Kota/Kabupaten <span class="text-red-500">*</span></label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Provinsi <span class="text-red-500">*</span></label>
                            <input type="text" name="province" value="{{ old('province') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" required>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">Kode Pos <span class="text-red-500">*</span></label>
                            <input type="text" name="zip" value="{{ old('zip') }}"
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5" required>
                        </div>
                    </div>
                </section>

                {{-- METODE PEMBAYARAN --}}
                <section class="bg-white p-6 rounded-xl border border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                        <svg class="w-6 h-6 mr-3 text-zinc-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M3 14h18M5 18h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2zM8 18h8V14H8v4z"/>
                        </svg>
                        Pilih Metode Pembayaran
                    </h2>
                    @php
                        $methods = [
                            'bank_transfer' => 'Bank Transfer (VA)',
                            'credit_card'   => 'Kartu Kredit / Debit',
                            'e_wallet'      => 'E-Wallet (GoPay/ShopeePay)',
                            'cod'           => 'COD (Bayar di Tempat)',
                        ];
                    @endphp
                    <div class="space-y-3">
                        @foreach ($methods as $key => $label)
                            <label class="flex items-center justify-between p-4 border border-gray-300 rounded-xl cursor-pointer hover:bg-gray-50 transition duration-150">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="payment_method" value="{{ $key }}" class="size-5 text-zinc-700 focus:ring-zinc-700 border-gray-300" {{ old('payment_method') === $key ? 'checked' : '' }} required>
                                    <span class="font-semibold text-gray-800">{{ $label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </section>

                <div class="flex justify-end">
                    <button type="submit" id="btn-pay"
                        class="inline-flex items-center justify-center px-6 py-3 text-base font-semibold text-center text-white bg-zinc-900 rounded-full hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300 transition duration-300">
                        Bayar Sekarang ({{ $fmt($total) }})
                    </button>
                </div>
            </form>
        </div>

        {{-- RINGKASAN --}}
        <div class="mt-8 lg:mt-0 lg:col-span-1 order-1 lg:order-2">
            <div class="sticky top-8 bg-gray-50 p-6 rounded-xl border border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-4">Ringkasan Pesanan</h2>

                <div class="space-y-4 mb-6">
                    @foreach ($items as $it)
                        @php
                            $title = $it->variant->product->name ?? ('Variant #'.$it->variant_id);
                        @endphp
                        <div class="flex justify-between items-start text-sm">
                            <span class="text-gray-700 pr-2 line-clamp-2">{{ $title }} (x{{ $it->qty }})</span>
                            <span class="font-semibold text-gray-900 flex-shrink-0">{{ $fmt($it->row_total) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-3 pt-4 border-t border-gray-300 text-sm">
                    <div class="flex justify-between text-gray-700">
                        <span>Subtotal Produk:</span>
                        <span class="font-medium text-gray-900">{{ $fmt($subtotal) }}</span>
                    </div>
                    @if($discount > 0)
                        <div class="flex justify-between text-gray-700">
                            <span>Diskon:</span>
                            <span class="font-medium text-gray-900">-{{ $fmt($discount) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-gray-700">
                        <span>Biaya Pengiriman:</span>
                        <span class="font-medium text-gray-900">{{ $fmt($shipping) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>Pajak (PPN 10%):</span>
                        <span class="font-medium text-gray-900">{{ $fmt($tax) }}</span>
                    </div>
                </div>

                <div class="flex justify-between font-bold text-lg mt-6 pt-4 border-t border-gray-300">
                    <span class="text-gray-900">Total Pembayaran:</span>
                    <span class="text-2xl font-extrabold text-zinc-900">{{ $fmt($total) }}</span>
                </div>

                <p class="text-xs text-gray-500 mt-4 text-center">Pembayaran diproses aman & terenkripsi.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('checkout-form')?.addEventListener('submit', function(){
  const btn = document.getElementById('btn-pay');
  if (btn) { btn.disabled = true; btn.textContent = 'Memproses...'; }
});
</script>
@endsection
