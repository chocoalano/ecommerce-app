@extends('layouts.app')

@section('content')
@php
    $breadcrumbs = [
        ['label' => 'Beranda', 'href' => route('home')],
        ['label' => 'Profil', 'href' => route('auth.profile')],
        ['label' => 'Riwayat Pesanan', 'href' => null], // current
    ];

    // Kita definisikan warna utama (primary color) sebagai zinc
    $zinc_color = 'zinc';
    $zinc_50 = 'bg-zinc-50';
    $zinc_100 = 'bg-zinc-100';
    $zinc_300 = 'border-zinc-300';
    $zinc_600 = 'text-zinc-600';
    $zinc_700 = 'bg-zinc-700';
    $zinc_800 = 'hover:bg-zinc-800';
    $zinc_900 = 'text-zinc-900';
    $zinc_focus = 'focus:ring-zinc-300 focus:border-zinc-500';
@endphp

<div class="mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12 pt-6 pb-12">
    <livewire:components.breadcrumb :breadcrumbs="$breadcrumbs" />

    <div>
        <h2 class="mb-6 text-2xl font-bold {{ $zinc_900 }} md:text-3xl">Riwayat Pesanan Anda 📦</h2>

        <div class="mb-8 p-4 bg-white border border-gray-200 rounded-lg">
            <form method="GET" action="{{ route('auth.orders') }}">
                <div class="flex flex-col md:flex-row md:items-end gap-4">

                    <div class="relative flex-grow">
                        <label for="search" class="sr-only">Cari Pesanan</label>
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari berdasarkan No. Pesanan..." class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full pl-10 p-2.5">
                    </div>

                    <div>
                        <label for="order-type-dropdown" class="block mb-1 text-sm font-medium {{ $zinc_900 }}">Status</label>
                        <select id="order-type-dropdown" name="status" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5 min-w-[150px]">
                            <option value="">Semua Status</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="duration-dropdown" class="block mb-1 text-sm font-medium {{ $zinc_900 }}">Periode</label>
                        <select id="duration-dropdown" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-500 focus:border-zinc-500 block w-full p-2.5 min-w-[150px]">
                            <option value="">Periode waktu</option>
                            <option value="7days">7 hari terakhir</option>
                            <option value="30days">30 hari terakhir</option>
                            <option value="90days">3 bulan terakhir</option>
                            <option value="1year">1 tahun terakhir</option>
                        </select>
                    </div>

                    <button type="submit" class="text-white bg-zinc-900 {{ $zinc_800 }} focus:ring-4 focus:outline-none focus:ring-zinc-300 font-medium rounded-lg text-sm px-5 py-2.5 h-full md:h-auto">
                        Cari
                    </button>

                    @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('auth.orders') }}" class="text-gray-900 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 h-full md:h-auto whitespace-nowrap">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if($orders->count() > 0)
            <div class="space-y-6">
                @foreach($orders as $order)
                    <div class="max-w-full bg-white border border-gray-200 rounded-lg hover:shadow-xl transition-shadow duration-300 ease-in-out">
                        <div class="p-5 md:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between border-b border-gray-100 pb-4 mb-4">
                                <div class="mb-4 sm:mb-0">
                                    <p class="text-xs font-semibold uppercase text-gray-500">Nomor Pesanan</p>
                                    <h5 class="text-lg font-bold tracking-tight {{ $zinc_900 }} hover:text-zinc-600 transition-colors">
                                        <a href="{{ route('auth.order.detail', $order) }}">{{ $order->order_no }}</a>
                                    </h5>
                                    <p class="text-sm text-gray-500 mt-1">Tanggal Pesan: {{ $order->created_at->format('d F Y, H:i') }}</p>
                                </div>

                                <div class="flex flex-col items-start sm:items-end space-y-2">
                                    @php
                                        // Status badge colors are kept distinct for easy visual identification,
                                        // but the primary action color (Detail button) is Zinc.
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                            'confirmed' => 'bg-zinc-100 text-zinc-800 border border-zinc-300', // Changed to Zinc
                                            'processing' => 'bg-indigo-100 text-indigo-800 border border-indigo-300',
                                            'shipped' => 'bg-purple-100 text-purple-800 border border-purple-300',
                                            'completed' => 'bg-green-100 text-green-800 border border-green-300',
                                            'cancelled' => 'bg-red-100 text-red-800 border border-red-300',
                                        ];
                                        $statusClass = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800 border border-gray-300';
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                                        {{ $statuses[$order->status] ?? ucfirst($order->status) }}
                                    </span>
                                    <p class="text-sm font-semibold text-gray-500">Total Pembayaran</p>
                                    <span class="text-2xl font-extrabold {{ $zinc_900 }}">{{ $order->currency }} {{ number_format($order->grand_total, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4 text-sm text-gray-600">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 {{ $zinc_600 }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    <span>{{ $order->items->count() }} Produk</span>
                                </div>

                                @if($order->payment_method)
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 {{ $zinc_600 }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        <span>
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
                                @endif

                                @if($order->tracking_number)
                                    <div class="flex items-center col-span-2">
                                        <svg class="w-4 h-4 {{ $zinc_600 }} mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3l-3-3"></path></svg>
                                        <span class="font-medium {{ $zinc_900 }}">No. Resi:</span> {{ $order->tracking_number }}
                                    </div>
                                @endif
                            </div>


                            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-100 mt-4">
                                <a href="{{ route('auth.order.detail', $order) }}" class="text-white bg-zinc-900 {{ $zinc_800 }} focus:ring-4 focus:outline-none focus:ring-zinc-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Lihat Detail
                                </a>

                                @if(in_array($order->status, ['pending', 'confirmed']))
                                    <button type="button" onclick="openCancelModal('{{ $order->id }}')" data-modal-target="cancel-order-modal" data-modal-toggle="cancel-order-modal" class="text-red-700 hover:text-white border border-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Batalkan Pesanan
                                    </button>
                                @endif

                                @if($order->status === 'completed')
                                    <a href="{{ route('auth.review.create', $order) }}" class="text-green-700 hover:text-white border border-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        Beri Ulasan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $orders->links() }}
            </div>
        @else
            <div class="mt-8 p-12 text-center bg-gray-50 border border-dashed border-gray-300 rounded-lg shadow-inner max-w-lg mx-auto">
                <svg class="mx-auto mb-4 text-gray-400 w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                <h5 class="mb-2 text-xl font-semibold {{ $zinc_900 }}">Belum ada pesanan ditemukan</h5>
                <p class="text-sm text-gray-500 mb-6">Mulai berbelanja sekarang untuk melacak pesanan Anda di sini!</p>
                <a href="{{ route('home') }}" class="inline-flex items-center px-5 py-2.5 text-sm font-medium text-center text-white bg-zinc-900 {{ $zinc_800 }} rounded-lg focus:ring-4 focus:outline-none focus:ring-zinc-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Mulai Berbelanja
                </a>
            </div>
        @endif
    </div>
</div>

<div id="cancel-order-modal" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow">
            <button type="button" data-modal-hide="cancel-order-modal" class="absolute top-3 right-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Tutup modal</span>
            </button>
            <div class="p-6 text-center">
                <svg class="mx-auto mb-4 text-gray-400 w-12 h-12" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500">Apakah Anda yakin ingin membatalkan pesanan ini? Aksi ini tidak dapat dibatalkan.</h3>
                <button id="cancel-order-confirm" type="button" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                    Ya, Batalkan
                </button>
                <button data-modal-hide="cancel-order-modal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Pastikan Anda telah menyertakan Flowbite JS, baik melalui CDN atau bundler.

    let currentOrderId = null;

    // Flowbite Modal initialization/handling
    const $modalElement = document.getElementById('cancel-order-modal');
    let cancelModal;

    if (typeof Modal !== 'undefined') {
        cancelModal = new Modal($modalElement);
    }

    function openCancelModal(orderId) {
        currentOrderId = orderId;
        if (cancelModal) {
            cancelModal.show();
        } else {
            $modalElement.classList.remove('hidden');
        }
    }

    function closeCancelModal() {
        if (cancelModal) {
            cancelModal.hide();
        } else {
            $modalElement.classList.add('hidden');
        }
        currentOrderId = null;
    }

    // --- LOGIC PEMBATALAN PESANAN ---
    document.getElementById('cancel-order-confirm').addEventListener('click', async function() {
        if (!currentOrderId) return;

        try {
            const response = await fetch(`/auth/orders/${currentOrderId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, // Pastikan meta tag CSRF ada
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                // Asumsi `showToast` adalah fungsi untuk menampilkan notifikasi
                showToast(result.message, 'success');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            showToast('Terjadi kesalahan saat membatalkan pesanan.', 'error');
        }

        closeCancelModal();
    });

    // --- LOGIC FILTERING ---

    // Filter Status (Langsung mengarahkan ke URL)
    document.getElementById('order-type-dropdown').addEventListener('change', function() {
        const url = new URL(window.location);
        if (this.value) {
            url.searchParams.set('status', this.value);
        } else {
            url.searchParams.delete('status');
        }
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        window.location.href = url.toString();
    });

    // Filter Periode (Date Range Logic)
    document.getElementById('duration-dropdown').addEventListener('change', function() {
        const url = new URL(window.location);
        const now = new Date();
        let dateFrom;
        const value = this.value;

        const statusValue = document.getElementById('order-type-dropdown').value;
        if (statusValue) {
             url.searchParams.set('status', statusValue);
        } else {
             url.searchParams.delete('status');
        }
        const searchValue = document.getElementById('search').value;
        if (searchValue) {
             url.searchParams.set('search', searchValue);
        } else {
             url.searchParams.delete('search');
        }

        if (!value) {
            url.searchParams.delete('date_from');
            url.searchParams.delete('date_to');
            window.location.href = url.toString();
            return;
        }

        switch (value) {
            case '7days':
                dateFrom = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                break;
            case '30days':
                dateFrom = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                break;
            case '90days':
                dateFrom = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000);
                break;
            case '1year':
                dateFrom = new Date(now.getTime() - 365 * 24 * 60 * 60 * 1000);
                break;
            default:
                return;
        }

        // Format tanggal ke YYYY-MM-DD
        const formatDate = (date) => date.toISOString().split('T')[0];

        url.searchParams.set('date_from', formatDate(dateFrom));
        url.searchParams.set('date_to', formatDate(now));
        window.location.href = url.toString();
    });
</script>
@endsection
