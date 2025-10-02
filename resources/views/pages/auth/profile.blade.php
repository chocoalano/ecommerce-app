@extends('layouts.app')

@section('content')
@php
    // ====== DATA SEMENTARA (bisa diganti dari Controller) ======
    $breadcrumbs = [
        ['label' => 'Home', 'href' => '#'],
        ['label' => 'My account', 'href' => '#'],
        ['label' => 'Account', 'href' => null], // current
    ];

    $overviewStats = [
        [
            'icon' => 'truck', // hanya penanda; svg di bawah
            'label' => 'Orders made',
            'value' => '24',
            'delta' => '10.3%',
            'delta_bg' => 'bg-green-100 dark:bg-green-900',
            'delta_text' => 'text-green-800 dark:text-green-300',
            'note' => 'vs 20 last 3 months',
        ],
        [
            'icon' => 'star',
            'label' => 'Reviews added',
            'value' => '16',
            'delta' => '8.6%',
            'delta_bg' => 'bg-green-100 dark:bg-green-900',
            'delta_text' => 'text-green-800 dark:text-green-300',
            'note' => 'vs 14 last 3 months',
        ],
        [
            'icon' => 'heart',
            'label' => 'Favorite products added',
            'value' => '8',
            'delta' => '12%',
            'delta_bg' => 'bg-red-100 dark:bg-red-900',
            'delta_text' => 'text-red-800 dark:text-red-300',
            'note' => 'vs 10 last 3 months',
        ],
        [
            'icon' => 'return',
            'label' => 'Product returns',
            'value' => '2',
            'delta' => '50%',
            'delta_bg' => 'bg-green-100 dark:bg-green-900',
            'delta_text' => 'text-green-800 dark:text-green-300',
            'note' => 'vs 1 last 3 months',
        ],
    ];

    // mapping status => badge
    $statusMap = [
        'in_transit' => [
            'label' => 'In transit',
            'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'icon'  => 'truck-badge',
        ],
        'cancelled' => [
            'label' => 'Cancelled',
            'badge' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'icon'  => 'x',
        ],
        'completed' => [
            'label' => 'Completed',
            'badge' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'icon'  => 'check',
        ],
    ];

    $orders = [
        [
            'id' => '#FWB12546798',
            'date' => '11.12.2023',
            'price' => '$499',
            'status_key' => 'in_transit',
            'menu_id' => '10',
            'has_cancel' => true,
        ],
        [
            'id' => '#FWB12546777',
            'date' => '10.11.2024',
            'price' => '$3,287',
            'status_key' => 'cancelled',
            'menu_id' => '11',
            'has_cancel' => false,
        ],
        [
            'id' => '#FWB12546846',
            'date' => '07.11.2024',
            'price' => '$111',
            'status_key' => 'completed',
            'menu_id' => '12',
            'has_cancel' => false,
        ],
        [
            'id' => '#FWB12546212',
            'date' => '18.10.2024',
            'price' => '$756',
            'status_key' => 'completed',
            'menu_id' => '13',
            'has_cancel' => false,
        ],
    ];

    // negara untuk dropdown telepon
    $phoneCountries = [
        ['label' => 'United States (+1)', 'code' => '+1',  'flag' => 'us'],
        ['label' => 'United Kingdom (+44)', 'code' => '+44', 'flag' => 'uk'],
        ['label' => 'Australia (+61)', 'code' => '+61', 'flag' => 'au'],
        ['label' => 'Germany (+49)', 'code' => '+49', 'flag' => 'de'],
        ['label' => 'France (+33)', 'code' => '+33', 'flag' => 'fr'],
        ['label' => 'Germany (+49)', 'code' => '+49', 'flag' => 'de2'], // duplikat contoh asli dipertahankan
    ];
@endphp

