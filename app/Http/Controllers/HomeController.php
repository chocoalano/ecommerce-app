<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use App\Models\Product\Category;
use App\Models\Product\Product;
use App\Models\Promo\Promotion;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Ambil Data Promo Utama dengan cache
        $promo1 = Cache::remember('promo:hero_1', 300, function () {
            return $this->promotionService->getPromotionSlot('HERO_1')
                ?? $this->promotionService->getPromotionSlot('HERO');
        });

        $promo2 = Cache::remember('promo:hero_2', 300, function () use ($promo1) {
            return $this->promotionService->getPromotionSlot('HERO_2')
                ?? $this->promotionService->getSecondHeroPromo($promo1);
        });

        // 2. Format Data Promo untuk View (tidak perlu cache, hasil promo sudah cache)
        $p1Data = $this->promotionService->formatPromotionProps(
            $promo1,
            'Promo Unggulan',
            'Penawaran terbaik minggu ini.'
        );

        $p2Data = $this->promotionService->formatPromotionProps(
            $promo2,
            'Welcome to Our Store!',
            'Temukan penawaran terbaik dan produk terbaru kami.'
        );

        // 3. Ambil Produk Rekomendasi (sudah cache di getRecommendedProducts)
        $recommendedProducts = $this->getRecommendedProducts();

        // 4. Data Hero dengan cache
        $hero = Promotion::with('products')
            ->select(['id', 'name', 'type', 'description', 'image'])
            ->where([
                'show_on' => 'HERO',
                'page' => 'beranda',
                ])
            ->first();

        $heroData = [
            'hero' => $hero,
            'heroTitle' => $hero?->name ?? 'Upgrade Ponsel Lama Anda',
            'heroType' => $hero?->type ?? 'Special Offer',
            'heroDesc' => $hero?->description ?? 'Dapatkan diskon besar dengan Trade-in eksklusif kami!',
            'heroTag' => null,
            'heroImg' => $hero?->image ?? 'images/hero-banner.jpg',
            'heroLink' => route('products.index'),
            'heroMore' => '#',
            'products' => $hero?->products ?? [],
        ];

        // 5. Data Kategori dengan cache
        $categoriesFirst = Cache::remember('categories:first', 300, function () {
            return Category::query()
                ->where('is_active', true)
                ->orderBy('id', 'asc')
                ->limit(6)
                ->get();
        });

        $categoriesSecond = Cache::remember('categories:second', 300, function () use ($categoriesFirst) {
            return Category::query()
                ->where('is_active', true)
                ->whereNotIn('id', $categoriesFirst->pluck('id'))
                ->orderBy('id', 'desc')
                ->limit(6)
                ->get();
        });

        return view('pages.home', compact(
            'heroData', 'p1Data', 'p2Data', 'recommendedProducts',
            'categoriesFirst', 'categoriesSecond'
        ));
    }

    /**
     * Query dan format produk rekomendasi dengan cache 5 menit.
     */
    protected function getRecommendedProducts(): array
    {
        return Product::query()
                ->select(['id', 'sku', 'name', 'slug', 'created_at', 'base_price'])
                ->with([
                    'primaryMedia:id,product_id,url',
                    'reviews:id,product_id,rating',
                ])
                ->withAvg('reviews as avg_rating', 'rating')
                ->where('is_active', true)
                ->latest('id')
                ->limit(10)
                ->get()
                ->map(function ($p) {
                    $image = optional($p->primaryMedia)->url ?: asset('images/galaxy-z-flip7-share-image.png');
                    $rawPrice = $p->base_price ?? 0;

                    return [
                        'id'=> $p->id,
                        'sku' => $p->sku,
                        'title' => $p->name,
                        'image' => $image,
                        'price' => 'Rp'.number_format((float) $rawPrice, 0, ',', '.'),
                        'rating' => $p->avg_rating ? round($p->avg_rating, 1) : null,
                    ];
                })
                ->toArray();
    }

    public function newsletter(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
        ]);
        NewsletterSubscriber::create($validate);
        return redirect()->back()->with('success', 'Berhasil berlangganan newsletter!');
    }
}
