@extends('layouts.app')

@section('content')
    @php
        $customer = $customer ?? null;
        $breadcrumbs = $breadcrumbs ?? [];
        $currentBalance = $currentBalance ?? 0;
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m0 0l2.5-2.5m-2.5 2.5l-2.5-2.5M11 17h10m0 0l-2.5-2.5m2.5 2.5l-2.5-2.5M13 17a2 2 0 00-2-2H9m0 0v-2a2 2 0 012-2h4a2 2 0 012 2v2a2 2 0 002 2h2" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-2xl font-semibold text-gray-900">Penarikan Dana E-Wallet</h1>
                            <p class="text-sm text-gray-500">Tarik saldo e-wallet Anda ke rekening bank</p>
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

                    {{-- Card Saldo --}}
                    <div class="mb-6 bg-gradient-to-r from-zinc-700 to-zinc-900 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm opacity-90 mb-1">Saldo Tersedia</p>
                                <h2 class="text-3xl font-bold">Rp {{ number_format($currentBalance, 0, ',', '.') }}</h2>
                            </div>
                            <div class="bg-white/20 rounded-full p-4">
                                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path>
                                    <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <form action="{{ route('auth.ewallet.withdrawal.submit') }}" method="POST">
                            @csrf

                            <div class="space-y-6">
                                {{-- Jumlah Penarikan --}}
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Jumlah Penarikan <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">Rp</span>
                                        <input type="number" name="amount" id="amount"
                                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-zinc-700 focus:border-transparent"
                                            value="{{ old('amount') }}" min="100000" step="1000" max="{{ $currentBalance }}" required>
                                    </div>
                                    @error('amount')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-sm text-gray-500">Minimal penarikan Rp 100.000</p>
                                </div>

                                {{-- Metode Penarikan --}}
                                <div>
                                    <label for="withdrawal_method" class="block text-sm font-medium text-gray-700 mb-2">
                                        Metode Penarikan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="withdrawal_method" id="withdrawal_method"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-zinc-700 focus:border-transparent" required>
                                        <option value="">Pilih Metode Penarikan</option>
                                        <option value="transfer_bank" {{ old('withdrawal_method') == 'transfer_bank' ? 'selected' : '' }}>Transfer Bank</option>
                                        <option value="ewallet" {{ old('withdrawal_method') == 'ewallet' ? 'selected' : '' }}>E-Wallet</option>
                                    </select>
                                    @error('withdrawal_method')
                                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Informasi Rekening Tujuan --}}
                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Rekening Tujuan</h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                                                Nama Bank <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" name="bank_name" id="bank_name"
                                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-zinc-700 focus:border-transparent"
                                                value="{{ old('bank_name', $customer->bank_name) }}" required>
                                            @error('bank_name')
                                                <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">
                                                    Nomor Rekening <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="account_number" id="account_number"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-zinc-700 focus:border-transparent"
                                                    value="{{ old('account_number', $customer->bank_acc_number) }}" required>
                                                @error('account_number')
                                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                                @enderror
                                            </div>
                                            <div>
                                                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">
                                                    Nama Pemilik Rekening <span class="text-red-500">*</span>
                                                </label>
                                                <input type="text" name="account_name" id="account_name"
                                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-zinc-700 focus:border-transparent"
                                                    value="{{ old('account_name', $customer->bank_acc_name) }}" required>
                                                @error('account_name')
                                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Peringatan --}}
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="text-sm text-yellow-800">
                                            <p class="font-semibold mb-1">Perhatian:</p>
                                            <ul class="list-disc list-inside space-y-1">
                                                <li>Pastikan data rekening yang Anda masukkan sudah benar</li>
                                                <li>Proses penarikan memerlukan waktu 1-3 hari kerja</li>
                                                <li>Dana akan dikurangi dari saldo Anda setelah request diajukan</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                {{-- Buttons --}}
                                <div class="flex items-center gap-4 pt-4">
                                    <button type="submit"
                                        class="px-6 py-2 bg-zinc-700 text-white rounded-lg hover:bg-zinc-800 transition">
                                        Ajukan Penarikan
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
