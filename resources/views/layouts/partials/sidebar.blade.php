{{--
    Pastikan Anda memiliki tombol di Header yang menggunakan atribut data-drawer-target dan data-drawer-toggle
    Contoh: <button data-drawer-target="mobile-menu-sidebar" data-drawer-toggle="mobile-menu-sidebar" ...>
--}}
<aside id="mobile-menu-sidebar"
    class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white lg:hidden border-e border-zinc-200 dark:bg-zinc-800 dark:border-zinc-700 w-full max-w-xs"
    tabindex="-1" aria-labelledby="mobile-menu-sidebar-label">

    <div class="p-2 space-y-4">
        {{-- HEADER & LOGO --}}
        <div class="flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2" wire:navigate aria-label="Kembali ke beranda">
                <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900">
                    {{-- Ganti dengan SVG Logo Anda yang sebenarnya --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-5 fill-current" viewBox="0 0 40 42" aria-hidden="true">
                        <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"
                            d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                    </svg>
                </div>
                <div class="ms-1 grid flex-1 text-start text-sm">
                    <span class="mb-0.5 truncate leading-tight font-semibold text-zinc-900 dark:text-white">TokoZinc E-commerce</span>
                </div>
            </a>
            
            {{-- TOMBOL TUTUP --}}
            <button type="button" data-drawer-hide="mobile-menu-sidebar" aria-controls="mobile-menu-sidebar"
                class="inline-flex items-center p-2 text-sm text-zinc-500 rounded-lg hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-700">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close menu</span>
            </button>
        </div>

        {{-- Separator --}}
        <div class="h-px bg-zinc-200 dark:bg-zinc-700"></div>

        {{-- Navigasi --}}
        <div class="space-y-3">
            <h5 class="text-xs font-bold uppercase text-zinc-500 dark:text-zinc-400">Navigasi</h5>
            <ul class="space-y-1 font-medium">
                {{-- Item Beranda --}}
                <li>
                    <a href="{{ url('/') }}" class="flex items-center p-2 text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 group">
                        <svg class="w-5 h-5 me-3 text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span>Beranda</span>
                    </a>
                </li>
                {{-- Item Promo --}}
                <li>
                    <a href="#" class="flex items-center p-2 text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 group">
                        <svg class="w-5 h-5 me-3 text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10a1 1 0 011 1v10a1 1 0 01-1 1H7a1 1 0 01-1-1V8a1 1 0 011-1z"></path></svg>
                        <span>Promo</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Separator --}}
        <div class="h-px bg-zinc-200 dark:bg-zinc-700"></div>

        {{-- Kategori --}}
        <div class="space-y-3">
            <h5 class="text-xs font-bold uppercase text-zinc-500 dark:text-zinc-400">Kategori</h5>
            <ul class="space-y-1 font-medium">
                {{-- LOOPING KATEGORI --}}
                @foreach (['Kopi', 'Teh', 'Cokelat', 'Susu', 'Syrup', 'Topping'] as $cat)
                    <li>
                        <a href="#" class="flex items-center p-2 text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700">
                            {{ $cat }}
                        </a>
                    </li>
                @endforeach
                {{-- Item Semua Kategori --}}
                <li>
                    <a href="#" class="flex items-center p-2 text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 group">
                        <svg class="w-5 h-5 me-3 text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        <span>Semua Kategori</span>
                    </a>
                </li>
            </ul>
        </div>

        {{-- Separator --}}
        <div class="h-px bg-zinc-200 dark:bg-zinc-700"></div>

        {{-- Akun & Auth --}}
        @auth
            <div class="space-y-3">
                <h5 class="text-xs font-bold uppercase text-zinc-500 dark:text-zinc-400">Akun</h5>
                <ul class="space-y-1 font-medium">
                    {{-- Item Akun Saya --}}
                    <li>
                        <a href="#" class="flex items-center p-2 text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 group">
                            <svg class="w-5 h-5 me-3 text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14v4m-4-4h8m0 0a12 12 0 01-12 12h16a12 12 0 00-12-12z"></path></svg>
                            <span>Akun Saya</span>
                        </a>
                    </li>
                    {{-- Item Pesanan --}}
                    <li>
                        <a href="#" class="flex items-center p-2 text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 group">
                            <svg class="w-5 h-5 me-3 text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0l-2 2-2-2m2 2V6M5 11l2 2m-2-2l2-2m-2 2V6"></path></svg>
                            <span>Pesanan</span>
                        </a>
                    </li>
                    {{-- Form Logout --}}
                    <li>
                        <form method="POST" action="{{ route('auth.logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center p-2 w-full text-zinc-900 rounded-lg dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-700 group">
                                <svg class="w-5 h-5 me-3 text-zinc-500 group-hover:text-zinc-900 dark:group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"></path></svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @else
            {{-- Tombol Masuk/Daftar --}}
            <div class="grid grid-cols-2 gap-2 mt-4">
                <a href="{{ route('auth.login') }}" class="w-full">
                    <button type="button" class="w-full text-white bg-zinc-900 hover:bg-zinc-700 focus:ring-4 focus:outline-none focus:ring-zinc-300 font-medium rounded-lg text-sm px-4 py-2 text-center dark:bg-zinc-600 dark:hover:bg-zinc-700 dark:focus:ring-zinc-800">
                        Masuk
                    </button>
                </a>
                <a href="{{ route('auth.register') }}" class="w-full">
                    <button type="button" class="w-full text-zinc-900 border border-zinc-300 hover:bg-zinc-100 focus:ring-4 focus:outline-none focus:ring-zinc-300 font-medium rounded-lg text-sm px-4 py-2 text-center dark:border-zinc-500 dark:text-zinc-400 dark:hover:text-white dark:hover:bg-zinc-700 dark:focus:ring-zinc-800">
                        Daftar
                    </button>
                </a>
            </div>
        @endauth
    </div>
</aside>