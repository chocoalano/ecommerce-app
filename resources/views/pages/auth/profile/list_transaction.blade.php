@extends('layouts.app')

@section('content')
    @php
        // Pastikan variabel ada
        $customer = $customer ?? null;
        $header = $header ?? [];
        // $data adalah Paginator (Simple Pagination)
        $data = $data ?? collect();
        $title = $title ?? 'Daftar Order';
        $currentType = $currentType ?? 'Pending';
        // Asumsi route untuk controller ini. Pastikan Anda mendaftarkannya di web.php
        $routeBase = 'auth.transaction-order';
    @endphp

    <section class="bg-gray-50 py-8 antialiased md:py-10">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-12 gap-6">
                {{-- Side Bar Profile --}}
                {{-- Asumsi partial sidebar ada di lokasi ini --}}
                @include('pages.auth.profile.partial.sidebar')

                <main class="col-span-12 md:col-span-9">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm">
                            <svg class="h-6 w-6 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {{-- Ikon untuk Order/Belanja --}}
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l2.293 2.293c.63.63 1.282.68 1.932.164l.85-1.127m0 0l-2.29-2.29M12 18h.01M21 21h.01M9 19a2 2 0 11-4 0 2 2 0 014 0zm14 0a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                            <p class="text-sm text-gray-500">Lihat riwayat order produk Anda.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- Navigasi Tipe Order berdasarkan Status --}}
                        <div class="flex flex-wrap gap-3 p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <a href="{{ route($routeBase, ['type' => 'Pending']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'Pending' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Pending Pembayaran
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'Berbayar']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'Berbayar' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Diproses / Dikirim
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'Selesai']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'Selesai' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Selesai
                            </a>
                        </div>

                        {{-- Tabel Data --}}
                        <div class="relative overflow-x-auto border border-gray-200 sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead
                                    class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        @foreach ($header as $item)
                                            <th scope="col"
                                            class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $item }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse ($data as $row)
                                        <tr class="hover:bg-gray-50">
                                            {{-- Loop untuk menampilkan semua kolom sesuai header --}}
                                            @foreach ($header as $kolom)
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                    @php
                                                        $value = $row[$kolom] ?? '-';

                                                        // Pemformatan mata uang dinamis (Total (IDR))
                                                        if (strpos($kolom, '(IDR)') !== false) {
                                                            // Cast ke float untuk menghindari error number_format()
                                                            echo 'Rp' . number_format((float) $value, 0, ',', '.');
                                                        }
                                                        // Pemformatan Status
                                                        elseif ($kolom == 'Status') {
                                                            $color = match($value) {
                                                                'Selesai', 'Dikirim' => 'bg-green-100 text-green-800',
                                                                'Pending Pembayaran', 'Diproses' => 'bg-yellow-100 text-yellow-800',
                                                                'Dibatalkan' => 'bg-red-100 text-red-800',
                                                                default => 'bg-gray-100 text-gray-800',
                                                            };
                                                            echo "<span class='inline-flex items-center rounded-full {$color} px-2 py-1 text-xs font-semibold'>{$value}</span>";
                                                        }
                                                        // Teks biasa
                                                        else {
                                                            echo $value;
                                                        }
                                                    @endphp
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-6 text-center text-sm text-gray-500" colspan="{{ count($header) }}">Tidak ada order yang ditemukan dengan status ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="{{ count($header) }}" class="px-4 py-3 text-right">
                                            <div class="flex justify-end items-center gap-4">
                                                <div class="text-sm text-gray-500">
                                                    {{-- Menampilkan halaman saat ini (Simple Paginator) --}}
                                                    Halaman {{ $data->currentPage() }}
                                                </div>
                                                <div>
                                                    {{-- Memanggil links() dengan withQueryString() agar tombol Next/Previous muncul dan filter dipertahankan --}}
                                                    {{ $data->withQueryString()->links('pagination::tailwind') }}
                                                </div>
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
