@extends('layouts.app')

@section('content')
@php
    // View variables are prepared by the controller: $customer, $breadcrumbs, $overviewStats, $statusMap, $orders, $phoneCountries
    $customer = $customer ?? null;

    // Koleksi alamat & alamat utama sekali saja (hindari panggil where() berulang di view)
    $addresses = $customer?->addresses ?? collect();
    $primaryAddress = $addresses->firstWhere('is_default', true)
        ?? $addresses->firstWhere('label', 'Utama')
        ?? $addresses->first();

    // Helper ringkas untuk inisial avatar (tanpa Str facade)
    $avatarInitial = strtoupper(substr($customer?->name ?? '', 0, 1));
@endphp

<section class="bg-white py-8 antialiased md:py-8">
    <div class="mx-auto max-w-7xl px-4 2xl:px-0">
        <div class="grid grid-cols-12 gap-6">
            @include('pages.auth.profile.partial.sidebar')

            <div class="col-span-12 md:col-span-9">

                {{-- Breadcrumbs --}}
                @if(!empty($breadcrumbs))
                <nav class="mb-4 flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-2 rtl:space-x-reverse">
                        @foreach ($breadcrumbs as $i => $bc)
                            <li class="inline-flex items-center">
                                @if ($i === 0)
                                    <a href="{{ $bc['href'] ?? '#' }}"
                                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-zinc-600">
                                        <svg class="me-2 h-4 w-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5" />
                                        </svg>
                                        {{ $bc['label'] ?? 'Home' }}
                                    </a>
                                @else
                                    <div class="flex items-center">
                                        <svg class="mx-1 h-4 w-4 text-gray-400 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                                        </svg>
                                        @if (!empty($bc['href']))
                                            <a href="{{ $bc['href'] }}"
                                               class="ms-1 text-sm font-medium text-gray-700 hover:text-zinc-600 md:ms-2">
                                                {{ $bc['label'] ?? '-' }}
                                            </a>
                                        @else
                                            <span class="ms-1 text-sm font-medium text-gray-500 md:ms-2">
                                                {{ $bc['label'] ?? '-' }}
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
                @endif

                {{-- FLASH: status / success --}}
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                         role="alert" aria-live="polite" aria-atomic="true"
                         class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="h-5 w-5 text-green-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>
                            </svg>
                            <p class="text-sm font-medium text-green-800">
                                {{ session('success') }}
                            </p>
                            <button type="button" @click="show=false" aria-label="Tutup"
                                    class="ms-auto rounded p-1 text-green-700/70 hover:bg-green-100">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <h2 class="mb-4 text-xl font-semibold text-gray-900 sm:text-2xl md:mb-6">Ringkasan Umum</h2>

                {{-- Overview Stats --}}
                <div class="grid grid-cols-2 gap-6 border-y border-gray-200 py-4 md:py-8 lg:grid-cols-4 xl:gap-16">
                    @foreach ($overviewStats as $s)
                        <div>
                            @switch($s['icon'] ?? null)
                                @case('truck')
                                    <svg class="mb-2 h-8 w-8 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 4h1.5L9 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm-8.5-3h9.25L19 7H7.312"/></svg>
                                    @break
                                @case('star')
                                    <svg class="mb-2 h-8 w-8 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-width="2" d="M11.083 5.104c.35-.8 1.485-.8 1.834 0l1.752 4.022a1 1 0 0 0 .84.597l4.463.342c.9.069 1.255 1.2.556 1.771l-3.33 2.723a1 1 0 0 0-.337 1.016l1.03 4.119c.214.858-.71 1.552-1.474 1.106l-3.913-2.281a1 1 0 0 0-1.008 0L7.583 20.8c-.764.446-1.688-.248-1.474-1.106l1.03-4.119A1 1 0 0 0 6.8 14.56l-3.33-2.723c-.698-.571-.342-1.702.557-1.771l4.462-.342a1 1 0 0 0 .84-.597l1.753-4.022Z"/></svg>
                                    @break
                                @case('heart')
                                    <svg class="mb-2 h-8 w-8 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.01 6.001C6.5 1 1 8 5.782 13.001L12.011 20l6.23-7C23 8 17.5 1 12.01 6.002Z"/></svg>
                                    @break
                                @default
                                    <svg class="mb-2 h-8 w-8 text-zinc-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9h13a5 5 0 0 1 0 10H7M3 9l4-4M3 9l4 4"/></svg>
                            @endswitch

                            <h3 class="mb-2 text-gray-500">{{ $s['label'] ?? '-' }}</h3>
                            <span class="flex items-center text-2xl font-bold text-gray-900">
                                {{ $s['value'] ?? '0' }}
                                @if(!empty($s['delta']))
                                    <span class="ms-2 inline-flex items-center rounded px-2.5 py-0.5 text-xs font-medium {{ $s['delta_bg'] ?? 'bg-zinc-100' }} {{ $s['delta_text'] ?? 'text-zinc-700' }}">
                                        <svg class="-ms-1 me-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v13m0-13 4 4m-4-4-4 4"/></svg>
                                        {{ $s['delta'] }}
                                    </span>
                                @endif
                            </span>
                            @if(!empty($s['note']))
                                <p class="mt-2 flex items-center text-sm text-gray-500 sm:text-base">
                                    <svg class="me-1.5 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 11h2v5m-2 0h4m-2.592-8.5h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    {{ $s['note'] }}
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Profile + Details --}}
                <div class="py-4 md:py-8">
                    <div class="mb-4 grid gap-4 sm:grid-cols-2 sm:gap-8 lg:gap-16">
                        {{-- Kiri: Avatar & Info singkat --}}
                        <div class="space-y-4">
                            <div class="flex space-x-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-lg bg-zinc-200 text-2xl font-bold text-zinc-700">
                                    {{ $avatarInitial }}
                                </div>
                                <div>
                                    <span class="mb-2 inline-block rounded bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800">Akun Pembeli</span>
                                    <h2 class="flex items-center text-xl font-bold leading-none text-gray-900 sm:text-2xl">
                                        {{ $customer?->name ?? '' }}
                                    </h2>
                                </div>
                            </div>

                            <dl>
                                <dt class="font-semibold text-gray-900">Alamat Email</dt>
                                <dd class="text-gray-500">{{ $customer?->email ?? '' }}</dd>
                            </dl>

                            <dl>
                                <dt class="font-semibold text-gray-900">Alamat Rumah</dt>
                                <dd class="flex items-center gap-1 text-gray-500">
                                    <svg class="hidden h-5 w-5 shrink-0 text-gray-400 lg:inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12 8-8 8 8M6 10.5V19a1 1 0 0 0 1 1h3v-3a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h3a1 1 0 0 0 1-1v-8.5"/></svg>
                                    {{ $primaryAddress?->line1 ?? '' }}
                                </dd>
                            </dl>

                            <dl>
                                <dt class="font-semibold text-gray-900">Alamat Pengiriman</dt>
                                <dd class="flex items-center gap-1 text-gray-500">
                                    <svg class="hidden h-5 w-5 shrink-0 text-gray-400 lg:inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z"/></svg>
                                    {{ $primaryAddress?->line2 ?? '' }}
                                </dd>
                            </dl>
                        </div>

                        {{-- Kanan: Detail lainnya --}}
                        <div class="space-y-4">
                            <dl>
                                <dt class="font-semibold text-gray-900">Nomor Telepon</dt>
                                <dd class="text-gray-500">{{ $customer?->phone ?? '' }}</dd>
                            </dl>
                            <dl>
                                <dt class="font-semibold text-gray-900">Titik pengambilan favorit</dt>
                                <dd class="flex items-center gap-1 text-gray-500">
                                    <svg class="hidden h-5 w-5 shrink-0 text-gray-400 lg:inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12c.263 0 .524-.06.767-.175a2 2 0 0 0 .65-.491c.186-.21.333-.46.433-.734.1-.274.15-.568.15-.864a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 12 9.736a2.4 2.4 0 0 0 .586 1.591c.375.422.884.659 1.414.659.53 0 1.04-.237 1.414-.659A2.4 2.4 0 0 0 16 9.736c0 .295.052.588.152.861s.248.521.434.73a2 2 0 0 0 .649.488 1.809 1.809 0 0 0 1.53 0 2.03 2.03 0 0 0 .65-.488c.185-.209.332-.457.433-.73.1-.273.152-.566.152-.861 0-.974-1.108-3.85-1.618-5.121A.983.983 0 0 0 17.466 4H6.456a.986.986 0 0 0-.93.645C5.045 5.962 4 8.905 4 9.736c.023.59.241 1.148.611 1.567.37.418.865.667 1.389.697Z"/></svg>
                                    {{ $primaryAddress?->line1 ?? '' }}
                                </dd>
                            </dl>
                        </div>
                    </div>

                    {{-- Tombol Edit --}}
                    <button type="button"
                            data-modal-target="accountInformationModal2" data-modal-toggle="accountInformationModal2"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-zinc-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-4 focus:ring-zinc-300 sm:w-auto">
                        <svg class="-ms-0.5 me-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/></svg>
                        Edit data Anda
                    </button>
                    <button type="button"
                            data-modal-target="accountPasswordChangeModal" data-modal-toggle="accountPasswordChangeModal"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-zinc-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-4 focus:ring-zinc-300 sm:w-auto">
                        <svg class="-ms-0.5 me-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/></svg>
                        Ubah password Anda
                    </button>
                </div>

                {{-- Latest Orders --}}
                <div id="ordersContainer"
                    data-source-url="{{ route('auth.profile') }}"
                    data-cancel-url-template="/auth/orders/:id/cancel"
                    data-detail-url-template="/auth/orders/:id">
                </div>

                <div id="ordersFeedback" class="hidden mb-3 rounded border p-2 text-sm"></div>

                <div id="ordersList"></div>
                <nav id="ordersPagination" class="mt-3"></nav>
            </div>
        </div>
    </div>

    {{-- Account Information Modal --}}
    <div id="accountInformationModal2" tabindex="-1" aria-hidden="true"
         class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] w-full items-center justify-center overflow-y-auto overflow-x-hidden antialiased md:inset-0">
        <div class="relative max-h-full w-full max-w-lg p-4">
            <div class="relative rounded-lg bg-white">
                <div class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5">
                    <h3 class="ml-5 mt-10 text-lg font-semibold text-gray-900">Informasi Akun</h3>
                    <button type="button" data-modal-toggle="accountInformationModal2"
                            class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                        <span class="sr-only">Tutup</span>
                    </button>
                </div>
                <form id="profileUpdateForm" method="POST" action="{{ route('auth.profile.update') }}" class="p-4 md:p-5" novalidate>
                    @csrf
                    @php $address = $primaryAddress; @endphp

                    <!-- Feedback message container (dipakai oleh JS: #feedback-message & #feedback-content) -->
                    <div id="feedback-message" class="mb-4" style="display:none;">
                        <div id="feedback-content" class="rounded border p-3 text-sm"></div>
                    </div>

                    {{-- ====== DATA PROFIL (Customer) ====== --}}
                    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="col-span-2 sm:col-span-1">
                        <label for="name_input" class="mb-2 block text-sm font-medium text-gray-900">Nama (name)</label>
                        <input type="text" id="name_input" name="name"
                                value="{{ old('name', $customer?->name) }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Mis. username / nama singkat">
                        <p id="error-name" class="error-message"></p>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                        <label for="full_name_input" class="mb-2 block text-sm font-medium text-gray-900">Nama Lengkap (full_name)*</label>
                        <input type="text" id="full_name_input" name="full_name"
                                value="{{ old('full_name', $customer?->full_name) }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Nama lengkap" required>
                        <p id="error-full_name" class="error-message"></p>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                        <label for="email_input" class="mb-2 block text-sm font-medium text-gray-900">Email*</label>
                        <input type="email" id="email_input" name="email"
                                value="{{ old('email', $customer?->email) }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Email aktif" required>
                        <p id="error-email" class="error-message"></p>
                        </div>

                        <div class="col-span-2">
                        <label for="phone_input" class="mb-2 block text-sm font-medium text-gray-900">Nomor Telepon (phone)*</label>
                        <input type="text" id="phone_input" name="phone"
                                value="{{ old('phone', $customer?->phone) }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Nomor telepon" required>
                        <p id="error-phone" class="error-message"></p>
                        </div>
                    </div>

                    {{-- ====== DATA ALAMAT (Address) ====== --}}
                    <div class="mb-6">
                        <div class="mb-3 text-sm font-semibold text-gray-800">Alamat Utama</div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="col-span-2 sm:col-span-1">
                            <label for="address_label" class="mb-2 block text-sm font-medium text-gray-900">Label (label)*</label>
                            <input type="text" id="address_label" name="address[label]"
                                value="{{ old('address.label', $address->label ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Mis. Rumah, Kantor" required>
                            <p id="error-address-label" class="error-message"></p>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="address_recipient_name" class="mb-2 block text-sm font-medium text-gray-900">Nama Penerima (recipient_name)*</label>
                            <input type="text" id="address_recipient_name" name="address[recipient_name]"
                                value="{{ old('address.recipient_name', $address->recipient_name ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Nama penerima" required>
                            <p id="error-address-recipient_name" class="error-message"></p>
                        </div>

                        <div class="col-span-2">
                            <label for="address_phone_recipient" class="mb-2 block text-sm font-medium text-gray-900">Telepon Penerima (phone)*</label>
                            <input type="text" id="address_phone_recipient" name="address[phone]"
                                value="{{ old('address.phone', $address->phone ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Nomor telepon penerima" required>
                            <p id="error-address-phone" class="error-message"></p>
                        </div>

                        <div class="col-span-2">
                            <label for="address_line1" class="mb-2 block text-sm font-medium text-gray-900">Alamat Baris 1 (line1)*</label>
                            <input type="text" id="address_line1" name="address[line1]"
                                value="{{ old('address.line1', $address->line1 ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Jalan, nomor, RT/RW" required>
                            <p id="error-address-line1" class="error-message"></p>
                        </div>

                        <div class="col-span-2">
                            <label for="address_line2" class="mb-2 block text-sm font-medium text-gray-900">Alamat Baris 2 (line2)</label>
                            <input type="text" id="address_line2" name="address[line2]"
                                value="{{ old('address.line2', $address->line2 ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Blok, lantai, patokan (opsional)">
                            <p id="error-address-line2" class="error-message"></p>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="address_city" class="mb-2 block text-sm font-medium text-gray-900">Kota/Kabupaten (city)*</label>
                            <input type="text" id="address_city" name="address[city]"
                                value="{{ old('address.city', $address->city ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                required>
                            <p id="error-address-city" class="error-message"></p>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="address_province" class="mb-2 block text-sm font-medium text-gray-900">Provinsi (province)*</label>
                            <input type="text" id="address_province" name="address[province]"
                                value="{{ old('address.province', $address->province ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                required>
                            <p id="error-address-province" class="error-message"></p>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="address_postal_code" class="mb-2 block text-sm font-medium text-gray-900">Kode Pos (postal_code)*</label>
                            <input type="text" id="address_postal_code" name="address[postal_code]"
                                value="{{ old('address.postal_code', $address->postal_code ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                required>
                            <!-- sinkron dengan JS: #error-address-postal-code (pakai tanda minus) -->
                            <p id="error-address-postal-code" class="error-message"></p>
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="address_country" class="mb-2 block text-sm font-medium text-gray-900">Negara (country)*</label>
                            <input type="text" id="address_country" name="address[country]"
                                value="{{ old('address.country', $address->country ?? '') }}"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Mis. ID" required>
                            <p id="error-address-country" class="error-message"></p>
                        </div>

                        <div class="col-span-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-900">
                            <input type="checkbox" name="address[is_default]" value="1"
                                    @checked(old('address.is_default', $address->is_default ?? false))
                                    class="h-4 w-4 rounded border-gray-300 text-zinc-700 focus:ring-zinc-500">
                            Jadikan sebagai alamat utama (is_default)
                            </label>
                        </div>
                        </div>
                    </div>

                    <button id="submitButton" type="submit"
                            class="inline-flex w-full items-center justify-center rounded-lg bg-zinc-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-4 focus:ring-zinc-300">
                        <svg class="-ms-0.5 me-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Simpan perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Password Change Modal --}}
    <div id="accountPasswordChangeModal" tabindex="-1" aria-hidden="true"
         class="fixed left-0 right-0 top-0 z-50 hidden h-[calc(100%-1rem)] w-full items-center justify-center overflow-y-auto overflow-x-hidden antialiased md:inset-0">
        <div class="relative max-h-full w-full max-w-lg p-4">
            <div class="relative rounded-lg bg-white">
                <div class="flex items-center justify-between rounded-t border-b border-gray-200 p-4 md:p-5">
                    <h3 class="ml-5 mt-10 text-lg font-semibold text-gray-900">Ganti password</h3>
                    {{-- FIX: toggle mengarah ke modal ini, bukan ke accountInformationModal2 --}}
                    <button type="button" data-modal-toggle="accountPasswordChangeModal"
                            class="ms-auto inline-flex h-8 w-8 items-center justify-center rounded-lg bg-transparent text-sm text-gray-400 hover:bg-gray-200 hover:text-gray-900">
                        <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                        <span class="sr-only">Tutup</span>
                    </button>
                </div>

                <form id="changePasswordForm" method="POST" action="{{ route('auth.profile.password.update') }}" class="p-4 md:p-5" novalidate>
                    @csrf

                    <!-- Feedback area (dipakai jQuery) -->
                    <div id="pwd-feedback-message" class="mb-4" style="display:none;">
                        <div id="pwd-feedback-content" class="rounded border p-3 text-sm"></div>
                    </div>

                    <div class="space-y-6">
                        <!-- Kata Sandi Saat Ini -->
                        <div>
                            <label for="current_password_input" class="mb-2 block text-sm font-medium text-gray-900">Kata Sandi Saat Ini*</label>
                            <input type="password" id="current_password_input" name="current_password"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Masukkan kata sandi lama Anda" required value="123456789">
                            <p id="error-current_password" class="error-message"></p>
                        </div>

                        <!-- Kata Sandi Baru -->
                        <div>
                            <label for="password_input" class="mb-2 block text-sm font-medium text-gray-900">Kata Sandi Baru*</label>
                            <input type="password" id="password_input" name="password"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Minimal 8 karakter" required value="password">
                            <p id="error-password" class="error-message"></p>
                        </div>

                        <!-- Konfirmasi Kata Sandi Baru -->
                        <div>
                            <label for="password_confirmation_input" class="mb-2 block text-sm font-medium text-gray-900">Konfirmasi Kata Sandi Baru*</label>
                            <input type="password" id="password_confirmation_input" name="password_confirmation"
                                class="block w-full rounded-lg border border-gray-300 bg-gray-50 p-2.5 text-sm text-gray-900 focus:border-zinc-500 focus:ring-zinc-500"
                                placeholder="Ulangi kata sandi baru" required value="password">
                            <p id="error-password_confirmation" class="error-message"></p>
                        </div>
                    </div>

                    <button type="submit" id="submitPasswordButton"
                        class="mt-6 inline-flex w-full items-center justify-center rounded-lg bg-zinc-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-4 focus:ring-zinc-300 transition duration-150 ease-in-out">
                        <svg class="-ms-0.5 me-1.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ubah Kata Sandi
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="deleteOrderModal" tabindex="-1" aria-hidden="true"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/30 p-4">
        <div class="w-full max-w-md rounded-lg bg-white shadow">
            <div class="border-b px-5 py-3">
            <h3 class="text-base font-semibold text-gray-900">Batalkan Pesanan</h3>
            </div>
            <div class="px-5 py-4">
            <p class="text-sm text-gray-600">Yakin ingin membatalkan pesanan <span id="cancelOrderLabel" class="font-semibold text-gray-900">#—</span>?</p>
            <p class="mt-2 text-xs text-amber-600">Aksi ini tidak dapat dibatalkan.</p>
            <div id="cancelOrderAlert" class="mt-3 hidden rounded border p-2 text-sm"></div>
            </div>
            <div class="flex items-center justify-end gap-2 px-5 pb-5">
            <button type="button" id="btnCloseCancelModal"
                class="rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Tutup</button>
            <button type="button" id="btnConfirmCancel"
                class="inline-flex items-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">
                <svg class="-ms-1 me-1 hidden h-4 w-4 animate-spin" id="cancelSpinner" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                </svg>
                Batalkan Pesanan
            </button>
            </div>
        </div>
    </div>

        <!-- Modal Detail Pesanan -->
    <div id="orderDetailModal" tabindex="-1" aria-hidden="true"
            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/30 p-4">
        <div class="w-full max-w-2xl rounded-lg bg-white shadow">
            <div class="flex items-center justify-between border-b px-5 py-3">
            <h3 id="orderDetailTitle" class="text-base font-semibold text-gray-900">Detail Pesanan</h3>
            <button id="btnCloseDetailModal" class="rounded p-1 text-gray-500 hover:bg-gray-100" aria-label="Close">✕</button>
            </div>
            <div id="orderDetailBody" class="px-5 py-4">
            <!-- konten detail via AJAX -->
            </div>
        </div>
    </div>
</section>
@endsection
