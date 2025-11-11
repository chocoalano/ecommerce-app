@extends('layouts.app')

@section('content')
    @php
        // Pastikan variabel ada
        $customer = $customer ?? null;
        $header = $header ?? [];
        // $data adalah Paginator (Simple Pagination)
        $data = $data ?? collect();
        $title = $title ?? 'Daftar Komisi';
        $currentType = $currentType ?? 'sponsors';
        $routeBase = 'auth.komisi-list'; // Asumsikan nama route adalah ini
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
                                {{-- Ikon untuk Komisi / Reward / Uang --}}
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 4v4m0 4v2m-6-6h2m-2 0h-2M6 12h2m-2 0H4m12 0h2m-2 0h-2m4-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">{{ $title }}</h1>
                            <p class="text-sm text-gray-500">Lihat riwayat perhitungan {{ strtolower($title) }} Anda.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        {{-- Navigasi Tipe Komisi --}}
                        <div class="flex flex-wrap gap-3 p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                            <a href="{{ route($routeBase, ['type' => 'sponsors']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'sponsors' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Komisi Sponsor
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'pairings']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'pairings' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Komisi Pairing
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'matchings']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'matchings' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Komisi Matching
                            </a>
                            <a href="{{ route($routeBase, ['type' => 'rewards']) }}"
                                class="px-3 py-2 text-sm font-medium rounded-lg transition {{ $currentType == 'rewards' ? 'bg-zinc-700 text-white shadow-md' : 'text-zinc-700 hover:bg-gray-100' }}">
                                Komisi Reward
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
                                            {{-- Sponsors --}}
                                            @if($currentType == 'sponsors')
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['id'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['date'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['member'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $row['amount'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full {{ $row['status_class'] }} px-2 py-1 text-xs font-semibold">{{ $row['status'] }}</span>
                                                </td>
                                            {{-- Pairings --}}
                                            @elseif($currentType == 'pairings')
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['id'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['date'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['pair_left'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['pair_right'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $row['amount'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full {{ $row['status_class'] }} px-2 py-1 text-xs font-semibold">{{ $row['status'] }}</span>
                                                </td>
                                            {{-- Matchings --}}
                                            @elseif($currentType == 'matchings')
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['id'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['date'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['generation'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['source'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $row['amount'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full {{ $row['status_class'] }} px-2 py-1 text-xs font-semibold">{{ $row['status'] }}</span>
                                                </td>
                                            {{-- Rewards --}}
                                            @elseif($currentType == 'rewards')
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['id'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['date'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $row['reward_name'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $row['requirement'] }}</td>
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <span class="inline-flex items-center rounded-full {{ $row['status_class'] }} px-2 py-1 text-xs font-semibold">{{ $row['status'] }}</span>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-6 text-center text-sm text-gray-500" colspan="{{ count($header) }}">Tidak ada data komisi yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="{{ count($header) }}" class="px-4 py-3 text-right">
                                            <div class="flex justify-end items-center gap-4">
                                                <div class="text-sm text-gray-500">
                                                    Halaman {{ $data->currentPage() }}
                                                </div>
                                                <div>
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
