<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $input = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
        Cart::created($input);
        return view('pages.products.cart');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function wislist(int $id)
    {
        return view('pages.products.wislist');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function checkout(Request $request)
    {
        return view('pages.products.checkout');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function transaction(Request $request)
    {
        return view('pages.products.transaction');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $sku)
    {
        // Ambil ulang produk dengan eager-load & agregasi agar lengkap
        $product = Product::query()
            ->where('sku', $sku)
            ->with([
                'productMedia:id,product_id,url',
                'productCategories:id,name,slug',
                'productVariants:id,variant_sku,name,product_id,base_price,stock',
                // ambil ulasan untuk ditampilkan (batasi di view jika perlu)
                'productReviews:id,product_id,user_id,title,comment,rating,created_at',
            ])
            ->withAvg('productReviews as avg_rating', 'rating')
            ->withCount('productReviews as reviews_count')
            ->addSelect([
                'id', 'name', 'slug', 'long_desc as description',
                DB::raw('(SELECT MIN(COALESCE(pv.base_price))
                          FROM product_variants pv
                          WHERE pv.product_id = products.id) AS min_price'),
                DB::raw('(SELECT COALESCE(SUM(pv.stock),0)
                          FROM product_variants pv
                          WHERE pv.product_id = products.id) AS total_stock'),
            ])
            ->firstOrFail();

        // Gambar utama = media pertama (fallback asset)
        $imageUrl = optional($product->productMedia->first())->url;
        if ($imageUrl && !preg_match('/^(http:\/\/|https:\/\/|data:image)/', $imageUrl)) {
            $imageUrl = asset($imageUrl);
        }
        $imageUrl = $imageUrl ?: asset('images/placeholder-product.png'); // Pastikan file ini ada di public/images

        // Galeri (semua url media yang valid)
        $gallery = $product->productMedia
            ->pluck('url')
            ->filter()
            ->map(fn($u) => (
                str_starts_with($u, 'http://') ||
                str_starts_with($u, 'https://') ||
                str_starts_with($u, 'data:image')
            ) ? $u : asset($u))
            ->values();

        // Kategori utama (untuk breadcrumb)
        $primaryCategory = optional($product->productCategories->first());

        // Warna/varian (opsional; distinct dari variants)
        $colors = $product->productVariants
            ->pluck('color')
            ->filter()
            ->unique()
            ->values()
            ->map(fn($c) => ['name' => $c, 'code' => null]); // jika ada hex di DB, map di sini

        // Distribusi rating: hitung count per bintang
        $ratingCounts = $product->productReviews()
            ->selectRaw('rating, COUNT(*) as c')
            ->groupBy('rating')
            ->pluck('c', 'rating');   // [5=>xxx, 4=>yyy, ...]
        $totalReviews = (int) $product->reviews_count;
        $ratingDistribution = collect([5,4,3,2,1])->mapWithKeys(function ($star) use ($ratingCounts, $totalReviews) {
            $count = (int) ($ratingCounts[$star] ?? 0);
            $pct   = $totalReviews > 0 ? round($count * 100 / $totalReviews) : 0;
            return [$star => $pct];
        });

        // Ulasan ditampilkan (terbaru dulu). Bisa dipaginasi bila perlu.
        $reviews = $product->productReviews->sortByDesc('created_at')->take(10)->values();

        // Harga terendah (min_price agregat)
        $minPrice = (float) ($product->min_price ?? 0);
        $priceFormatted = $minPrice > 0 ? 'Rp ' . number_format($minPrice, 0, ',', '.') : null;

        return view('pages.products.detail', [
            'product'            => $product,
            'imageUrl'           => $imageUrl,
            'gallery'            => $gallery,
            'primaryCategory'    => $primaryCategory,
            'colors'             => $colors,
            'ratingDistribution' => $ratingDistribution,
            'reviews'            => $reviews,
            'priceFormatted'     => $priceFormatted,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
