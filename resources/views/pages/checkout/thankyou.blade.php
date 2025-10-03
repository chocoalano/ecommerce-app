{{-- resources/views/pages/checkout/thankyou.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="max-w-2xl mx-auto bg-white border border-gray-200 rounded-xl p-8 text-center">
    <svg class="w-14 h-14 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 005.143 12v3.242c0 5.421 3.518 9.092 6.848 10.985a1 1 0 00.99 0c3.33-1.893 6.848-5.564 6.848-10.985V12a12.02 12.02 0 00-2.382-6.758z"/>
    </svg>
    <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Terima Kasih!</h1>
    <p class="text-gray-600">Pesanan {{ $order->order_no }} sedang diproses.</p>

    @php $notes = json_decode($order->notes ?? '[]', true); @endphp
    @if(!empty($notes['url']))
      <p class="text-sm text-gray-500 mt-4">Belum menyelesaikan pembayaran? Klik:</p>
      <a href="{{ $notes['url'] }}" class="inline-flex items-center justify-center px-5 py-2 mt-3 text-sm font-semibold text-white bg-zinc-900 rounded-lg hover:bg-zinc-700 focus:ring-4 focus:ring-zinc-300">
        Lanjutkan Pembayaran
      </a>
    @endif

    <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-5 py-2 mt-3 text-sm font-semibold text-zinc-900 border border-zinc-900 rounded-lg hover:bg-zinc-50">
      Kembali ke Beranda
    </a>
  </div>
</div>
@endsection
