{{-- Search --}}
<div class="space-y-2">
    <label for="filter-search-{{ $type }}" class="text-sm font-medium text-gray-700">Cari Produk</label>
    <input type="search" id="filter-search-{{ $type }}" wire:model.debounce.500ms="q" placeholder="Cari produk..."
        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block w-full p-2.5" />
</div>

{{-- Categories --}}
<div class="space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-900">Kategori Produk</h3>
        @if (!empty($categories))
            <button wire:click="$set('categories', [])" type="button"
                class="text-xs text-zinc-700 hover:underline">Hapus</button>
        @endif
    </div>
    @if ($categoryOptions->isNotEmpty())
        <div class="max-h-56 overflow-auto pr-1 space-y-2">
            @foreach ($categoryOptions as $opt)
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="categories" value="{{ $opt->slug }}"
                        class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900" />
                    <span class="text-sm text-gray-700">{{ $opt->name }}</span>
                </label>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500">Kategori belum tersedia.</p>
    @endif
</div>

{{-- Price --}}
<div class="space-y-3">
    <h3 class="text-sm font-medium text-gray-900">Harga</h3>
    <div class="flex items-center gap-3">
        <input type="number" wire:model.debounce.500ms="minPrice" min="0" placeholder="Min"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block w-1/2 p-2.5" />
        <span class="text-gray-400">—</span>
        <input type="number" wire:model.debounce.500ms="maxPrice" min="0" placeholder="Max"
            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block w-1/2 p-2.5" />
    </div>
</div>

{{-- Rating --}}
<div class="space-y-3">
    <h3 class="text-sm font-medium text-gray-900">Rating Minimal</h3>
    <div class="space-y-2">
        @foreach ([5, 4, 3, 2, 1] as $r)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" wire:model="minRating" value="{{ $r }}"
                    class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900" />
                <span class="text-sm text-gray-700">
                    {{ str_repeat('★', $r) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $r) }}</span>
                    &nbsp; {{ $r }}+
                </span>
            </label>
        @endforeach
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" wire:model="minRating" value=""
                class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900" />
            <span class="text-sm text-gray-500">Tanpa filter rating</span>
        </label>
    </div>
</div>

{{-- Availability & Promo --}}
<div class="grid gap-2">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" wire:model="inStock" value="1"
            class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900" />
        <span class="text-sm text-gray-700">Stok tersedia</span>
    </label>
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" wire:model="onSale" value="1"
            class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900" />
        <span class="text-sm text-gray-700">Sedang promo</span>
    </label>
</div>

{{-- Features --}}
<div class="space-y-3">
    <h3 class="text-sm font-medium text-gray-900">Brand</h3>
    <div class="flex flex-wrap gap-2">
        @foreach ($featureOptions as $feat)
            <label
                class="cursor-pointer text-xs font-medium me-2 px-3 py-1 rounded-full
                                      {{ in_array($feat, $features) ? 'bg-zinc-900 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }} transition">
                <input type="checkbox" wire:model="features" value="{{ $feat }}" class="sr-only" />
                {{ $feat }}
            </label>
        @endforeach
    </div>
</div>

{{-- Reset --}}
<div class="pt-4 border-t border-gray-100 mt-4 flex flex-col gap-2 py-10">
    <button @click="open = false" wire:click="applyFilters"
        class="w-full bg-zinc-900 hover:bg-zinc-800 text-white rounded-full py-3 font-semibold transition duration-200">
        Tampilkan Hasil
    </button>
    <button wire:click="resetFilters" type="button"
        class="w-full inline-flex items-center justify-center px-5 py-3 text-sm font-semibold text-gray-900
               border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-2 focus:ring-zinc-200 transition duration-200">
        Reset Filter
    </button>
</div>
