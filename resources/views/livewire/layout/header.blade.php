@php
    // Definisikan warna Flowbite Primary untuk konsistensi
    $primary_color = 'bg-zinc-900';
    $primary_hover = 'hover:bg-zinc-700';
    $primary_focus = 'focus:ring-zinc-300';
    $text_primary = 'text-zinc-600';
@endphp
<div class="w-full">

    {{-- BAGIAN 1: HEADER NAVIGASI UTAMA (Sticky) --}}
    <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl py-3 px-4 sm:px-6 lg:px-8">

            {{-- KIRI: BRAND & NAVIGASI (Desktop) --}}
            <div class="flex items-center space-x-4 rtl:space-x-reverse shrink-0">
                {{-- Logo/Brand --}}
                <a href="{{ url('/') }}" class="flex items-center space-x-2 rtl:space-x-reverse" wire:navigate aria-label="Kembali ke beranda">
                    <img src="{{ asset('images/logo-puranura-id.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                    {{-- <span class="self-center text-xl font-bold whitespace-nowrap text-gray-900">{{ config('app.name') }}</span> --}}
                </a>

                {{-- Dropdown Kategori Desktop --}}
                <div x-data="{ open: false }" class="relative hidden lg:block">
                    <button @click="open = !open" @click.away="open = false"
                        class="flex items-center text-sm font-semibold px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-100 transition duration-150 border border-transparent hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        Kategori
                        <svg class="w-3 h-3 ms-2" :class="{'rotate-180': open}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                        </svg>
                    </button>

                    {{-- Kategori Dropdown Menu --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 top-full mt-2 w-72 border border-gray-200 shadow-xl bg-white rounded-xl z-30">
                        <div class="p-2 text-gray-900">
                            <div class="px-3 py-2 text-sm font-semibold text-gray-700 border-b border-gray-100 mb-1">
                                Jelajahi Kategori
                            </div>

                            <ul class="space-y-1">
                                @foreach ($categories as $category)
                                    <li>
                                        <div x-data="{ open: false }" class="group">
                                            {{-- Main Category --}}
                                            <a href="{{ route('products.index', $category->slug) }}"
                                               @if(isset($category->subCategories) && $category->subCategories->count())
                                                   @mouseenter="open = true" @mouseleave="open = false"
                                               @endif
                                               class="flex items-center justify-between p-3 rounded-lg hover:bg-gray-100 transition duration-150">
                                                <div class="flex items-center">
                                                    <img src="{{ asset('storage/images/' . ($category->image ?? 'default.png')) }}" alt="{{ $category->name }}" class="w-10 h-10 me-3 object-contain" />
                                                    <div>
                                                        <div class="font-medium text-sm">{{ $category->name }}</div>
                                                        @if(isset($category->description))
                                                            <span class="text-xs text-gray-500">{{ $category->description }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if(isset($category->subCategories) && $category->subCategories->count())
                                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                @endif
                                            </a>

                                            {{-- Subcategories Dropdown --}}
                                            @if(isset($category->subCategories) && $category->subCategories->count())
                                                <div x-show="open"
                                                     @mouseenter="open = true" @mouseleave="open = false"
                                                     x-transition:enter="transition ease-out duration-200"
                                                     x-transition:enter-start="opacity-0 translate-x-4"
                                                     x-transition:enter-end="opacity-100 translate-x-0"
                                                     x-transition:leave="transition ease-in duration-150"
                                                     x-transition:leave-start="opacity-100 translate-x-0"
                                                     x-transition:leave-end="opacity-0 translate-x-4"
                                                     class="absolute left-full top-0 ml-2 w-56 bg-white border border-gray-200 shadow-lg rounded-lg z-40">
                                                    <div class="p-2">
                                                        <div class="px-3 py-2 text-xs font-semibold text-gray-700 border-b border-gray-100 mb-1">
                                                            {{ $category->name }}
                                                        </div>
                                                        <ul class="space-y-1">
                                                            @foreach($category->subCategories as $subCategory)
                                                                <li>
                                                                    <a href="{{ route('products.index', $subCategory->slug) }}"
                                                                       class="flex items-center p-2 rounded-md hover:bg-gray-100 transition duration-150">
                                                                        <div>
                                                                            <div class="font-medium text-sm text-gray-900">{{ $subCategory->name }}</div>
                                                                            @if(isset($subCategory->description))
                                                                                <span class="text-xs text-gray-500">{{ $subCategory->description }}</span>
                                                                            @endif
                                                                        </div>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <a href="{{ route('products.index') }}" class="block p-3 mt-2 text-center text-sm font-semibold text-gray-900 rounded-lg hover:bg-gray-100 transition duration-150 border-t border-gray-100">
                                Lihat Semua Kategori
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TENGAH: SEARCH BOX (Tablet & Desktop) --}}
            <div class="hidden md:block flex-1 min-w-0 mx-4">
                <div class="max-w-3xl mx-auto">
                    <form wire:submit.prevent="search" class="relative flex items-stretch">
                        <input type="text" wire:model.defer="searchQuery" class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-s-lg bg-gray-50 focus:ring-zinc-500 focus:border-zinc-500 placeholder:text-gray-500" placeholder="Cari di {{ config('app.name') }}..." required>
                        <button type="submit" class="p-2.5 text-sm font-medium text-white {{ $primary_color }} rounded-e-lg border border-zinc-600 {{ $primary_hover }} focus:ring-4 focus:outline-none {{ $primary_focus }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span class="sr-only">Search</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- KANAN: Ikon Aksi & Menu Toggle --}}
            <div class="flex items-center space-x-1.5 rtl:space-x-reverse shrink-0">

                {{-- Search Toggle (Mobile) --}}
                <button wire:click="toggleMobileSearch" type="button" class="md:hidden p-2 text-gray-600 rounded-lg hover:bg-gray-100" aria-label="Buka Pencarian">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </button>

                {{-- Wishlist --}}
                <a href="#" class="p-2 text-gray-600 rounded-lg hover:bg-gray-100" aria-label="Wishlist">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </a>

                {{-- KERANJANG (Cart) --}}
                <a href="{{ route('cart.index') }}" class="p-2 text-gray-600 rounded-lg hover:bg-gray-100 relative" aria-label="Keranjang Belanja">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l3.5 7.5M16 16l-3 3-5-5M6 14a2 2 0 100 4 2 2 0 000-4zM16 14a2 2 0 100 4 2 2 0 000-4z"></path>
                    </svg>
                    @livewire('components.cart-indicator')
                </a>

                {{-- Divider --}}
                <div class="hidden md:block h-6 w-px bg-gray-300 mx-2"></div>

                {{-- Login/Daftar & Profile (Desktop) --}}
                @if(!isset($isLoggedIn) || !$isLoggedIn)
                    {{-- Tampilan saat GUEST (Belum Login) --}}
                    <a href="{{ route('auth.login') }}" class="hidden md:block px-4 py-2 text-sm font-medium text-white {{ $primary_color }} rounded-lg {{ $primary_hover }} focus:ring-4 {{ $primary_focus }}" aria-label="Masuk">
                        Masuk
                    </a>
                    <a href="{{ route('auth.register') }}" class="hidden md:block px-4 py-2 text-sm font-medium text-gray-900 border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:ring-gray-200" aria-label="Daftar">
                        Daftar
                    </a>
                @else
                    {{-- Tampilan saat USER sudah Login --}}
                    <div x-data="{ open: false }" class="relative hidden md:block">
                        <button @click="open = !open" @click.away="open = false" type="button"
                            class="flex items-center space-x-2 text-sm rounded-full focus:ring-4 focus:ring-zinc-300 p-0.5"
                            aria-expanded="false" aria-label="Menu Pengguna">

                            @php
                                $initial = (isset($user) && $user->name) ? strtoupper(mb_substr($user->name, 0, 1)) : 'U';
                            @endphp
                            <img class="w-9 h-9 rounded-full object-cover border border-gray-200"
                                src="https://placehold.co/36x36/1D4ED8/ffffff?text={{ $initial }}"
                                alt="{{ $user->name ?? 'Pengguna' }}">

                            <span class="font-medium text-gray-900 hidden lg:inline">{{ $user->name ?? 'Pengguna' }}</span>
                            <svg class="w-3 h-3 text-gray-500 hidden lg:inline" :class="{'rotate-180': open}" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                            </svg>
                        </button>

                        {{-- User Dropdown Menu --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-full mt-2 w-48 bg-white divide-y divide-gray-100 rounded-lg shadow-xl z-50">
                            <div class="px-4 py-3">
                                <span class="block text-sm text-gray-900">{{ $user->name ?? 'Nama Pengguna' }}</span>
                                <span class="block text-xs font-medium text-gray-500 truncate">{{ $user->email ?? 'email@example.com' }}</span>
                            </div>

                            <ul class="py-2">
                                <li>
                                    <a href="{{ route('auth.profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Profil
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('auth.orders') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Pesanan Saya
                                    </a>
                                </li>
                                <li>
                                    <button wire:click="logout" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700">
                                        Keluar
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Tombol Pemicu Mobile Menu (Gunakan atribut Flowbite data-drawer-target) --}}
                <button type="button" data-drawer-target="mobile-menu-sidebar" data-drawer-show="mobile-menu-sidebar" aria-controls="mobile-menu-sidebar"
                    class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-gray-600 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200"
                    aria-label="Buka Menu">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- SEARCH OVERLAY (Mobile) --}}
        @if(isset($showMobileSearch) && $showMobileSearch)
            <div class="md:hidden p-3 border-y border-gray-200 bg-gray-50">
                <form wire:submit.prevent="search" class="relative flex items-stretch">
                    <input type="text" wire:model.defer="searchQuery" class="block w-full p-2.5 text-sm text-gray-900 border border-gray-300 rounded-s-lg bg-white focus:ring-zinc-500 focus:border-zinc-500" placeholder="Cari di {{ config('app.name') }}..." required>
                    <button type="submit" class="p-2.5 text-sm font-medium text-white {{ $primary_color }} rounded-e-lg border border-zinc-600 {{ $primary_hover }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <span class="sr-only">Search</span>
                    </button>
                </form>
            </div>
        @endif
    </header>

    {{--
        BAGIAN 2: MOBILE MENU SIDEBAR (Flowbite Drawer)
        Pastikan Livewire tidak mengontrol kondisi tampilan (hanya Flowbite/JS) untuk Drawer.
        Hapus kondisi @if($showMobileMenu) dan @if(isset($showMobileMenu))
    --}}
    <aside id="mobile-menu-sidebar"
        class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white lg:hidden border-e border-gray-200 w-full max-w-xs"
        tabindex="-1" aria-labelledby="mobile-menu-sidebar-label"
        data-drawer-backdrop="true" data-drawer-placement="left"
        aria-modal="true" role="dialog">

        <div class="p-2 space-y-4">
            {{-- HEADER & LOGO --}}
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2" wire:navigate aria-label="Kembali ke beranda">
                    <div class="flex aspect-square size-8 items-center justify-center rounded-md {{ $primary_color }} text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 fill-current" viewBox="0 0 40 42" aria-hidden="true">
                            <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"
                                d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                        </svg>
                    </div>
                    <div class="ms-1 grid flex-1 text-start text-sm">
                        <span class="mb-0.5 truncate leading-tight font-semibold text-gray-900">Toko E-commerce</span>
                    </div>
                </a>

                {{-- TOMBOL TUTUP Flowbite --}}
                <button type="button" data-drawer-hide="mobile-menu-sidebar" aria-controls="mobile-menu-sidebar"
                    class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg hover:bg-gray-100 hover:text-gray-900">
                    <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close menu</span>
                </button>
            </div>

            {{-- Separator --}}
            <div class="h-px bg-gray-200"></div>

            {{-- Navigasi --}}
            <div class="space-y-3">
                <h5 class="text-xs font-bold uppercase text-gray-500">Navigasi</h5>
                <ul class="space-y-1 font-medium">
                    <li>
                        <a href="{{ url('/') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                            <svg class="w-5 h-5 me-3 text-gray-500 group-hover:text-gray-900"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                            <svg class="w-5 h-5 me-3 text-gray-500 group-hover:text-gray-900"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h10a1 1 0 011 1v10a1 1 0 01-1 1H7a1 1 0 01-1-1V8a1 1 0 011-1z"></path>
                            </svg>
                            <span>Promo</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Separator --}}
            <div class="h-px bg-gray-200"></div>

            {{-- Kategori --}}
            <div class="space-y-3">
                <h5 class="text-xs font-bold uppercase text-gray-500">Kategori</h5>
                <ul class="space-y-1 font-medium">
                    @foreach ($categories as $category)
                        <li>
                            <div x-data="{ open: false }">
                                <button @click="open = !open" type="button" class="flex items-center justify-between w-full p-2 text-gray-900 rounded-lg hover:bg-gray-100">
                                    <span class="flex-1 text-left">{{ $category->name }}</span>
                                    @if (isset($category->subCategories) && $category->subCategories->count())
                                        <svg class="w-3 h-3 text-gray-500 transition-transform" :class="{'rotate-180': open}" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                        </svg>
                                    @endif
                                </button>

                                @if (isset($category->subCategories) && $category->subCategories->count())
                                    <ul x-show="open" x-transition class="py-2 space-y-1">
                                        @foreach ($category->subCategories as $subCategory)
                                            <li>
                                                <a href="{{ route('products.index', $subCategory->slug) }}" class="flex items-center w-full p-2 text-sm text-gray-700 transition duration-75 rounded-lg pl-8 hover:bg-gray-100">{{ $subCategory->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </li>
                    @endforeach
                    {{-- Item Semua Kategori --}}
                    <li>
                        <a href="{{ route('products.index') }}"
                            class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group border-t border-gray-100 mt-2">
                            <svg class="w-5 h-5 me-3 text-gray-500 group-hover:text-gray-900"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                            <span>Lihat Semua Kategori</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Separator --}}
            <div class="h-px bg-gray-200"></div>

            {{-- Akun & Auth --}}
            @if(isset($isLoggedIn) && $isLoggedIn)
                <div class="space-y-3">
                    <h5 class="text-xs font-bold uppercase text-gray-500">Akun</h5>
                    <ul class="space-y-1 font-medium">
                        <li>
                            <a href="{{ route('auth.profile') }}"
                                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <svg class="w-5 h-5 me-3 text-gray-500 group-hover:text-gray-900"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14v4m-4-4h8m0 0a12 12 0 01-12 12h16a12 12 0 00-12-12z">
                                    </path>
                                </svg>
                                <span>Akun Saya</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('auth.orders') }}"
                                class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 group">
                                <svg class="w-5 h-5 me-3 text-gray-500 group-hover:text-gray-900"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 11H5m14 0l-2 2-2-2m2 2V6M5 11l2 2m-2-2l2-2m-2 2V6"></path>
                                </svg>
                                <span>Pesanan</span>
                            </a>
                        </li>
                        <li>
                            <button wire:click="logout" type="button"
                                class="flex items-center p-2 w-full text-left text-red-600 rounded-lg hover:bg-red-50 group">
                                <svg class="w-5 h-5 me-3 text-red-500 group-hover:text-red-700"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7"></path>
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </li>
                    </ul>
                </div>
            @else
                {{-- Tombol Masuk/Daftar --}}
                <div class="grid grid-cols-2 gap-2 mt-4">
                    <a href="{{ route('auth.login') }}" class="w-full">
                        <button type="button"
                            class="w-full text-white {{ $primary_color }} {{ $primary_hover }} focus:ring-4 focus:outline-none {{ $primary_focus }} font-medium rounded-lg text-sm px-4 py-2 text-center">
                            Masuk
                        </button>
                    </a>
                    <a href="{{ route('auth.register') }}" class="w-full">
                        <button type="button"
                            class="w-full text-gray-900 border border-gray-300 hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 font-medium rounded-lg text-sm px-4 py-2 text-center">
                            Daftar
                        </button>
                    </a>
                </div>
            @endif
        </div>
    </aside>

    {{-- Drawer backdrop --}}
    <div id="drawer-backdrop" class="bg-gray-900/50 dark:bg-gray-900/80 fixed inset-0 z-30 hidden"></div>

</div> {{-- Penutup DIV root Livewire --}}
