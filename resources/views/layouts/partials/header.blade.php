{{-- ===== HEADER E-COMMERCE (Flux UI) ===== --}}
<flux:header container
    class="sticky top-0 z-50 bg-zinc-50/80 backdrop-blur supports-[backdrop-filter]:bg-zinc-50/60 dark:bg-zinc-900/80 border-b border-zinc-200 dark:border-zinc-700">

    {{-- LEFT cluster --}}
    <div class="flex items-center gap-2 shrink-0">
        {{-- Toggle menu (mobile) --}}
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" label="Menu" aria-label="Buka menu" />

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="ms-1 me-2 flex items-center gap-2 lg:ms-0" wire:navigate
            aria-label="Kembali ke beranda">
            <div
                class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                <div class="size-5 fill-current text-white dark:text-black">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" aria-hidden="true">
                        <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"
                            d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                    </svg>
                </div>
            </div>
            <div class="ms-1 grid flex-1 text-start">
                <span class="truncate leading-tight font-semibold hidden sm:inline text-sm sm:text-base">
                    {{ config('app.name') }}
                </span>

                <span class="sr-only">Home</span>
            </div>
        </a>
    </div>

    {{-- NAV kiri (desktop) --}}
    <div class="hidden lg:flex items-center gap-1 shrink-0">
        <flux:navbar class="items-center -mb-px">
            <flux:navbar.item icon="home" href="#" :current="request()->routeIs('dashboard')" wire:navigate>
                Beranda
            </flux:navbar.item>

            {{-- Kategori: mega dropdown (tidak mendorong layout) --}}
            <flux:dropdown>
                <flux:navbar.item icon="squares-2x2" icon:trailing="chevron-down">
                    Kategori
                </flux:navbar.item>
                <flux:navmenu class="min-w-[560px]">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2 p-2">
                        @php
                            $category = \App\Models\Category::all();
                        @endphp
                        @foreach ($category as $cat)
                            <flux:navmenu.item href="{{ route('category', [
                            'category'=>$cat['slug']
                            ]) }}" class="flex items-center gap-3">
                                <flux:icon name="shopping-bag" class="size-5" />
                                <span>{{ $cat['name'] }}</span>
                            </flux:navmenu.item>
                        @endforeach
                    </div>
                    <flux:separator class="my-1" />
                    <flux:navmenu.item href="#" icon="chevron-right">
                        Lihat Semua Kategori
                    </flux:navmenu.item>
                </flux:navmenu>
            </flux:dropdown>
        </flux:navbar>
    </div>

    {{-- SEARCH (tablet & desktop) --}}
    <div class="hidden md:block flex-1 min-w-0 px-2">
        <div class="max-w-lg lg:max-w-xl ml-auto">
            @livewire('layouts.search-box')
        </div>
    </div>

    {{-- Search toggle (mobile) --}}
    <div class="md:hidden me-1 shrink-0">
        <flux:sidebar.toggle inset="right" icon="magnifying-glass" label="Cari produk" aria-label="Buka pencarian" />
    </div>

    {{-- RIGHT cluster --}}
    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
        <flux:navbar class="items-center">
            <flux:navbar.item icon="heart" href="#" :label="__('Wishlist')" />
            @livewire('layouts.cart-indicator') {{-- badge qty di komponen ini --}}
            <flux:navbar.item class="hidden lg:flex" icon="information-circle" href="#" :label="__('Bantuan')" />
        </flux:navbar>

        {{-- ACCOUNT dropdown / Auth --}}
        @php
            $auth = Auth::guard('customer');
            $user = $auth->user() ?? null;
        @endphp
        @if ($auth->check())
            <flux:dropdown position="top" align="end" class="ms-1">
                <flux:profile class="cursor-pointer" :name="$user->name" :email="$user->email"
                    avatar="{{ $user->avatar_url ?? 'https://fluxui.dev/img/demo/user.png' }}" />
                <flux:menu>
                    <div class="px-2 py-1.5 text-sm">
                        <div class="font-semibold truncate">{{ $user->name }}</div>
                        <div class="text-xs truncate text-zinc-500 dark:text-zinc-400">{{ $user->email }}
                        </div>
                    </div>
                    <flux:menu.separator />
                    <flux:menu.item href="{{ route('auth.profile') }}" icon="cog" wire:navigate>
                        Pengaturan Akun
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('product.transaction') }}" icon="shopping-bag">
                        Pesanan Saya
                    </flux:menu.item>
                    <flux:menu.item href="{{ route('auth.profile') }}" icon="map">
                        Alamat Pengiriman
                    </flux:menu.item>
                    <flux:menu.separator />
                    <form method="POST" action="#" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full">
                            Keluar
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        @else
            <div class="flex items-center gap-1 sm:gap-2 ms-1">
                <flux:button href="{{ route('auth.login') }}" size="sm" variant="ghost" class="px-2 sm:px-3">
                    Masuk
                </flux:button>
                <flux:button href="{{ route('auth.profile') }}" size="sm" class="px-3 sm:px-4">
                    Daftar
                </flux:button>
            </div>
        @endif
    </div>
</flux:header>
