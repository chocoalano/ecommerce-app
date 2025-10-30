@extends('layouts.app')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    @php
        $breadcrumbs = [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'Riwayat Pesanan', 'href' => null],
        ];
        $zinc_800 = 'hover:bg-zinc-800';
        $zinc_900 = 'text-zinc-900';
    @endphp

    <div class="mx-auto max-w-5/6 2xl:px-0 px-4 sm:px-6 lg:px-8 py-8 lg:py-12 pt-6 pb-12">
        <livewire:components.breadcrumb :breadcrumbs="$breadcrumbs" />

        <h2 class="mb-6 text-2xl font-bold {{ $zinc_900 }} md:text-3xl">Riwayat Pesanan Anda</h2>

        {{-- Alert global untuk feedback (dipakai orders-adapted.js) --}}
        <div id="ordersFeedback" class="hidden mb-4 rounded border p-3 text-sm"></div>

        {{-- Filter (tidak reload; di-wire di script bawah) --}}
        <div class="mb-8 rounded-lg border border-gray-200 bg-white p-4">
            <form id="ordersFilters" class="flex flex-col gap-4 md:flex-row md:items-end" autocomplete="off">
                <div class="relative flex-grow">
                    <label for="search" class="sr-only">Cari Pesanan</label>
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 20 20">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <input id="search" type="text" placeholder="Cari berdasarkan No. Pesanan..."
                        class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 pl-10 text-sm focus:border-zinc-500 focus:ring-zinc-500">
                </div>

                <div>
                    <label for="order-type-dropdown" class="mb-1 block text-sm font-medium {{ $zinc_900 }}">Status</label>
                    <select id="order-type-dropdown"
                        class="min-w-[150px] block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-zinc-500 focus:ring-zinc-500">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu Pembayaran</option>
                        <option value="confirmed">Dikonfirmasi</option>
                        <option value="processing">Diproses</option>
                        <option value="shipped">Dikirim</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <div>
                    <label for="duration-dropdown" class="mb-1 block text-sm font-medium {{ $zinc_900 }}">Periode</label>
                    <select id="duration-dropdown"
                        class="min-w-[150px] block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-zinc-500 focus:ring-zinc-500">
                        <option value="">Periode waktu</option>
                        <option value="7days">7 hari terakhir</option>
                        <option value="30days">30 hari terakhir</option>
                        <option value="90days">3 bulan terakhir</option>
                        <option value="1year">1 tahun terakhir</option>
                    </select>
                </div>

                <button id="btnFilterApply" type="submit"
                    class="h-full whitespace-nowrap rounded-lg bg-zinc-900 px-5 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-4 focus:ring-zinc-300 {{ $zinc_800 }}">
                    Cari
                </button>
                <button id="btnFilterReset" type="button"
                    class="h-full whitespace-nowrap rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200">
                    Reset
                </button>
                <div>
                    <label for="per-page-dropdown" class="mb-1 block text-sm font-medium {{ $zinc_900 }}">Tampil</label>
                    <select id="per-page-dropdown"
                        class="min-w-[120px] block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm focus:border-zinc-500 focus:ring-zinc-500">
                        <option value="1">5 / halaman</option>
                        <option value="10" selected>10 / halaman</option>
                        <option value="20">20 / halaman</option>
                        <option value="50">50 / halaman</option>
                    </select>
                </div>
            </form>
        </div>

        {{-- Kontainer LIST + PAGINATION untuk AJAX (atribut data dibaca oleh orders-adapted.js) --}}
        <div id="ordersContainer" data-source-url="{{ route('auth.orders') }}" data-detail-url-template="/auth/orders/:id"
            data-cancel-url-template="/auth/orders/:id/cancel">
            <div id="ordersList" class="divide-y divide-gray-200"></div>
            <nav id="ordersPagination" class="mt-4"></nav>
        </div>
    </div>

    {{-- DETAIL MODAL (ID wajib sesuai JS) --}}
    <div id="orderDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="w-full max-w-2xl rounded-lg bg-white p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 id="orderDetailTitle" class="text-lg font-semibold">Detail Pesanan</h3>
                <button id="btnCloseDetailModal" class="rounded p-2 hover:bg-gray-100">✕</button>
            </div>
            <div id="orderDetailBody" class="max-h-[70vh] overflow-y-auto"></div>
        </div>
    </div>

    {{-- CANCEL MODAL (ID wajib sesuai JS) --}}
    <div id="deleteOrderModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
        <div class="w-full max-w-md rounded-lg bg-white p-4">
            <h3 class="mb-2 text-lg font-semibold">Batalkan Pesanan</h3>
            <p class="text-sm text-gray-600">
                Yakin ingin membatalkan <span id="cancelOrderLabel" class="font-semibold text-gray-900">#—</span>?
            </p>
            <div id="cancelOrderAlert" class="hidden mt-3"></div>
            <div class="mt-4 flex justify-end gap-2">
                <button id="btnCloseCancelModal" class="rounded border px-3 py-2 hover:bg-gray-50">Tutup</button>
                <button id="btnConfirmCancel"
                    class="inline-flex items-center rounded bg-red-600 px-3 py-2 text-white hover:bg-red-700">
                    <svg id="cancelSpinner" class="mr-2 hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                    </svg>
                    Ya, Batalkan
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const $form = $('#ordersFilters');
            const $search = $('#search');
            const $status = $('#order-type-dropdown');
            const $period = $('#duration-dropdown');
            const $perPage = $('#per-page-dropdown');
            const $reset = $('#btnFilterReset');

            function rangeFromPeriod(val) {
                if (!val) return {};
                const now = new Date();
                let from = new Date();
                switch (val) {
                    case '7days': from = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000); break;
                    case '30days': from = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000); break;
                    case '90days': from = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000); break;
                    case '1year': from = new Date(now.getTime() - 365 * 24 * 60 * 60 * 1000); break;
                }
                const fmt = d => d.toISOString().split('T')[0];
                return { date_from: fmt(from), date_to: fmt(now) };
            }

            function pushFiltersToUrl({ search, status, period, per_page }) {
                const url = new URL(window.location.href);

                // clear agar page kembali ke 1 saat filter/per_page berubah
                ['search', 'status', 'date_from', 'date_to', 'page', 'per_page'].forEach(k => url.searchParams.delete(k));

                if (search) url.searchParams.set('search', search);
                if (status) url.searchParams.set('status', status);
                if (per_page) url.searchParams.set('per_page', per_page);

                const r = rangeFromPeriod(period);
                if (r.date_from) url.searchParams.set('date_from', r.date_from);
                if (r.date_to) url.searchParams.set('date_to', r.date_to);

                window.history.pushState({}, '', url.toString());
                // orders-adapted.js listen popstate → fetchOrders()
                window.dispatchEvent(new PopStateEvent('popstate'));
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function hydrateFromUrl() {
                const sp = new URLSearchParams(window.location.search);
                $search.val(sp.get('search') || '');
                $status.val(sp.get('status') || '');
                $perPage.val(sp.get('per_page') || $perPage.val()); // pakai dari URL jika ada
                // periode tidak di-derive otomatis dari date_from/date_to; dibiarkan apa adanya
            }

            document.addEventListener('DOMContentLoaded', hydrateFromUrl);

            $form.on('submit', function (e) {
                e.preventDefault();
                pushFiltersToUrl({
                    search: $search.val().trim(),
                    status: $status.val(),
                    period: $period.val(),
                    per_page: $perPage.val()
                });
            });

            // Auto apply on change
            $status.on('change', function () { $form.trigger('submit'); });
            $period.on('change', function () { $form.trigger('submit'); });
            $perPage.on('change', function () { $form.trigger('submit'); });

            // Reset semua termasuk per_page
            $reset.on('click', function () {
                const url = new URL(window.location.href);
                ['search', 'status', 'date_from', 'date_to', 'page', 'per_page'].forEach(k => url.searchParams.delete(k));
                window.history.pushState({}, '', url.toString());
                window.dispatchEvent(new PopStateEvent('popstate'));
                $form[0].reset();
                // pastikan per_page kembali ke default yang kamu tentukan (misal 10)
                $perPage.val('10');
            });
        })();
    </script>
@endpush
