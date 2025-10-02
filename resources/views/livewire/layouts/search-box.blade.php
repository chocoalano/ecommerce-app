<div wire:ignore.self>
    <div class="relative">
        <input
            wire:model.debounce.250ms="q"
            type="search"
            class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 px-4 py-2 pe-10"
            placeholder="Cari produk…"
            aria-label="Cari produk"
        />
        <button class="absolute right-2 top-1/2 -translate-y-1/2" aria-label="Cari">
            <flux:icon name="magnifying-glass" class="size-5" />
        </button>
    </div>

    @if(!empty($results))
        <div class="mt-2 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow">
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach($results as $p)
                    <li>
                        <a href="#" class="flex items-center justify-between px-4 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-900">
                            <span class="truncate">{{ $p->name }}</span>
                            <span class="ms-3 whitespace-nowrap font-semibold">Rp{{ number_format($p->price,0,',','.') }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
