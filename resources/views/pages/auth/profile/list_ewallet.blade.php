@extends('layouts.app')

@section('content')
    @php
        $customer = $customer ?? null;
        $header = $header ?? [];
        $data = $data ?? collect(); // $data kini adalah Illuminate\Pagination\Paginator (Simple)
        $title = $title ?? 'Daftar Data';
        $currentType = $currentType ?? 'member_prospect';
    @endphp

    <section class="bg-gray-50 py-8 antialiased md:py-10">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-12 gap-6">
                {{-- Side Bar Profile --}}
                @include('pages.auth.profile.partial.sidebar')

                <main class="col-span-12 md:col-span-9">
                    <div class="mb-6 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm">
                            <svg class="h-6 w-6 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0l2.5-2.5m-2.5 2.5l-2.5-2.5M11 17h10m0 0l-2.5-2.5m2.5 2.5l-2.5-2.5M13 17a2 2 0 00-2-2H9m0 0v-2a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 002 2h2" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                            <p class="text-sm text-gray-500">Lihat riwayat data {{ strtolower($title) }} Anda.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- Navigasi Tipe Data --}}
                        <div class="flex flex-wrap gap-3 p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                            {{-- Catatan: Ganti 'auth.ewallet' jika route Anda berbeda. Saya asumsikan ini benar. --}}
                            <a href="{{ route('auth.ewallet', ['type' => 'transactions']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'transactions' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Daftar Transaksi
                            </a>
                            <a href="{{ route('auth.ewallet', ['type' => 'withdrawal']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'withdrawal' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Daftar Penarikan
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
                                            @foreach ($header as $kolom)
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                    @php
                                                        $value = $row[$kolom] ?? '-';

                                                        // Pemformatan mata uang dinamis
                                                        if (strpos($kolom, '(IDR)') !== false) {
                                                            echo 'Rp' . number_format($value, 0, ',', '.');
                                                        }
                                                        // Pemformatan Status
                                                        elseif ($kolom == 'Status Keanggotaan' || $kolom == 'Status') {
                                                            $color = match($value) {
                                                                'Aktif', 'Selesai' => 'bg-green-100 text-green-800',
                                                                'Pending', 'Diproses', 'Pending Verifikasi' => 'bg-yellow-100 text-yellow-800',
                                                                'Dibatalkan', 'Non-Aktif', 'Blokir', 'Gagal' => 'bg-red-100 text-red-800',
                                                                default => 'bg-gray-100 text-gray-800',
                                                            };
                                                            echo "<span class='inline-flex items-center rounded-full {$color} px-2 py-1 text-xs font-semibold'>{$value}</span>";
                                                        }
                                                        // Aksi
                                                        elseif ($kolom == 'Aksi') {
                                                            echo '<a href="#" class="text-zinc-600 hover:text-zinc-900">Detail</a>';
                                                        }
                                                        else {
                                                            echo $value;
                                                        }
                                                    @endphp
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-6 text-center text-sm text-gray-500" colspan="{{ count($header) }}">Tidak ada data yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="{{ count($header) }}" class="px-4 py-3 text-right">
                                            <div class="flex justify-end items-center gap-4">
                                                <div class="text-sm text-gray-500">
                                                    Menampilkan halaman {{ $data->currentPage() }}
                                                </div>
                                                <div>
                                                    {{-- PERBAIKAN: Gunakan withQueryString() untuk mempertahankan parameter 'type' --}}
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