<section class="bg-white py-8 antialiased dark:bg-gray-900 md:py-8">
    <div class="mx-auto max-w-5/6 px-4 2xl:px-0">

        {{-- Breadcrumbs --}}
        <nav class="mb-4 flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                @foreach ($breadcrumbs as $i => $bc)
                    <li class="inline-flex items-center">
                        @if ($i === 0)
                            <a href="{{ $bc['href'] ?? '#' }}"
                               class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-white">
                                {{-- home icon --}}
                                <svg class="me-2 h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5" />
                                </svg>
                                {{ $bc['label'] }}
                            </a>
                        @else
                            <div class="flex items-center">
                                <svg class="mx-1 h-4 w-4 text-gray-400 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                                </svg>
                                @if (!empty($bc['href']))
                                    <a href="{{ $bc['href'] }}"
                                       class="ms-1 text-sm font-medium text-gray-700 hover:text-primary-600 dark:text-gray-400 dark:hover:text-white md:ms-2">
                                        {{ $bc['label'] }}
                                    </a>
                                @else
                                    <span class="ms-1 text-sm font-medium text-gray-500 dark:text-gray-400 md:ms-2">
                                        {{ $bc['label'] }}
                                    </span>
                                @endif
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>

        <h2 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl md:mb-6">General overview</h2>

        {{-- Overview Stats --}}
        <div class="grid grid-cols-2 gap-6 border-b border-t border-gray-200 py-4 dark:border-gray-700 md:py-8 lg:grid-cols-4 xl:gap-16">
            @foreach ($overviewStats as $s)
                <div>
                    {{-- Ikon dinamis sederhana --}}
                    @switch($s['icon'])
                        @case('truck')
                            <svg class="mb-2 h-8 w-8 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312"/></svg>
                            @break
                        @case('star')
                            <svg class="mb-2 h-8 w-8 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z"/></svg>
                            @break
                        @case('heart')
                            <svg class="mb-2 h-8 w-8 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z"/></svg>
                            @break
                        @default
                            <svg class="mb-2 h-8 w-8 text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"/></svg>
                    @endswitch

                    <h3 class="mb-2 text-gray-500 dark:text-gray-400">{{ $s['label'] }}</h3>
                    <span class="flex items-center text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $s['value'] }}
                        <span class="ms-2 inline-flex items-center rounded px-2.5 py-0.5 text-xs font-medium {{ $s['delta_bg'] }} {{ $s['delta_text'] }}">
                            <svg class="-ms-1 me-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
                            {{ $s['delta'] }}
                        </span>
                    </span>
                    <p class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400 sm:text-base">
                        <svg class="me-1.5 h-4 w-4 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        {{ $s['note'] }}
                    </p>
                </div>
            @endforeach
        </div>

        {{-- Profile + Details (bagian ini mostly unik, dibiarkan) --}}
        <div class="py-4 md:py-8">
            <div class="mb-4 grid gap-4 sm:grid-cols-2 sm:gap-8 lg:gap-16">
                {{-- Kiri: Avatar & Info singkat --}}
                <div class="space-y-4">
                    <div class="flex space-x-4">
                        <img class="h-16 w-16 rounded-lg" src="https://flowbite.s3.amazonaws.com/blocks/marketing-ui/avatars/helene-engels.png" alt="Helene avatar" />
                        <div>
                            <span class="mb-2 inline-block rounded bg-primary-100 px-2.5 py-0.5 text-xs font-medium text-primary-800 dark:bg-primary-900 dark:text-primary-300">Akun Pembeli</span>
                            <h2 class="flex items-center text-xl font-bold leading-none text-gray-900 dark:text-white sm:text-2xl">{{ Auth::guard('customer')->user()->name }}</h2>
                        </div>
                    </div>

                    <dl><dt class="font-semibold text-gray-900 dark:text-white">Email Address</dt><dd class="text-gray-500 dark:text-gray-400">
                        {{ Auth::guard('customer')->user()->email }}</dd></dl>
                    <dl>
                        <dt class="font-semibold text-gray-900 dark:text-white">Home Address</dt>
                        <dd class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                            <svg class="hidden h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 lg:inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/></svg>
                            2 Miles Drive, NJ 071, New York, United States of America
                        </dd>
                    </dl>
                    <dl>
                        <dt class="font-semibold text-gray-900 dark:text-white">Delivery Address</dt>
                        <dd class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                            <svg class="hidden h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 lg:inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Zm-10 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/></svg>
                            9th St. PATH Station, New York, United States of America
                        </dd>
                    </dl>
                </div>

                {{-- Kanan: Detail lainnya --}}
                <div class="space-y-4">
                    <dl><dt class="font-semibold text-gray-900 dark:text-white">Phone Number</dt><dd class="text-gray-500 dark:text-gray-400">+1234 567 890 / +12 345 678</dd></dl>
                    <dl>
                        <dt class="font-semibold text-gray-900 dark:text-white">Favorite pick-up point</dt>
                        <dd class="flex items-center gap-1 text-gray-500 dark:text-gray-400">
                            <svg class="hidden h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500 lg:inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12c.263 0 .524-.06.767-.175a2 2 0 0 0 .65-.491c.186-.21.333-.46.433-.734.1-.274.15-.568.15-.864a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 12 9.736a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 16 9.736c0 .295.052.588.152.861s.248.521.434.73a2 2 0 0 0 .649.488 1.809 1.809 0 0 0 1.53 0 2.03 2.03 0 0 0 .65-.488c.185-.209.332-.457.433-.73.1-.273.152-.566.152-.861 0-.974-1.108-3.85-1.618-5.121A.983.983 0 0 0 17.466 4H6.456a.986.986 0 0 0-.93.645C5.045 5.962 4 8.905 4 9.736c.023.59.241 1.148.611 1.567.37.418.865.667 1.389.697Zm0 0c.328 0 .651-.091.94-.266A2.1 2.1 0 0 0 7.66 11h.681a2.1 2.1 0 0 0 .718.734c.29.175.613.266.942.266.328 0 .651-.091.94-.266.29-.174.537-.427.719-.734h.681a2.1 2.1 0 0 0 .719.734c.289.175.612.266.94.266.329 0 .652-.091.942-.266.29-.174.536-.427.718-.734h.681c.183.307.43.56.719.734.29.174.613.266.941.266a1.819 1.819 0 0 0 1.06-.351"/></svg>
                            Herald Square, 2, New York, United States of America
                        </dd>
                    </dl>
                    <dl><dt class="font-semibold text-gray-900 dark:text-white">My Companies</dt><dd class="text-gray-500 dark:text-gray-400">FLOWBITE LLC, Fiscal code: 18673557</dd></dl>
                    <dl>
                        <dt class="mb-1 font-semibold text-gray-900 dark:text-white">Payment Methods</dt>
                        <dd class="flex items-center space-x-4 text-gray-500 dark:text-gray-400">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                <img class="h-4 w-auto dark:hidden" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/brand-logos/visa.svg" alt="" />
                                <img class="hidden h-4 w-auto dark:flex" src="https://flowbite.s3.amazonaws.com/blocks/e-commerce/brand-logos/visa-dark.svg" alt="" />
                            </div>
                            <div class="text-sm">
                                <p class="mb-0.5 font-medium text-gray-900 dark:text-white">Visa ending in 7658</p>
                                <p class="font-normal text-gray-500 dark:text-gray-400">Expiry 10/2024</p>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>

            <button type="button" data-modal-target="accountInformationModal2" data-modal-toggle="accountInformationModal2"
                class="inline-flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 sm:w-auto">
                <svg class="-ms-0.5 me-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/></svg>
                Edit your data
            </button>
        </div>

        {{-- Latest Orders (loop) --}}
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 md:p-8">
            <h3 class="mb-4 text-xl font-semibold text-gray-900 dark:text-white">Latest orders</h3>

            @foreach ($orders as $idx => $o)
                @php
                    $status = $statusMap[$o['status_key']];
                @endphp
                <div class="flex flex-wrap items-center gap-y-4 {{ $idx < count($orders)-1 ? 'border-b border-gray-200 md:py-5 py-4 pb-4 dark:border-gray-700' : 'pt-4 md:pt-5' }}">
                    <dl class="w-1/2 sm:w-48">
                        <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Order ID:</dt>
                        <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">
                            <a href="#" class="hover:underline">{{ $o['id'] }}</a>
                        </dd>
                    </dl>

                    <dl class="w-1/2 sm:w-1/4 md:flex-1 lg:w-auto">
                        <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Date:</dt>
                        <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">{{ $o['date'] }}</dd>
                    </dl>

                    <dl class="w-1/2 sm:w-1/5 md:flex-1 lg:w-auto">
                        <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Price:</dt>
                        <dd class="mt-1.5 text-base font-semibold text-gray-900 dark:text-white">{{ $o['price'] }}</dd>
                    </dl>

                    <dl class="w-1/2 sm:w-1/4 sm:flex-1 lg:w-auto">
                        <dt class="text-base font-medium text-gray-500 dark:text-gray-400">Status:</dt>
                        <dd class="me-2 mt-1.5 inline-flex shrink-0 items-center rounded px-2.5 py-0.5 text-xs font-medium {{ $status['badge'] }}">
                            @switch($status['icon'])
                                @case('truck-badge')
                                    <svg class="me-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/></svg>
                                    @break
                                @case('x')
                                    <svg class="me-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
                                    @break
                                @default
                                    <svg class="me-1 h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/></svg>
                            @endswitch
                            {{ $status['label'] }}
                        </dd>
                    </dl>

                    <div class="w-full sm:flex sm:w-32 sm:items-center sm:justify-end sm:gap-4">
                        @php $menu = 'dropdownOrderModal'.$o['menu_id']; $btn = 'actionsMenuDropdownModal'.$o['menu_id']; @endphp
                        <button id="{{ $btn }}" data-dropdown-toggle="{{ $menu }}" type="button"
                            class="flex w-full items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700 md:w-auto">
                            Actions
                            <svg class="-me-0.5 ms-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/></svg>
                        </button>
                        <div id="{{ $menu }}"
                             class="z-10 hidden w-40 divide-y divide-gray-100 rounded-lg bg-white shadow dark:bg-gray-700">
                            <ul class="p-2 text-left text-sm font-medium text-gray-500 dark:text-gray-400" aria-labelledby="{{ $btn }}">
                                <li>
                                    <a href="#"
                                       class="group inline-flex w-full items-center rounded-md px-3 py-2 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                        <svg class="me-1.5 h-4 w-4 text-gray-400 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.651 7.65a7.131 7.131 0 0 0-12.68 3.15M18.001 4v4h-4m-7.652 8.35a7.13 7.13 0 0 0 12.68-3.15M6 20v-4h4"/></svg>
                                        <span>Order again</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="#"
                                       class="group inline-flex w-full items-center rounded-md px-3 py-2 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white">
                                        <svg class="me-1.5 h-4 w-4 text-gray-400 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/><path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        Order details
                                    </a>
                                </li>
                                @if($o['has_cancel'])
                                    <li>
                                        <a href="#" data-modal-target="deleteOrderModal" data-modal-toggle="deleteOrderModal"
                                           class="group inline-flex w-full items-center rounded-md px-3 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                            <svg class="me-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/></svg>
                                            Cancel order
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Account Information Modal --}}
    <div id="accountInformationModal2" tabindex="-1" aria-hidden="true"
         class="max-h-auto fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] max-h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden antialiased md:inset-0">
        <div class="max-h-auto relative max-h-full w-full max-w-lg p-4">
            <div class="relative rounded-lg bg-white shadow dark:bg-gray-800">
                <div class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 dark:border-gray-700 md:p-5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Account Information</h3>
                    <button type="button" class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="accountInformationModal2">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <form class="p-4 md:p-5">
                    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="col-span-2">
                            <label for="pick-up-point-input" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Pick-up point*</label>
                            <input type="text" id="pick-up-point-input" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" placeholder="Enter the pick-up point name" required />
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="full_name_info_modal" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Your Full Name*</label>
                            <input type="text" id="full_name_info_modal" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" placeholder="Enter your first name" required />
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="email_info_modal" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Your Email*</label>
                            <input type="text" id="email_info_modal" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" placeholder="Enter your email here" required />
                        </div>

                        <div class="col-span-2">
                            <label for="phone-input_billing_modal" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Phone Number*</label>
                            <div class="flex items-center">
                                <button id="dropdown_phone_input__button_billing_modal" data-dropdown-toggle="dropdown_phone_input_billing_modal"
                                        class="z-10 inline-flex shrink-0 items-center rounded-s-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-center text-sm font-medium text-gray-900 hover:bg-gray-200 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-700"
                                        type="button">
                                    {{-- Default US Flag (dipertahankan) --}}
                                    <svg fill="none" aria-hidden="true" class="me-2 h-4 w-4" viewBox="0 0 20 15">
                                        <rect width="19.6" height="14" y=".5" fill="#fff" rx="2" />
                                        <!-- … (flag path asli dipertahankan untuk ringkas) … -->
                                    </svg>
                                    +1
                                    <svg class="-me-0.5 ms-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" /></svg>
                                </button>

                                <div id="dropdown_phone_input_billing_modal" class="z-10 hidden w-56 divide-y divide-gray-100 rounded-lg bg-white shadow dark:bg-gray-700">
                                    <ul class="p-2 text-sm font-medium text-gray-700 dark:text-gray-200" aria-labelledby="dropdown_phone_input__button_billing_modal">
                                        @foreach ($phoneCountries as $pc)
                                            <li>
                                                <button type="button"
                                                    class="inline-flex w-full rounded-md px-3 py-2 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white"
                                                    role="menuitem">
                                                    <span class="inline-flex items-center">
                                                        {{-- Untuk ringkas, ikon bendera tidak diulang semua path-nya.
                                                             Bisa dibuat komponen kecil bila perlu. --}}
                                                        <svg class="me-2 h-4 w-4" viewBox="0 0 20 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <rect width="19.6" height="14" y=".5" fill="#fff" rx="2" />
                                                        </svg>
                                                        {{ $pc['label'] }}
                                                    </span>
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="relative w-full">
                                    <input type="text" id="phone-input"
                                           class="z-20 block w-full rounded-e-lg border border-s-0 border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:border-s-gray-700  dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500"
                                           pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" placeholder="123-456-7890" required />
                                </div>
                            </div>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="select_country_input_billing_modal" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Country*</label>
                            <select id="select_country_input_billing_modal" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500">
                                <option selected>United States</option>
                                <option value="AS">Australia</option>
                                <option value="FR">France</option>
                                <option value="ES">Spain</option>
                                <option value="UK">United Kingdom</option>
                            </select>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="select_city_input_billing_modal" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">City*</label>
                            <select id="select_city_input_billing_modal" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500">
                                <option selected>San Francisco</option>
                                <option value="NY">New York</option>
                                <option value="LA">Los Angeles</option>
                                <option value="CH">Chicago</option>
                                <option value="HU">Houston</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label for="address_billing_modal" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Delivery Address*</label>
                            <textarea id="address_billing_modal" rows="4" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" placeholder="Enter here your address"></textarea>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="company_name" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">Company name</label>
                            <input type="text" id="company_name" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" placeholder="Flowbite LLC" />
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="vat_number" class="mb-2 block text-sm font-medium text-gray-900 dark:text-white">VAT number</label>
                            <input type="text" id="vat_number" class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder:text-gray-400 dark:focus:border-primary-500 dark:focus:ring-primary-500" placeholder="DE42313253" />
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 dark:border-gray-700 md:pt-5">
                        <button type="submit" class="me-2 inline-flex items-center rounded-lg bg-primary-700 px-5 py-2.5 text-center text-sm font-medium text-white hover:bg-primary-800 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">Save information</button>
                        <button type="button" data-modal-toggle="accountInformationModal2" class="me-2 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:outline-none focus:ring-4 focus:ring-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white dark:focus:ring-gray-700">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Order Modal (tanpa perubahan berarti) --}}
    <div id="deleteOrderModal" tabindex="-1" aria-hidden="true"
         class="fixed left-0 right-0 top-0 z-50 hidden h-modal w-full items-center justify-center overflow-y-auto overflow-x-hidden md:inset-0 md:h-full">
        <div class="relative h-full w-full max-w-md p-4 md:h-auto">
            <div class="relative rounded-lg bg-white p-4 text-center shadow dark:bg-gray-800 sm:p-5">
                <button type="button" class="absolute right-2.5 top-2.5 ml-auto inline-flex items-center rounded-lg bg-transparent p-1.5 text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-600 dark:hover:text-white" data-modal-toggle="deleteOrderModal">
                    <svg aria-hidden="true" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    <span class="sr-only">Close modal</span>
                </button>
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 p-2 dark:bg-gray-700">
                    <svg class="h-8 w-8 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z"/></svg>
                    <span class="sr-only">Danger icon</span>
                </div>
                <p class="mb-3.5 text-gray-900 dark:text-white"><a href="#" class="font-medium text-primary-700 hover:underline dark:text-primary-500">@heleneeng</a>, are you sure you want to delete this order from your account?</p>
                <p class="mb-4 text-gray-500 dark:text-gray-300">This action cannot be undone.</p>
                <div class="flex items-center justify-center space-x-4">
                    <button data-modal-toggle="deleteOrderModal" type="button" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:z-10 focus:outline-none focus:ring-4 focus:ring-primary-300 dark:border-gray-500 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white dark:focus:ring-gray-600">No, cancel</button>
                    <button type="submit" class="rounded-lg bg-red-700 px-3 py-2 text-center text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-300 dark:bg-red-600 dark:hover:bg-red-700 dark:focus:ring-red-900">Yes, delete</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
