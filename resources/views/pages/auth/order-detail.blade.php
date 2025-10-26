@extends('layouts.app')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Beranda', 'href' => route('home')],
        ['label' => 'Profil', 'href' => route('auth.profile')],
        ['label' => 'Riwayat Pesanan', 'href' => route('auth.orders')],
        ['label' => $order->order_no, 'href' => null], // current
    ];

    // Menggunakan warna umum untuk status, tidak ada dark:
    $statusClasses = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'processing' => 'bg-indigo-100 text-indigo-800',
        'shipped' => 'bg-purple-100 text-purple-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    $statusClass = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800';

    $statusLabels = [
        'pending' => 'Menunggu Pembayaran',
        'confirmed' => 'Dikonfirmasi',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    // Warna primary untuk Flowbite standar (biru)
    $primary_600 = 'bg-blue-600';
    $primary_700 = 'hover:bg-blue-700';
    $primary_300_focus = 'focus:ring-blue-300';
@endphp

<div class="mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12 pt-6 pb-12">
    <livewire:components.breadcrumb :breadcrumbs="$breadcrumbs" />

    <div>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Detail Pesanan <span class="text-gray-500 text-base">#{{ $order->order_no }}</span></h1>
                <p class="mt-1 text-sm text-gray-500">Tanggal Pesan: {{ $order->created_at->format('d F Y, H:i') }}</p>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center rounded-lg px-3 py-1 text-sm font-semibold {{ $statusClass }}">
                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                </span>

                @if(in_array($order->status, ['pending', 'confirmed']))
                    <button type="button" onclick="cancelOrder('{{ $order->id }}')"
                            class="inline-flex items-center rounded-lg border border-red-700 bg-gray-50 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50 focus:outline-none focus:ring-4 focus:ring-red-300 transition-colors duration-150">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batalkan Pesanan
                    </button>
                @endif
            </div>
        </div>

        <div class="mb-6 rounded-lg bg-gray-50 p-5 border border-gray-200">
            <div class="flex items-center justify-between">
                <span class="text-xl font-semibold text-gray-500">Total Pembayaran</span>
                <span class="text-3xl font-extrabold text-gray-900">{{ $order->currency }} {{ number_format($order->grand_total, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">

                <div class="rounded-lg bg-gray-50 p-6 border border-gray-200">
                    <h3 class="mb-4 text-xl font-semibold text-gray-900">Produk yang Dibeli ({{ $order->items->count() }})</h3>

                    <div class="space-y-5">
                        @foreach($order->items as $item)
                            <div class="flex items-start gap-4 border-b border-gray-100 pb-5 last:border-b-0 last:pb-0">
                                <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-gray-100 border border-gray-200">
                                    @if($item->product && $item->product->image)
                                        <img src="{{ asset('storage/' . $item->product->image->url) }}" alt="{{ $item->name }}" class="h-full w-full object-cover object-center">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gray-200">
                                            <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-semibold text-gray-900">{{ $item->name }}</h4>
                                    @if($item->sku)
                                        <p class="mt-0.5 text-xs text-gray-500">SKU: {{ $item->sku }}</p>
                                    @endif

                                    <div class="mt-2 flex items-center gap-4 text-sm">
                                        <span class="text-gray-500">Qty: **{{ $item->qty }}**</span>
                                        <span class="text-gray-500">@ {{ $order->currency }} {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-base font-bold text-gray-900">
                                        {{ $order->currency }} {{ number_format($item->row_total, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="rounded-lg bg-gray-50 p-6 border border-gray-200">
                        <h3 class="mb-4 text-lg font-medium text-gray-900 flex items-center">
                             <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.727A8 8 0 016.343 5.273M17.657 16.727A8 8 0 006.343 5.273m11.314 11.454l2.828 2.828m-14.142 0l-2.828 2.828M17.657 16.727L12 12m5.657 4.727L12 12m0 0L6.343 5.273M12 12v6"></path></svg>
                             Alamat Pengiriman
                        </h3>

                        @if($order->shippingAddress)
                            <div class="text-sm text-gray-900 space-y-1">
                                <p class="font-bold">{{ $order->shippingAddress->first_name }} {{ $order->shippingAddress->last_name }}</p>
                                <p class="text-gray-600">{{ $order->shippingAddress->phone }}</p>
                                <p>{{ $order->shippingAddress->address }}</p>
                                <p>{{ $order->shippingAddress->city }}, {{ $order->shippingAddress->province }} {{ $order->shippingAddress->postal_code }}</p>
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Alamat pengiriman tidak tersedia</p>
                        @endif
                    </div>

                    <div class="rounded-lg bg-gray-50 p-6 border border-gray-200">
                        <h3 class="mb-4 text-lg font-medium text-gray-900 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            Informasi Pembayaran
                        </h3>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Metode:</span>
                                <span class="text-gray-900 font-semibold">
                                    @switch($order->payment_method)
                                        @case('cod') Cash on Delivery @break
                                        @case('credit_card') Kartu Kredit @break
                                        @case('bank_transfer') Transfer Bank @break
                                        @case('e_wallet') E-Wallet @break
                                        @case('midtrans') Midtrans @break
                                        @default {{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}
                                    @endswitch
                                </span>
                            </div>

                            @if(isset($orderNotes['gateway']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Gateway:</span>
                                    <span class="text-gray-900">{{ ucfirst($orderNotes['gateway']) }}</span>
                                </div>
                            @endif

                            @if(isset($orderNotes['snap_token']))
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Token Pembayaran:</span>
                                    <span class="text-gray-900 font-mono text-xs">{{ substr($orderNotes['snap_token'], 0, 20) }}...</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1 space-y-6">

                <div class="rounded-lg bg-gray-50 p-6 border border-gray-200">
                    <h3 class="mb-4 text-lg font-medium text-gray-900">Ringkasan Biaya</h3>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal Produk:</span>
                            <span class="text-gray-900">{{ $order->currency }} {{ number_format($order->subtotal_amount, 0, ',', '.') }}</span>
                        </div>

                        @if($order->discount_amount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Diskon:</span>
                                <span class="text-red-600 font-medium">-{{ $order->currency }} {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($order->shipping_amount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Ongkos Kirim:</span>
                                <span class="text-gray-900">{{ $order->currency }} {{ number_format($order->shipping_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        @if($order->tax_amount > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-600">Pajak:</span>
                                <span class="text-gray-900">{{ $order->currency }} {{ number_format($order->tax_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-4 mt-4">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-bold text-gray-900">Total Akhir:</span>
                                <span class="text-xl font-extrabold text-gray-900">{{ $order->currency }} {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    @if($order->status === 'completed')
                        <button class="w-full rounded-lg {{ $primary_600 }} px-4 py-2.5 text-sm font-medium text-white {{ $primary_700 }} focus:outline-none focus:ring-4 {{ $primary_300_focus }}">
                            Beli Lagi
                        </button>
                        <button class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Tulis Review
                        </button>
                    @endif

                    @if(in_array($order->status, ['shipped', 'completed']))
                        <button class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200">
                            Lacak Pengiriman
                        </button>
                    @endif

                    <a href="{{ route('auth.orders') }}" class="block w-full rounded-lg px-4 py-2.5 text-center text-sm font-medium text-gray-100 bg-zinc-900 hover:bg-zinc-80 transition-colors">
                        Kembali ke Riwayat Pesanan
                    </a>
                </div>

                <div class="rounded-lg bg-gray-50 p-6 border border-gray-200">
                    <h3 class="mb-5 text-lg font-medium text-gray-900">Timeline Pesanan</h3>

                    <ol class="relative border-s border-gray-200 ms-3">
                        <li class="mb-10 ms-6">
                            <span class="absolute flex items-center justify-center w-6 h-6 bg-green-100 rounded-full -start-3 ring-8 ring-white">
                                <svg class="w-3 h-3 text-green-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 16a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-8a1 1 0 0 1-2 0V6a1 1 0 1 1 2 0v2Z"/>
                                </svg>
                            </span>
                            <h4 class="flex items-center mb-1 text-sm font-semibold text-gray-900">Pesanan Dibuat</h4>
                            <time class="block mb-2 text-xs font-normal leading-none text-gray-400">{{ $order->created_at->format('d M Y') }}</time>
                            <p class="text-sm font-normal text-gray-500">Menunggu konfirmasi dan pembayaran.</p>
                        </li>

                        @if($order->status !== 'pending')
                            <li class="ms-6">
                                <span class="absolute flex items-center justify-center w-6 h-6 {{ $order->status === 'cancelled' ? 'bg-red-100' : 'bg-blue-100' }} rounded-full -start-3 ring-8 ring-white">
                                    <svg class="w-3 h-3 {{ $order->status === 'cancelled' ? 'text-red-500' : 'text-blue-500' }}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        @if($order->status === 'cancelled')
                                             <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        @else
                                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM10 16a1 1 0 1 1 0-2 1 1 0 0 1 0 2Zm1-8a1 1 0 0 1-2 0V6a1 1 0 1 1 2 0v2Z"/>
                                        @endif
                                    </svg>
                                </span>
                                <h4 class="mb-1 text-sm font-semibold text-gray-900">Status Terbaru: {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</h4>
                                <time class="block mb-2 text-xs font-normal leading-none text-gray-400">{{ $order->updated_at->format('d M Y, H:i') }}</time>
                                <p class="text-sm font-normal text-gray-500">
                                    @if($order->status === 'confirmed') Pembayaran diterima. Pesanan siap diproses.
                                    @elseif($order->status === 'processing') Pesanan sedang disiapkan.
                                    @elseif($order->status === 'shipped') Pesanan telah diserahkan ke kurir. {{ $order->tracking_number ? 'No. Resi: ' . $order->tracking_number : '' }}
                                    @elseif($order->status === 'completed') Pesanan telah diterima dan selesai.
                                    @elseif($order->status === 'cancelled') Pesanan dibatalkan.
                                    @endif
                                </p>
                            </li>
                        @endif
                    </ol>
                </div>

            </div>
        </div>
    </div>
</div>

<div id="cancel-order-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-gray-50 rounded-lg shadow">
            <button type="button" onclick="closeCancelModal()" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Tutup modal</span>
            </button>
            <div class="p-6 text-center">
                <svg class="mx-auto mb-4 text-red-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500">Apakah Anda yakin ingin membatalkan pesanan ini? Aksi ini tidak dapat dibatalkan.</h3>
                <button id="cancel-order-confirm" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                    Ya, Batalkan
                </button>
                <button onclick="closeCancelModal()" type="button" class="text-gray-500 bg-gray-50 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

@include('components.toast-notification')

<script>
// Pastikan Flowbite JS sudah diinisialisasi jika menggunakan data-modal-toggle/data-modal-hide

let currentOrderId = null;

function cancelOrder(orderId) {
    currentOrderId = orderId;
    document.getElementById('cancel-order-modal').classList.remove('hidden');

    // Tambahkan event listener di sini (atau pastikan sudah ada)
    document.getElementById('cancel-order-confirm').onclick = async function() {
        if (!currentOrderId) return;

        try {
            // Asumsi CSRF token ada di meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            const response = await fetch(`/auth/orders/${currentOrderId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Asumsi `showToast` adalah fungsi untuk menampilkan notifikasi
                if (typeof showToast === 'function') {
                    showToast(result.message, 'success');
                } else {
                    alert(result.message);
                }

                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                 if (typeof showToast === 'function') {
                    showToast(result.message, 'error');
                } else {
                    alert(result.message);
                }
            }
        } catch (error) {
             if (typeof showToast === 'function') {
                showToast('Terjadi kesalahan saat membatalkan pesanan.', 'error');
            } else {
                alert('Terjadi kesalahan saat membatalkan pesanan.');
            }
        }

        closeCancelModal();
    };
}

function closeCancelModal() {
    document.getElementById('cancel-order-modal').classList.add('hidden');
    currentOrderId = null;
}
</script>
@endsection
