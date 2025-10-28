@php
    // Ambil ID komponen Livewire secara resmi (v3)
    $componentId = method_exists($this, 'getId') ? $this->getId() : uniqid('lw-');

    // ID unik untuk tombol & dropdown (aman jika dipakai banyak instance)
    $btnId = 'cartDropdownButton-' . $componentId;
    $menuId = 'cartDropdown-' . $componentId;

    // Teks badge (99+)
    $label = $cartCount > 99 ? '99+' : $cartCount;
@endphp

<div class="relative">
    <button id="{{ $btnId }}" data-dropdown-toggle="{{ $menuId }}" data-dropdown-placement="bottom-end"
        class="relative p-2 text-gray-600 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-300"
        type="button" aria-label="Keranjang Belanja" aria-haspopup="true" aria-expanded="false"
        wire:loading.attr="aria-busy">
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

    {{-- Dropdown preview (sederhana, komponen ini memang hanya expose $cartCount) --}}
    <div id="{{ $menuId }}" class="z-50 hidden w-72 bg-white divide-y divide-gray-100 rounded-lg shadow-xl">
        <div class="p-4 border-b">
            <h5 class="text-base font-semibold text-gray-900">
                Keranjang Belanja ({{ $cartCount }} Item)
            </h5>
        </div>

        <div class="p-6 text-center text-sm text-gray-500">
            @if($cartCount < 1)
                Keranjang Anda kosong.
            @else
                @foreach ($cartData as $k => $v)
                {{-- {{ dd($v['product']) }} --}}
                    <li class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-600 transition duration-150">
                        <img class="w-12 h-12 rounded object-cover mr-3" src="{{ asset('storage/'.$v['product']['image']['url']) }}"
                            onerror="this.onerror=null;this.src='https://placehold.co/48x48/3e7a3e/ffffff?text=NA';"
                            alt="Product Image">
                        <div class="flex-grow">
                            <p class="text-sm font-medium text-gray-900 dark:text-white line-clamp-1">{{ $v['product']['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $v['qty'] }} x <span
                                    class="font-semibold text-gray-700 dark:text-gray-300">Rp {{ number_format($v['product']['base_price'], 0, ',', '.') }}</span></p>
                        </div>
                    </li>
                @endforeach
            @endif
        </div>

        <div class="flex flex-col p-4">
            <a href="{{ route('cart.index') }}"
                class="w-full text-white bg-zinc-700 hover:bg-zinc-800 focus:ring-4 focus:ring-zinc-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center transition">
                Lihat Keranjang & Checkout
            </a>
        </div>
    </div>

    {{-- Indikator loading kecil (opsional) --}}
    <div wire:loading.delay.shortest class="absolute -top-1 -right-1">
        <span class="inline-flex h-3 w-3 animate-ping rounded-full bg-zinc-400 opacity-75"></span>
    </div>
</div>
