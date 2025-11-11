@extends('layouts.app')

@section('title', 'Pembayaran Topup E-Wallet')

@section('content')
    <section class="py-12">
        <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                @include('pages.auth.profile.partial.sidebar')

                <main class="flex-1">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">Pembayaran Topup E-Wallet</h2>
                            <p class="text-gray-600 mt-2">Silakan selesaikan pembayaran untuk topup saldo e-wallet Anda</p>
                        </div>

                        {{-- Order Summary --}}
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Order ID:</span>
                                    <span class="font-medium">{{ $topup->order_no }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Jumlah Topup:</span>
                                    <span class="font-medium text-lg">Rp {{ number_format($topup->amount, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm">
                                        Menunggu Pembayaran
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Payment Button --}}
                        <div class="text-center">
                            <button id="pay-button"
                                class="px-8 py-3 bg-zinc-700 text-white rounded-lg hover:bg-zinc-800 transition text-lg font-medium">
                                Bayar Sekarang
                            </button>
                            <p class="text-sm text-gray-500 mt-4">
                                Anda akan diarahkan ke halaman pembayaran Midtrans
                            </p>
                        </div>

                        {{-- Back Link --}}
                        <div class="mt-8 text-center">
                            <a href="{{ route('auth.ewallet') }}" class="text-zinc-700 hover:text-zinc-800">
                                ← Kembali ke E-Wallet
                            </a>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
@endsection

@push('script')
    <!-- Midtrans Snap.js -->
    <script src="{{ config('services.midtrans.snap_url') }}"
            data-client-key="{{ config('services.midtrans.client_key') }}"></script>

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const payButton = document.getElementById('pay-button');
            const snapToken = '{{ $snapToken }}';
            const clientKey = '{{ config('services.midtrans.client_key') }}';

            // Debug logging
            console.log('Snap Token:', snapToken);
            console.log('Client Key:', clientKey);
            console.log('Snap available:', typeof snap !== 'undefined');

            if (!clientKey || clientKey === '') {
                alert('Error: Midtrans Client Key belum dikonfigurasi. Silakan tambahkan MIDTRANS_CLIENT_KEY ke file .env');
                payButton.disabled = true;
                return;
            }

            if (!snapToken || snapToken === '') {
                alert('Error: Snap Token tidak tersedia');
                payButton.disabled = true;
                return;
            }

            payButton.addEventListener('click', function(e) {
                e.preventDefault();

                if (typeof snap === 'undefined') {
                    alert('Error: Midtrans Snap library belum dimuat. Periksa koneksi internet Anda.');
                    return;
                }

                // Trigger Snap popup
                snap.pay(snapToken, {
                    onSuccess: function(result){
                        console.log('Payment Success:', result);
                        window.location.href = "{{ route('auth.ewallet.topup.finish', $topup->id) }}?status=success";
                    },
                    onPending: function(result){
                        console.log('Payment Pending:', result);
                        window.location.href = "{{ route('auth.ewallet.topup.finish', $topup->id) }}?status=pending";
                    },
                    onError: function(result){
                        console.log('Payment Error:', result);
                        window.location.href = "{{ route('auth.ewallet.topup.finish', $topup->id) }}?status=error";
                    },
                    onClose: function(){
                        console.log('Payment popup closed');
                        alert('Anda menutup popup pembayaran sebelum menyelesaikan transaksi');
                    }
                });
            });
        });
    </script>
@endpush
