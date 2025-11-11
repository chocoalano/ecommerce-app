@extends('layouts.app')

@section('content')
    @php
        $customer = $customer ?? null;
        $breadcrumbs = $breadcrumbs ?? [];
    @endphp

    <section class="bg-gray-50 py-8 antialiased md:py-10">
        <div class="mx-auto max-w-7xl px-4 2xl:px-0">
            <div class="grid grid-cols-12 gap-6">
                {{-- Side Bar Profile --}}
                @include('pages.auth.profile.partial.sidebar')

                <main class="col-span-12 md:col-span-9">
                    @if(!empty($breadcrumbs))
                        <livewire:components.breadcrumb :breadcrumbs="$breadcrumbs" />
                    @endif

                    <div class="mb-6 flex items-center gap-3">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white shadow-sm">
                            <svg class="h-6 w-6 text-zinc-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Topup E-Wallet</h1>
                            <p class="text-sm text-gray-500">Isi saldo e-wallet Anda dengan mudah</p>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 rounded-lg bg-green-50 p-4 text-sm text-green-800">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 rounded-lg bg-red-50 p-4 text-sm text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <form action="{{ route('auth.ewallet.topup.submit') }}" method="POST" id="topupForm">
                            @csrf

                            <div class="space-y-6">
                                {{-- Jumlah Topup --}}
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Jumlah Topup <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                        <input type="number" name="amount" id="amount"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-zinc-700 focus:border-transparent @error('amount') @enderror"
                                            value="{{ old('amount') }}" min="50000" step="1000" required>
                                    </div>
                                    @error('amount')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Minimal topup Rp 50.000</p>
                                </div>

                                {{-- Metode Pembayaran Midtrans --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Pilih Metode Pembayaran <span class="text-red-500">*</span>
                                    </label>
                                    <div class="space-y-3">
                                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment_method" value="bank_transfer" class="w-4 h-4 text-zinc-700 focus:ring-zinc-700" required checked>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900">Transfer Bank</div>
                                                <div class="text-sm text-gray-500">BCA, BNI, BRI, Mandiri, Permata</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment_method" value="credit_card" class="w-4 h-4 text-zinc-700 focus:ring-zinc-700" required>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900">Kartu Kredit/Debit</div>
                                                <div class="text-sm text-gray-500">Visa, Mastercard, JCB</div>
                                            </div>
                                        </label>
                                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                                            <input type="radio" name="payment_method" value="e_wallet" class="w-4 h-4 text-zinc-700 focus:ring-zinc-700" required>
                                            <div class="ml-3">
                                                <div class="font-medium text-gray-900">E-Wallet</div>
                                                <div class="text-sm text-gray-500">GoPay, ShopeePay, QRIS</div>
                                            </div>
                                        </label>
                                    </div>
                                    @error('payment_method')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Informasi Pembayaran --}}
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h3 class="font-semibold text-blue-900 mb-2">Informasi Rekening Tujuan:</h3>
                                    <div class="space-y-1 text-sm text-blue-800">
                                        <p><strong>Bank:</strong> BCA</p>
                                        <p><strong>Nomor Rekening:</strong> 1234567890</p>
                                        <p class="text-sm text-blue-800">
                                            <svg class="inline w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                            Setelah submit, Anda akan diarahkan ke halaman pembayaran Midtrans untuk menyelesaikan transaksi.
                                        </p>
                                    </div>
                                </div>

                                {{-- Buttons --}}
                                <div class="flex items-center gap-4 pt-4">
                                    <button type="submit"
                                        class="px-6 py-2 bg-zinc-700 text-white rounded-lg hover:bg-zinc-800 transition">
                                        Lanjutkan ke Pembayaran
                                    </button>
                                    <a href="{{ route('auth.ewallet') }}"
                                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                                        Batal
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </section>
@endsection
