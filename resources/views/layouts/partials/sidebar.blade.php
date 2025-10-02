{{-- ===== SIDEBAR: MENU (Mobile, kiri) ===== --}}
    <flux:sidebar side="left" stashable sticky
        class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 max-w-[90vw] sm:max-w-xs">
        <div class="p-4 space-y-3">
            <div class="flex items-center justify-between">
                <a href="#" class="flex items-center gap-2" wire:navigate aria-label="Kembali ke beranda">
                    <div
                        class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
                        <div class="size-5 fill-current text-white dark:text-black">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 42" aria-hidden="true">
                                <path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M17.2 5.633 8.6.855 0 5.633v26.51l16.2 9 16.2-9v-8.442l7.6-4.223V9.856l-8.6-4.777-8.6 4.777V18.3l-5.6 3.111V5.633ZM38 18.301l-5.6 3.11v-6.157l5.6-3.11V18.3Zm-1.06-7.856-5.54 3.078-5.54-3.079 5.54-3.078 5.54 3.079ZM24.8 18.3v-6.157l5.6 3.111v6.158L24.8 18.3Zm-1 1.732 5.54 3.078-13.14 7.302-5.54-3.078 13.14-7.3v-.002Zm-16.2 7.89 7.6 4.222V38.3L2 30.966V7.92l5.6 3.111v16.892ZM8.6 9.3 3.06 6.222 8.6 3.143l5.54 3.08L8.6 9.3Zm21.8 15.51-13.2 7.334V38.3l13.2-7.334v-6.156ZM9.6 11.034l5.6-3.11v14.6l-5.6 3.11v-14.6Z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-1 grid flex-1 text-start text-sm">
                        <span class="mb-0.5 truncate leading-tight font-semibold">Laravel Starter Kit</span>
                    </div>
                </a>
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" aria-label="Tutup menu" />
            </div>

            <flux:separator />

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Navigasi')">
                    <flux:navlist.item icon="home" href="#" :current="request()->routeIs('dashboard')"
                        wire:navigate>
                        {{ __('Beranda') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="tag" href="#">{{ __('Promo') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Kategori')">
                    @foreach (['Kopi', 'Teh', 'Cokelat', 'Susu', 'Syrup', 'Topping'] as $cat)
                        <flux:navlist.item href="#">{{ $cat }}</flux:navlist.item>
                    @endforeach
                    <flux:navlist.item icon="chevron-right" href="#">{{ __('Semua Kategori') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:separator />

            @if (auth()->check())
                <flux:navlist variant="outline">
                    <flux:navlist.item icon="user" href="#">{{ __('Akun Saya') }}</flux:navlist.item>
                    <flux:navlist.item icon="shopping-bag" href="#">{{ __('Pesanan') }}</flux:navlist.item>
                    <form method="POST" action="#">
                        @csrf
                        <flux:navlist.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                            {{ __('Keluar') }}
                        </flux:navlist.item>
                    </form>
                </flux:navlist>
            @else
                <div class="grid grid-cols-2 gap-2">
                    <a href="#">
                        <flux:button class="w-full" size="sm">{{ __('Masuk') }}</flux:button>
                    </a>
                    <a href="#">
                        <flux:button variant="outline" class="w-full" size="sm">{{ __('Daftar') }}
                        </flux:button>
                    </a>
                </div>
            @endif
        </div>
    </flux:sidebar>
