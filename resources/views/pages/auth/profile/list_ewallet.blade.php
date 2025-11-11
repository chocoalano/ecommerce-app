@extends('layouts.app')

@section('content')
    @php
        $customer = $customer ?? null;
        $header = $header ?? [];
        $data = $data ?? collect();
        $title = $title ?? 'Daftar Data E-Wallet';
        $currentType = $currentType ?? 'transactions';
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

                    @php
                        // Ambil saldo terakhir dari transaksi terbaru
                        $latestTransaction = \App\Models\Mlm\TblEwalletTransaction::where('member_id', $customer->id)
                            ->orderBy('created_on', 'desc')
                            ->first();
                        $currentBalance = $latestTransaction ? $latestTransaction->balance : 0;
                    @endphp

                    {{-- Card Saldo E-Wallet --}}
                    <div class="mb-6 bg-gradient-to-r from-zinc-700 to-zinc-900 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90 mb-1">Saldo E-Wallet Anda</p>
                                <h2 class="text-3xl font-bold">Rp {{ number_format($currentBalance, 0, ',', '.') }}</h2>
                            </div>
                            <div class="bg-white/20 rounded-full p-4">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-3">
                            <a href="{{ route('auth.ewallet.topup') }}"
                                class="px-4 py-2 bg-white text-zinc-700 rounded-lg hover:bg-gray-100 transition font-medium text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Topup Saldo
                            </a>
                            <a href="{{ route('auth.ewallet.withdrawal') }}"
                                class="px-4 py-2 bg-white/20 text-white rounded-lg hover:bg-white/30 transition font-medium text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0l2.5-2.5m-2.5 2.5l-2.5-2.5M11 17h10m0 0l-2.5-2.5m2.5 2.5l-2.5-2.5M13 17a2 2 0 00-2-2H9m0 0v-2a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 002 2h2" />
                                </svg>
                                Tarik Dana
                            </a>
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
                                            {{-- Transactions --}}
                                            @if($currentType == 'transactions')
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['id'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['date'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                                                    <span class="inline-flex items-center rounded-full {{ strpos($row['type'], 'Kredit') !== false ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800' }} px-2 py-1 text-xs font-semibold">
                                                        {{ $row['type'] }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $row['note'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $row['amount'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $row['balance'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full {{ $row['status_class'] }} px-2 py-1 text-xs font-semibold">{{ $row['status'] }}</span>
                                                </td>
                                            {{-- Withdrawal --}}
                                            @elseif($currentType == 'withdrawal')
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['id'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['date'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $row['amount'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['method'] }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $row['note'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full {{ $row['status_class'] }} px-2 py-1 text-xs font-semibold">{{ $row['status'] }}</span>
                                                </td>
                                            @endif
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
