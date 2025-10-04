<header class="sticky top-0 z-50 bg-white border-b border-zinc-200">
    <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl py-3 px-4 sm:px-6 lg:px-8">

        {{-- KIRI: BRAND & NAVIGASI (Desktop) --}}
        <div class="flex items-center space-x-4 rtl:space-x-reverse shrink-0">
            <a href="{{ url('/') }}" class="flex items-center space-x-2 rtl:space-x-reverse">
                <img src="{{ asset('images/logo-puranura-id.png') }}" class="h-10" alt="{{ config('app.name') }}" />
                <span class="self-center text-xl font-bold whitespace-nowrap text-zinc-800">{{ config('app.name') }}</span>
            </a>

            {{-- Dropdown Kategori Desktop --}}
            {{-- DITAMBAHKAN: data-dropdown-placement --}}
            <button id="kategori-dropdown-button" 
                data-dropdown-toggle="kategori-dropdown-menu" 
                data-dropdown-placement="bottom-start" 
                class="hidden lg:flex items-center text-sm font-semibold px-3 py-2 rounded-lg text-zinc-600 hover:bg-zinc-100 transition duration-150 border border-transparent hover:border-zinc-300">
                Kategori
                <svg class="w-3 h-3 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                </svg>
            </button>
        </div>

        {{-- TENGAH: SEARCH BOX (Tablet & Desktop) --}}
        <div class="hidden md:block flex-1 min-w-0 mx-4">
            <div class="max-w-3xl mx-auto">
                <form action="#" method="GET" class="relative flex items-stretch">
                    <input type="text" name="q" id="search-navbar" class="block w-full p-2.5 text-sm text-zinc-900 border border-zinc-300 rounded-s-lg bg-zinc-50 focus:ring-zinc-900 focus:border-zinc-900 placeholder:text-zinc-500" placeholder="Cari di TokoZinc..." required>
                    <button type="submit" class="p-2.5 text-sm font-medium text-white bg-zinc-900 rounded-e-lg border border-zinc-900 hover:bg-zinc-600 focus:ring-4 focus:outline-none focus:ring-zinc-300">
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
            <button data-collapse-toggle="search-mobile-overlay" type="button" class="md:hidden p-2 text-zinc-600 rounded-lg hover:bg-zinc-100" aria-controls="search-mobile-overlay" aria-expanded="false" aria-label="Buka Pencarian">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
            
            {{-- Wishlist --}}
            <a href="#" class="p-2 text-zinc-600 rounded-lg hover:bg-zinc-100" aria-label="Wishlist">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </a>
            
            {{-- KERANJANG (Cart) --}}
            <a href="{{ route('cart.index') }}" class="p-2 text-zinc-600 rounded-lg hover:bg-zinc-100 relative" aria-label="Keranjang Belanja">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l3.5 7.5M16 16l-3 3-5-5M6 14a2 2 0 100 4 2 2 0 000-4zM16 14a2 2 0 100 4 2 2 0 000-4z"></path>
                </svg>
                @php
                    $cartCount = 0;
                    if (Auth::guard('customer')->check()) {
                        $user = Auth::guard('customer')->user();
                        $cartCount = isset($user->carts) ? $user->carts->count() : 0;
                    }
                @endphp
                @if (isset($cartCount) && $cartCount > 0)
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center h-4 w-4 text-xs font-bold text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full leading-none">{{ $cartCount }}</span>
                @endif
            </a>
            
            {{-- Divider --}}
            <div class="hidden md:block h-6 w-px bg-zinc-300 mx-2"></div>

            {{-- Login/Daftar & Profile (Desktop) --}}
            @if(Auth::guard('customer')->guest())
                {{-- Tampilan saat GUEST (Belum Login) --}}
                <a href="{{ route('auth.login') }}" class="hidden md:block px-4 py-2 text-sm font-medium text-white bg-zinc-900 rounded-lg hover:bg-zinc-600 focus:ring-4 focus:ring-zinc-300" aria-label="Masuk">
                    Masuk
                </a>
                <a href="{{ route('auth.register') }}" class="hidden md:block px-4 py-2 text-sm font-medium text-zinc-900 border border-zinc-900 rounded-lg hover:bg-zinc-50" aria-label="Daftar">
                    Daftar
                </a>
            @else
                {{-- Tampilan saat USER sudah Login: Menggunakan Dropdown Profil Flowbite --}}
                <div class="relative hidden md:block">
                    {{-- Tombol Pemicu Dropdown Profil --}}
                    {{-- Ganti rute 'auth.profile' di bawah dengan route ke gambar profil pengguna --}}
                    <button id="user-menu-button" type="button" 
                        data-dropdown-toggle="user-dropdown" 
                        data-dropdown-placement="bottom-end" 
                        class="flex items-center space-x-2 text-sm rounded-full focus:ring-4 focus:ring-zinc-300" 
                        aria-expanded="false" aria-label="Menu Pengguna">
                        
                        <img class="w-8 h-8 rounded-full object-cover" 
                            src="https://placehold.co/32x32/1a1a1a/ffffff?text=U" 
                            alt="Foto Profil">
                        
                        {{-- Nama Pengguna (Opsional) --}}
                        <span class="font-medium text-zinc-900 hidden lg:inline">{{ Auth::guard('customer')->user()->name ?? 'Pengguna' }}</span>
                    </button>
                    
                    {{-- Konten Dropdown Profil --}}
                    <div id="user-dropdown" 
                        class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow">
                        
                        <div class="px-4 py-3">
                            {{-- Data Pengguna --}}
                            <span class="block text-sm text-gray-900">{{ Auth::guard('customer')->user()->name ?? 'Nama Pengguna' }}</span>
                            <span class="block text-sm font-medium text-gray-500 truncate">{{ Auth::guard('customer')->user()->email ?? 'email@example.com' }}</span>
                        </div>
                        
                        <ul class="py-2" aria-labelledby="user-menu-button">
                            <li>
                                <a href="{{ route('auth.cart') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Profil
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('auth.order') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Pesanan Saya
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('auth.setting') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Pengaturan Akun
                                </a>
                            </li>
                            <li>
                                {{-- Tautan Logout --}}
                                <a href="{{ route('auth.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Keluar
                                </a>
                                
                                {{-- Form Logout Tersembunyi untuk keamanan Laravel --}}
                                <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" class="hidden">
                                    @csrf 
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            @endguest

            {{-- Tombol Pemicu Drawer (Sidebar Mobile) --}}
            <button data-drawer-target="mobile-menu-sidebar" data-drawer-toggle="mobile-menu-sidebar" type="button" 
                class="inline-flex items-center p-2 w-10 h-10 justify-center text-sm text-zinc-600 rounded-lg lg:hidden hover:bg-zinc-100" 
                aria-controls="mobile-menu-sidebar" aria-expanded="false" aria-label="Buka Menu">
                <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 17 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h15M1 7h15M1 13h15"/>
                </svg>
            </button>
        </div>
    </div>
    
    {{-- SEARCH OVERLAY (Mobile) --}}
    <div id="search-mobile-overlay" class="hidden md:hidden p-3 border-y border-zinc-200 bg-zinc-50">
        <form action="#" method="GET" class="relative flex items-stretch">
            <input type="text" name="q" class="block w-full p-2.5 text-sm text-zinc-900 border border-zinc-300 rounded-s-lg bg-white focus:ring-zinc-900 focus:border-zinc-900" placeholder="Cari di TokoZinc..." required>
            <button type="submit" class="p-2.5 text-sm font-medium text-white bg-zinc-900 rounded-e-lg border border-zinc-900 hover:bg-zinc-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <span class="sr-only">Search</span>
            </button>
        </form>
    </div>

    {{-- MENU MOBILE/SIDEBAR (DRAWER) --}}
    <div id="mobile-menu-sidebar" 
        class="fixed top-0 left-0 z-40 h-screen p-4 overflow-y-auto transition-transform -translate-x-full bg-white w-64 lg:hidden" 
        tabindex="-1" aria-labelledby="drawer-label">
        
        <h5 id="drawer-label" class="inline-flex items-center mb-4 text-base font-semibold text-gray-500 uppercase">
            Menu Utama
        </h5>
        
        <button type="button" data-drawer-hide="mobile-menu-sidebar" aria-controls="mobile-menu-sidebar" 
            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 absolute top-2.5 end-2.5 inline-flex items-center justify-center">
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
            <span class="sr-only">Close menu</span>
        </button>

        {{-- Konten Menu Sidebar --}}
        <ul class="space-y-2 font-medium">
            <li><a href="{{ url('/') }}" class="flex items-center p-2 text-zinc-900 rounded-lg hover:bg-zinc-100">Beranda</a></li>
            
            {{-- DUMMY KATEGORI JIKA TIDAK ADA DATA ASLI --}}
            @php
                // Ambil kategori top-level + subkategori dari model Category (cache 10 menit)
                $categories = \Illuminate\Support\Facades\Cache::remember('menu:categories:top-with-children', 600, function () {
                    // Jika relasi subkategori kamu bernama 'children' (umum untuk self-relation)
                    return \App\Models\Category::query()
                        ->select(['id','name','slug','description','parent_id','is_active'])
                        ->where('is_active', true)               // hapus jika tidak pakai flag aktif
                        ->whereNull('parent_id')                 // hanya kategori level 1
                        ->orderBy('name')
                        ->with([
                            // Ubah 'children' ke nama relasi subkategori kamu jika berbeda (mis: 'subCategories')
                            'children' => function ($q) {
                                $q->select(['id','parent_id','name','slug','is_active'])
                                ->where('is_active', true)
                                ->orderBy('name');
                            }
                        ])
                        ->get()
                        ->map(function ($cat) {
                            return (object) [
                                'id'            => $cat->id,
                                'name'          => $cat->name,
                                'slug'          => $cat->slug,
                                'description'   => $cat->description,
                                // bentukkan seperti contoh sebelumnya: collection of objects { slug, name }
                                'subCategories' => $cat->children->map(fn ($child) => (object) [
                                    'slug' => $child->slug,
                                    'name' => $child->name,
                                ]),
                            ];
                        });
                });
            @endphp

            @if(isset($categories))
                @foreach ($categories as $category)
                    <li>
                        {{-- Tombol Kategori Utama (Trigger Sub-Kategori) --}}
                        <button type="button" data-collapse-toggle="mobile-kategori-list-{{ $category->slug }}" class="flex items-center justify-between w-full p-2 text-zinc-900 rounded-lg hover:bg-zinc-100" aria-expanded="false" aria-controls="mobile-kategori-list-{{ $category->slug }}">
                            <span class="flex-1 ms-3 text-left whitespace-nowrap">{{ $category->name }}</span>
                            @if ($category->subCategories->count())
                                <svg class="w-3 h-3 ms-2.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                </svg>
                            @endif
                        </button>
                        
                        {{-- Daftar Sub-Kategori (Konten yang di-toggle) --}}
                        @if ($category->subCategories->count())
                            <ul id="mobile-kategori-list-{{ $category->id }}" class="hidden py-2 space-y-2">
                                @foreach ($category->subCategories as $subCategory)
                                    <li>
                                        <a href="{{ route('category', $subCategory->slug) }}" class="flex items-center w-full p-2 text-zinc-900 transition duration-75 rounded-lg pl-11 group hover:bg-zinc-100">{{ $subCategory->name }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            @endif
            
            <li><a href="#" class="flex items-center p-2 text-zinc-900 rounded-lg hover:bg-zinc-100">Bantuan</a></li>
            <li class="pt-4 mt-4 border-t border-zinc-200">
                <a href="{{ route('auth.login') }}" class="block text-center py-2 px-3 text-white bg-zinc-900 rounded-lg hover:bg-zinc-600">Masuk / Daftar</a>
            </li>
        </ul>
    </div>

    {{-- KATEGORI DROPDOWN MENU (Desktop) --}}
    <div id="kategori-dropdown-menu" 
         class="z-30 hidden w-72 border border-zinc-200 shadow-2xl bg-white rounded-xl absolute start-4 mt-2">
        {{-- KONTEN DROPDOWN DIKEMBALIKAN --}}
        <div class="p-2 text-zinc-900">

            <div class="px-3 py-2 text-sm font-semibold text-zinc-700 border-b border-zinc-100 mb-1">
                Jelajahi Kategori
            </div>

            <ul class="space-y-1">
                {{-- KATEGORI DROPDOWN DESKTOP: LOOPING --}}
                @if(isset($categories))
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('category', $category->slug) }}" class="flex items-center p-3 rounded-lg hover:bg-zinc-100 transition duration-150 group">
                                {{-- Icon Kategori --}}
                                <svg class="w-5 h-5 me-3 text-zinc-500 group-hover:text-zinc-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2m-3-12v12m0 0l4-4m-4 4l-4-4M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <div>
                                    <div class="font-medium text-sm">{{ $category->name }}</div>
                                    @if(isset($category->description))
                                        <span class="text-xs text-zinc-500">{{ $category->description }}</span>
                                    @endif
                                </div>
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>

            <a href="{{ route('category') }}" class="block p-3 mt-2 text-center text-sm font-semibold text-zinc-900 rounded-lg hover:bg-zinc-100 transition duration-150">
                Lihat Semua Kategori
            </a>
        </div>
    </div>
</header>