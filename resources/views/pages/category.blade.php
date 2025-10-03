@extends('layouts.app')

@section('content')
@php
    // -------- Params dari URL --------
    $q         = trim((string) request('q', ''));
    $minPrice  = request('min_price');
    $maxPrice  = request('max_price');
    $minRating = request('min_rating'); // 1..5
    $inStock   = (bool) request('in_stock');
    $onSale    = (bool) request('on_sale');
    $features  = (array) request('features', []);        // JSON features di products
    $categories= (array) request('category', []);        // slug[] / id[]
    $sort      = request('sort', 'popular');             // popular|new|price_asc|price_desc

    // per-page clamp
    $perPageReq = (int) request('per_page', 24);
    $perPage    = max(1, min($perPageReq ?: 24, 60));

    // -------- Caching opsi kategori (10 menit) --------
    $categoryOptions = \Illuminate\Support\Facades\Cache::remember('filter:product_categories', 600, function () {
        return \App\Models\Category::query()
            ->select(['id','name','slug'])
            ->orderBy('name')
            ->get();
    });

    // -------- Query dasar Produk --------
    $productsBase = \App\Models\Product::query()
        ->with(['productMedia:id,product_id,url']) // eager load gambar pertama di view
        ->withAvg('productReviews as avg_rating', 'rating')
        ->addSelect([
            'products.id','products.name','products.slug','products.created_at',
            \Illuminate\Support\Facades\DB::raw('(SELECT MIN(COALESCE(pv.base_price))
                                                 FROM product_variants pv
                                                 WHERE pv.product_id = products.id) AS min_price'),
        ])
        ->where('products.is_active', true)
        // SEARCH (grouped)
        ->when($q !== '', function ($qb) use ($q) {
            $like = '%' . $q . '%';
            $qb->where(function ($s) use ($like) {
                $s->where('products.name', 'like', $like)
                  ->orWhere('products.slug', 'like', $like)
                  ->orWhere('products.sku', 'like', $like)
                  ->orWhere('products.description', 'like', $like);

                $s->orWhereHas('productCategories', function ($qc) use ($like) {
                    $qc->where(function ($qq) use ($like) {
                        $qq->where('name', 'like', $like)
                           ->orWhere('slug', 'like', $like);
                    });
                });

                $s->orWhereHas('productVariants', function ($qv) use ($like) {
                    $qv->where(function ($qq) use ($like) {
                        $qq->where('slug', 'like', $like)
                           ->orWhere('sku', 'like', $like)
                           ->orWhere('description', 'like', $like);
                    });
                });
            });
        })
        // FILTER KATEGORI (slug/id)
        ->when(!empty($categories), function ($q) use ($categories) {
            $ids  = array_values(array_filter($categories, fn($v) => is_numeric($v)));
            $slugs= array_values(array_filter($categories, fn($v) => !is_numeric($v) && $v !== ''));
            $q->whereHas('productCategories', function ($qc) use ($ids, $slugs) {
                $qc->where(function($w) use ($ids, $slugs) {
                    if ($slugs) { $w->whereIn('slug', $slugs); }
                    if ($ids)   { $w->orWhereIn('id', $ids); }
                });
            });
        })
        // FILTER HARGA (via VARIANT)
        ->when(is_numeric($minPrice) || is_numeric($maxPrice), function ($q) use ($minPrice, $maxPrice) {
            $q->whereHas('productVariants', function ($qv) use ($minPrice, $maxPrice) {
                if (is_numeric($minPrice)) {
                    $qv->where(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_price, price, base_price)'), '>=', (float) $minPrice);
                }
                if (is_numeric($maxPrice)) {
                    $qv->where(\Illuminate\Support\Facades\DB::raw('COALESCE(sale_price, price, base_price)'), '<=', (float) $maxPrice);
                }
            });
        })
        // FILTER RATING minimal
        ->when(is_numeric($minRating), fn ($q) => $q->havingRaw('avg_rating >= ?', [(int)$minRating]))
        // STOK (ada varian dengan stock > 0)
        ->when($inStock, function ($q) {
            $q->whereHas('productVariants', fn($qv) => $qv->where('stock', '>', 0));
        })
        // PROMO/SALE (varian punya sale_price atau is_promo)
        ->when($onSale, function ($q) {
            $q->whereHas('productVariants', function ($qv) {
                $qv->whereNotNull('sale_price')
                   ->orWhere('is_promo', true);
            });
        })
        // FITUR (di products.features JSON)
        ->when(!empty($features), function ($q) use ($features) {
            foreach ($features as $f) {
                if ($f !== '' && $f !== null) {
                    $q->whereJsonContains('features', $f);
                }
            }
        });

    // -------- Sorting --------
    $productsBase = match ($sort) {
        'new'        => $productsBase->latest('products.created_at'),
        'price_asc'  => $productsBase
            ->orderByRaw('CASE WHEN min_price IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('min_price', 'asc'),
        'price_desc' => $productsBase
            ->orderByRaw('CASE WHEN min_price IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('min_price', 'desc'),
        default      => $productsBase
            ->orderByRaw('CASE WHEN avg_rating IS NULL THEN 1 ELSE 0 END ASC')
            ->orderBy('avg_rating', 'desc')
            ->latest('products.created_at'),
    };

    // -------- Cache hasil listing (5 menit) --------
    $cacheKey = 'listing:products:' . md5(request()->fullUrl()); // unik per filter+page
    $products = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($productsBase, $perPage) {
        return $productsBase->paginate($perPage)->withQueryString();
    });
@endphp

    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12" x-data>
        {{-- Hero Section --}}
        <livewire:components.promotion-hero-secondary show-on="HERO" cache-ttl="300" />

        {{-- FILTER FORM (GET) --}}
        <form id="filterForm" method="GET" class="flex flex-col lg:flex-row gap-8 lg:gap-10">
            {{-- SIDEBAR FILTERS --}}
            <aside class="lg:w-1/4 sticky top-6 self-start hidden lg:block">
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Filter Produk</h2>
                    </div>

                    <div class="p-5 space-y-6">
                        {{-- Search --}}
                        <div class="space-y-2">
                            <label for="filter-search" class="text-sm font-medium text-gray-700">Cari Produk</label>
                            <input type="search" id="filter-search" name="q" value="{{ $q }}"
                                   placeholder="Cari produk..."
                                   class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block w-full p-2.5"
                                   oninput="this.form.requestSubmit()"
                            />
                        </div>

                        {{-- Categories --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900">Kategori Produk</h3>
                                @if(!empty($categories))
                                    <a href="{{ request()->fullUrlWithQuery(['category' => null]) }}"
                                       class="text-xs text-zinc-700 hover:underline">Hapus</a>
                                @endif
                            </div>
                            @if($categoryOptions->isNotEmpty())
                                <div class="max-h-56 overflow-auto pr-1 space-y-2">
                                    @foreach($categoryOptions as $opt)
                                        @php
                                            $isChecked = in_array($opt->slug, $categories, true) || in_array((string)$opt->id, $categories, true);
                                        @endphp
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox"
                                                   name="category[]"
                                                   value="{{ $opt->slug }}"
                                                   @checked($isChecked)
                                                   class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900"
                                                   onchange="this.form.requestSubmit()"
                                            />
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
                                <input type="number" name="min_price" value="{{ old('min_price', $minPrice) }}" min="0"
                                       placeholder="Min"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block w-1/2 p-2.5"
                                       onchange="this.form.requestSubmit()"
                                />
                                <span class="text-gray-400">—</span>
                                <input type="number" name="max_price" value="{{ old('max_price', $maxPrice) }}" min="0"
                                       placeholder="Max"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block w-1/2 p-2.5"
                                       onchange="this.form.requestSubmit()"
                                />
                            </div>
                        </div>

                        {{-- Rating --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-900">Rating Minimal</h3>
                            <div class="space-y-2">
                                @foreach ([5,4,3,2,1] as $r)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="radio" name="min_rating" value="{{ $r }}"
                                               @checked((string)$minRating === (string)$r)
                                               class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900"
                                               onchange="this.form.requestSubmit()"
                                        />
                                        <span class="text-sm text-gray-700">
                                            {{ str_repeat('★', $r) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $r) }}</span> &nbsp; {{ $r }}+
                                        </span>
                                    </label>
                                @endforeach
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="min_rating" value=""
                                           @checked(empty($minRating))
                                           class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900"
                                           onchange="this.form.requestSubmit()"
                                    />
                                    <span class="text-sm text-gray-500">Tanpa filter rating</span>
                                </label>
                            </div>
                        </div>

                        {{-- Availability & Promo --}}
                        <div class="grid gap-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="in_stock" value="1" @checked($inStock)
                                       class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900"
                                       onchange="this.form.requestSubmit()"
                                />
                                <span class="text-sm text-gray-700">Stok tersedia</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="on_sale" value="1" @checked($onSale)
                                       class="w-4 h-4 text-zinc-900 bg-gray-100 border-gray-300 rounded focus:ring-zinc-900"
                                       onchange="this.form.requestSubmit()"
                                />
                                <span class="text-sm text-gray-700">Sedang promo</span>
                            </label>
                        </div>

                        {{-- Features --}}
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-900">Fitur</h3>
                            <div class="flex flex-wrap gap-2">
                                @php $featureOptions = ['Fast Charging','Bluetooth','Water Resistant','NFC','Wi-Fi']; @endphp
                                @foreach ($featureOptions as $feat)
                                    <label class="cursor-pointer text-xs font-medium me-2 px-3 py-1 rounded-full
                                                  {{ in_array($feat, $features) ? 'bg-zinc-900 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300' }} transition">
                                        <input type="checkbox" name="features[]"
                                               value="{{ $feat }}" class="sr-only"
                                               @checked(in_array($feat, $features))
                                               onchange="this.form.requestSubmit()"
                                        />
                                        {{ $feat }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Reset --}}
                        <div class="pt-4 border-t border-gray-100 mt-4 !space-y-0">
                            <a href="{{ url()->current() }}"
                               class="w-full inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-center text-gray-900
                                      border border-gray-300 rounded-full hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 transition duration-300">
                                Reset Filter
                            </a>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- MAIN CONTENT --}}
            <main class="lg:w-3/4 w-full">
                {{-- Top toolbar --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                    <p class="text-gray-600 text-sm sm:text-base">
                        @if ($products->total() > 0)
                            Menampilkan {{ ($products->firstItem() ?? 1) }}–{{ ($products->lastItem() ?? $products->count()) }}
                            dari {{ $products->total() }} produk
                        @else
                            Tidak ada produk yang cocok
                        @endif
                    </p>
                    <div class="flex items-center gap-2">
                        <label for="sort-select" class="text-gray-600 text-sm whitespace-nowrap">Urutkan:</label>
                        <select id="sort-select" name="sort"
                                class="w-full sm:w-auto bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block p-2.5"
                                onchange="this.form.requestSubmit()">
                            <option value="popular"   @selected($sort==='popular')>Paling Populer</option>
                            <option value="new"       @selected($sort==='new')>Terbaru</option>
                            <option value="price_asc" @selected($sort==='price_asc')>Harga: Rendah → Tinggi</option>
                            <option value="price_desc"@selected($sort==='price_desc')>Harga: Tinggi → Rendah</option>
                        </select>

                        <select name="per_page"
                                class="w-full sm:w-auto bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-zinc-900 focus:border-zinc-900 block p-2.5"
                                onchange="this.form.requestSubmit()">
                            @foreach ([12,24,36,48] as $pp)
                                <option value="{{ $pp }}" @selected($perPage==$pp)>{{ $pp }}/hal</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- PRODUCT GRID --}}
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                    @forelse($products as $product)
                        @php
                            $image  = optional($product->productMedia->first())->url;              // hasMany productMedia
                            $price  = $product->min_price;                                         // MIN(COALESCE(...))
                            $rating = $product->avg_rating ? round($product->avg_rating, 1) : null;// withAvg
                        @endphp

                        <livewire:components.card-product
                            :sku="$product->sku"
                            :title="$product->name"
                            :price="'Rp ' . number_format((float)$price, 0, ',', '.')"
                            :image="$image"
                            :rating="$rating"
                        />
                    @empty
                        <div class="col-span-full">
                            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
                                <h3 class="text-lg font-medium text-gray-900">Produk belum tersedia</h3>
                                <p class="mt-1 text-gray-500">Coba ubah filter atau kembali beberapa saat lagi.</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-12">
                    {{ $products->onEachSide(1)->links() }}
                </div>
            </main>
        </form>
    </div>

    {{-- Auto-submit (opsional) --}}
    <script>
        document.addEventListener('alpine:init', () => {
            const form = document.getElementById('filterForm');
            if (!form) return;
            form.addEventListener('submit', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        });
    </script>
@endsection
