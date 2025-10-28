@php
    // ... (Logika ID tetap sama) ...
    $componentId = method_exists($this, 'getId') ? $this->getId() : uniqid('lw-');
    $label = $cartCount > 99 ? '99+' : $cartCount;
@endphp

{{-- Tambahkan x-data untuk mengelola state dropdown: x-data="{ open: false }" --}}
<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    {{-- BUTTON: Ganti data-dropdown-toggle dengan @click --}}
    <button @click="open = ! open"
        class="relative p-2 text-gray-600 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-300"
        type="button" aria-label="Keranjang Belanja" aria-haspopup="true" aria-expanded="false"
        wire:loading.attr="aria-busy">
        {{-- ... (Ikon Keranjang dan Badge tetap sama) ... --}}

        {{-- Ikon keranjang --}}
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.94 1.88A2 2 0 0 0 7.76 6h10.49a1 1 0 0 1 .96 1.27l-1.5 6A2 2 0 0 1 15.78 15H9a2 2 0 1 0 0 4h7a2 2 0 1 0 0-4M7 19a2 2 0 1 1 0-4m10 4a2 2 0 1 1 0-4" />
        </svg>

        {{-- Badge jumlah item (sembunyi saat 0) --}}
        <span @class([
            'absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-purple-600 rounded-full ring-2 ring-white min-w-[1.25rem]',
            'hidden' => $cartCount < 1,
        ]) aria-hidden="true">
            {{ $label }}
        </span>
        <span class="sr-only">Item di keranjang: {{ $cartCount }}</span>
    </button>

    {{-- DROPDOWN MENU: Ganti hidden dengan x-cloak dan x-show --}}
    <div x-cloak x-show="open"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"

        class="absolute right-0 mt-2 z-50 w-72 bg-white divide-y divide-gray-100 rounded-lg shadow-xl origin-top-right">
        {{-- ... (Isi Dropdown tetap sama) ... --}}
        <div class="p-4 border-b">
             <h5 class="text-base font-semibold text-gray-900">
                Keranjang Belanja ({{ $cartCount }} Item)
            </h5>
        </div>

        <ul class="max-h-60 overflow-y-auto"> {{-- Tambahkan max-h dan overflow untuk scroll --}}
            @if($cartCount < 1)
                <div class="p-6 text-center text-sm text-gray-500">Keranjang Anda kosong.</div>
            @else
                @foreach ($cartData as $v)
                    <li class="flex items-center p-3 hover:bg-gray-50 transition duration-150">
                        <img class="w-12 h-12 rounded object-cover mr-3" src="{{ asset('storage/'.$v['product']['image']['url']) }}"
                            onerror="this.onerror=null;this.src='https://placehold.co/48x48/3e7a3e/ffffff?text=NA';"
                            alt="Product Image">
                        <div class="flex-grow">
                            <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $v['product']['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $v['qty'] }} x <span
                                    class="font-semibold text-gray-700">Rp {{ number_format($v['product']['base_price'], 0, ',', '.') }}</span></p>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>

        <div class="flex flex-col p-4">
            <a href="{{ route('cart.index') }}"
                class="w-full text-white bg-zinc-700 hover:bg-zinc-800 focus:ring-4 focus:ring-zinc-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition">
                Lihat Keranjang & Checkout
            </a>
        </div>
        {{-- ... (End Isi Dropdown) ... --}}
    </div>

    {{-- Indikator loading kecil (opsional) --}}
    <div wire:loading.delay.shortest class="absolute -top-1 -right-1">
        <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-zinc-400 opacity-75"></span>
    </div>
</div>
