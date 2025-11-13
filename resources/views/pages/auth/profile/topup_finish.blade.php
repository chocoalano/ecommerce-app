@extends('layouts.app')

@section('title', 'Status Pembayaran Topup')

@section('content')
    <section class="py-12">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                @include('pages.auth.profile.partial.sidebar')

                <main class="flex-1">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="text-center py-8">
                            @if($topup->status == 'approved')
                                {{-- Success --}}
                                <div class="mb-6">
                                    <svg class="w-20 h-20 mx-auto text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Berhasil!</h2>
                                <p class="text-gray-600 mb-6">Saldo e-wallet Anda telah berhasil ditambahkan</p>
                            @elseif($topup->status == 'pending')
                                {{-- Pending --}}
                                <div class="mb-6">
                                    <svg class="w-20 h-20 mx-auto text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Menunggu Pembayaran</h2>
                                <p class="text-gray-600 mb-6">Pembayaran Anda sedang diproses. Saldo akan ditambahkan setelah pembayaran dikonfirmasi.</p>
                            @else
                                {{-- Failed --}}
                                <div class="mb-6">
                                    <svg class="w-20 h-20 mx-auto text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-800 mb-2">Pembayaran Gagal</h2>
                                <p class="text-gray-600 mb-6">Terjadi kesalahan saat memproses pembayaran. Silakan coba lagi.</p>
                            @endif

                            {{-- Transaction Details --}}
                            <div class="bg-gray-50 rounded-lg p-6 mb-6 max-w-md mx-auto">
                                <h3 class="font-semibold text-gray-800 mb-4">Detail Transaksi</h3>
                                <div class="space-y-2 text-left">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Order ID:</span>
                                        <span class="font-medium">{{ $topup->order_no }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Jumlah:</span>
                                        <span class="font-medium">Rp {{ number_format($topup->amount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Tanggal:</span>
                                        <span class="font-medium">{{ $topup->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="px-3 py-1 rounded-full text-sm
                                            @if($topup->status == 'approved') bg-green-100 text-green-800
                                            @elseif($topup->status == 'pending')
                                            @else
                                            @endif">
                                            @if($topup->status == 'approved') Berhasil
                                            @elseif($topup->status == 'pending') Menunggu
                                            @else Gagal
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex justify-center gap-4">
                                <a href="{{ route('auth.ewallet') }}"
                                    class="px-6 py-2 bg-zinc-700 text-white rounded-lg hover:bg-zinc-800 transition">
                                    Lihat E-Wallet
                                </a>
                                @if($topup->status != 'approved')
                                    <a href="{{ route('auth.ewallet.topup') }}"
                                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                        Coba Lagi
                                    </a>

                                    {{-- Tombol Manual Approve untuk Testing (hapus di production) --}}
                                    @if($topup->status == 'pending')
                                        <form action="{{ route('auth.ewallet.topup.manual-approve', $topup->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                                                onclick="return confirm('Simulasi pembayaran berhasil? (Hanya untuk testing)')">
                                                ✓ Simulasi Bayar (Testing)
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
@endsection
