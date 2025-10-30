@extends('layouts.app')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
    @php
        // Default agar aman kalau variabel belum dikirim
        $customer = $customer ?? null;
        $title = $title ?? 'Daftar Order';
        $currentType = $currentType ?? 'All';
        $routeBase = 'auth.transaction-order';

        // Header default sesuai renderer JS (jangan ubah labelnya tanpa ubah JS juga)
        $header = $header ?? [];
        if (empty($header)) {
            $header = ['ID Order', 'Tanggal', 'Nama Produk', 'Kuantitas', 'Total (IDR)', 'Status'];
        }

        $breadcrumbs = $breadcrumbs ?? [
            ['label' => 'Beranda', 'href' => route('home')],
            ['label' => 'Profil', 'href' => route('auth.profile')],
            ['label' => 'Daftar Order', 'href' => null],
        ];

        $zinc_900 = 'text-zinc-900';
    @endphp

    <section class="bg-gray-50 py-8 antialiased md:py-10">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-12 gap-6">
                {{-- Sidebar profil (opsional) --}}
                @include('pages.auth.profile.partial.sidebar')

                <main class="col-span-12 md:col-span-9">
                    {{-- Breadcrumb (opsional) --}}
                    @if(!empty($breadcrumbs))
                        <livewire:components.breadcrumb :breadcrumbs="$breadcrumbs" />
                    @endif

                    <div class="mb-6 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm">
                            <svg class="h-6 w-6 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l2.293 2.293c.63.63 1.282.68 1.932.164l.85-1.127m0 0l-2.29-2.29M12 18h.01M21 21h.01M9 19a2 2 0 11-4 0 2 2 0 014 0zm14 0a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                            <p class="text-sm text-gray-500">Lihat riwayat order produk Anda.</p>
                        </div>
                    </div>

                    {{-- Alert global untuk feedback JS --}}
                    <div id="ordersFeedback" class="hidden mb-4 rounded border p-3 text-sm"></div>

                    <div class="space-y-6">
                        {{-- Tabs status (Pending / Berbayar / Selesai) — dikendalikan AJAX (pushState) --}}
                        <div class="flex flex-wrap gap-3 rounded-lg border border-gray-200 bg-white p-4">
                            <a href="{{ route($routeBase, ['type' => 'pending']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ strtolower($currentType) === 'pending' ? 'bg-zinc-700 text-white' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Pending Pembayaran
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'shipped']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ strtolower($currentType) === 'shipped' ? 'bg-zinc-700 text-white' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Diproses / Dikirim
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'completed']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ strtolower($currentType) === 'completed' ? 'bg-zinc-700 text-white' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Selesai
                            </a>
                        </div>

                        {{-- Tabel shell: diisi oleh JS lewat AJAX (tanpa reload) --}}
                        <div class="relative overflow-x-auto border border-gray-200 sm:rounded-lg">
                            <table id="ordersTable" class="w-full text-left text-sm text-gray-500">
                                <thead class="bg-gray-50 text-xs uppercase text-gray-700">
                                    <tr>
                                        @foreach ($header as $item)
                                            <th scope="col"
                                                class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500">
                                                {{ $item }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200">
                                    {{-- Placeholder awal (akan diganti JS) --}}
                                    <tr>
                                        <td colspan="{{ count($header) }}"
                                            class="px-4 py-6 text-center text-sm text-gray-500">
                                            Memuat data...
                                        </td>
                                    </tr>
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <td colspan="{{ count($header) }}" class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-4">
                                                {{-- Indikator halaman diisi JS --}}
                                                <div data-page-indicator class="text-sm text-gray-500">Halaman 1</div>
                                                {{-- Pager tombol Prev/Next diisi JS --}}
                                                <div id="ajaxPager"></div>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
@endsection
